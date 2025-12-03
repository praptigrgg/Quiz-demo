<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\LiveSet;
use Illuminate\Http\Request;
use App\Models\LiveSetOption;
use App\Models\LiveSetQuestion;
use App\Events\ActivityAssigned;
use App\Models\MeetingAssignment;
use App\Models\Student;
use Illuminate\Support\Facades\Log;

class LiveSetController extends Controller
{
    /**
     * List all meeting assignments
     */
    public function index()
    {
        $assignedMeetings = MeetingAssignment::with('assignable')->paginate(10);
        $unassignedLiveSets = LiveSet::whereDoesntHave('meetingAssignments')->paginate(10);

        return view('pages.admin.live.index', compact('assignedMeetings', 'unassignedLiveSets'));
    }


    /**
     * Show page to create a new Live Set
     */
    public function assignPage()
    {
        $quizzes = Quiz::all();
        return view('pages.admin.live.assign', compact('quizzes'));
    }

    /**
     * Store a new Live Set with questions and options
     */
    public function store(Request $request)
    {
        $request->validate([
            'questions' => 'required|array|min:1',
            'questions.*.questionText' => 'required|string',
            'questions.*.questionType' => 'required|string|in:mcq,true_false,subjective',
        ]);

        $liveSet = LiveSet::create([
            'description' => $request->description ?? null,

            // ⭐ TIMER ADDED (stored with Live Set)
            'timer' => $request->timer ?? 30,

            'created_by' => null, // No auth
        ]);

        foreach ($request->questions as $q) {
            $question = LiveSetQuestion::create([
                'live_set_id' => $liveSet->id,
                'questionText' => $q['questionText'],
                'questionType' => $q['questionType'],
                'isMandatory' => $q['isMandatory'] ?? true,
            ]);

            if (isset($q['options']) && in_array($q['questionType'], ['mcq', 'true_false'])) {
                foreach ($q['options'] as $opt) {
                    LiveSetOption::create([
                        'question_id' => $question->id,
                        'optionText' => $opt['optionText'],
                        'isCorrect' => $opt['isCorrect'] ?? false,
                    ]);
                }
            }
        }

        return $liveSet;
    }

    /**
     * Assign Live Set or Quiz to a meeting
     */
    public function assignToMeeting(Request $request)
    {
        $request->validate([
            'meeting_id' => 'required|string',
            'assignable_type' => 'required|in:quiz,live',
            'assignable_id' => 'required|integer',
            // ⭐ TIMER VALIDATION ADDED
            'timer' => 'nullable|integer|min:1',
        ]);

        $modelClass = $request->assignable_type === 'quiz' ? Quiz::class : LiveSet::class;
        $assignable = $modelClass::with('questions')->findOrFail($request->assignable_id);

        $assignment = MeetingAssignment::updateOrCreate(
            [
                'assignable_type' => $modelClass,
                'assignable_id' => $assignable->id,
            ],
            [
                'meeting_id' => $request->meeting_id,
                'assigned_at' => now(),

                // ⭐ STORE TIMER IN ASSIGNMENT
                'timer' => $request->timer ?? $assignable->timer ?? null,
            ]
        );

        $questions = $assignable->questions->map(function ($q) use ($request) {
            return [
                'id' => $q->id,
                'title' => $q->title ?? null,
                'questionText' => $q->questionText ?? null,
                'type' => class_basename($q->assignable_type ?? $request->assignable_type),
            ];
        });

        event(new ActivityAssigned($assignment));
        Log::info("Broadcasting ActivityAssigned event for Zoom assignment ID: {$assignment->id}");

        return response()->json([
            'success' => true,
            'assigned_at' => $assignment->assigned_at->format('d M, Y h:i A'),
            'questions' => $questions,
            'assignment_id' => $assignment->id,
            'timer' => $assignment->timer // ⭐ RETURN ADDED
        ]);
    }


    /**
     * Store a new Live Set and optionally assign it
     */
    public function storeAndAssign(Request $request)
    {
        $assignNow = $request->boolean('assignNow', true);

        $request->validate([
            'questions' => 'required|array|min:1',
            'questions.*.questionText' => 'required|string',
            'questions.*.questionType' => 'required|string|in:mcq,true_false,subjective',
            'meeting_id' => $assignNow ? 'required|string' : 'nullable|string',
            // ⭐ TIMER VALIDATION
            'timer' => 'nullable|integer|min:1',
        ]);

        // Store live set (timer is included automatically)
        $liveSet = $this->store($request);

        if ($assignNow && $request->filled('meeting_id')) {
            $assignment = MeetingAssignment::create([
                'meeting_id' => $request->meeting_id,
                'assignable_type' => LiveSet::class,
                'assignable_id' => $liveSet->id,
                'assigned_at' => now(),

                // ⭐ STORE TIMER
                'timer' => $request->timer ?? $liveSet->timer,
            ]);

            event(new ActivityAssigned($assignment));
            Log::info("Broadcasting ActivityAssigned event for assignment ID: {$assignment->id}");

            return response()->json([
                'success' => true,
                'assignment_id' => $assignment->id,
                'timer' => $assignment->timer,  // ⭐ RETURN
                'message' => 'Live Set created & assigned successfully!'
            ]);
        }

        return response()->json([
            'success' => true,
            'live_set_id' => $liveSet->id,
            'timer' => $liveSet->timer, // ⭐ RETURN
            'message' => 'Live Set created successfully! Not assigned to meeting.'
        ]);
    }


    /**
     * Search quizzes by title
     */
    public function search(Request $request)
    {
        $query = $request->query('query');
        $quizzes = Quiz::where('quizTitle', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'quizTitle']);
        return response()->json($quizzes);
    }

   public function participants($assignmentId)
{
    $assignment = MeetingAssignment::with(['responses.student'])->findOrFail($assignmentId);

    // Get unique students who have responses, with fastest elapsed_time
    $participants = $assignment->responses
        ->groupBy('student_id')
        ->map(function ($responses, $studentId) {
            $student = $responses->first()->student;
            $student->firstResponse = $responses->sortBy('elapsed_time')->first(); // fastest response
            $student->elapsed_time = $student->firstResponse?->elapsed_time;       // store elapsed time
            return $student;
        })
        ->sortBy(function ($student) {
            return $student->elapsed_time ?? PHP_INT_MAX; // fastest first
        })
        ->values(); // reset keys

    return view('pages.admin.live.participants', compact('assignment', 'participants'));
}



    public function studentAnswers($assignmentId, $studentId)
    {
        $assignment = MeetingAssignment::with([
            'responses.questionable',
            'responses.selectedOption'
        ])->findOrFail($assignmentId);

        $responses = $assignment->responses
            ->where('student_id', $studentId)
            ->values()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'question' => $r->questionable ? [
                        'id' => $r->questionable->id,
                        'questionText' => $r->questionable->questionText,
                        'type' => class_basename($r->questionable_type),
                    ] : null,
                    'selected_option' => $r->selectedOption ? [
                        'id' => $r->selectedOption->id,
                        'optionText' => $r->selectedOption->optionText,
                    ] : null,
                    'subjective_answer' => $r->subjective_answer,
                    'is_correct' => $r->is_correct,
                    'score' => $r->score ?? 0,
                    'created_at' => $r->created_at?->format('d M, Y h:i A'),
                ];
            });

        $student = Student::findOrFail($studentId);

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
            ],
            'responses' => $responses,
        ]);
    }

    /**
     * Delete a meeting assignment
     */
    public function destroy($id)
    {
        MeetingAssignment::destroy($id);

        return response()->json([
            'success' => true,
            'message' => 'Assignment deleted successfully!'
        ]);
    }
}
