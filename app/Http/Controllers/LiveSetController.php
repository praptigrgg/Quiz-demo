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
     * Assign Live Set or Quiz to a meeting (supports single or multiple IDs)
     */
public function assignToMeeting(Request $request)
{
    try {
        // Validate the incoming request
        $validated = $request->validate([
            'meeting_id' => 'required|string',
            'assignable_type' => 'required|in:quiz,live',
            'assignable_id' => 'required|string', // comma-separated IDs
            'timer' => 'nullable|integer|min:1',
        ]);

        // Split and clean IDs
        $assignableIds = collect(explode(',', $validated['assignable_id']))
            ->filter(fn($id) => !empty($id))    // Remove empty values
            ->unique()                           // Remove duplicates
            ->values()
            ->all();

        if (empty($assignableIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid items selected to assign.'
            ], 422);
        }

        $modelClass = $validated['assignable_type'] === 'quiz' ? Quiz::class : LiveSet::class;
        $assignments = [];

        foreach ($assignableIds as $id) {
            $assignable = $modelClass::with('questions')->find($id);

            if (!$assignable) {
                continue; // skip invalid IDs
            }

            $assignment = MeetingAssignment::updateOrCreate(
                [
                    'assignable_type' => $modelClass,
                    'assignable_id' => $assignable->id,
                ],
                [
                    'meeting_id' => $validated['meeting_id'],
                    'assigned_at' => now(),
                    'timer' => $validated['timer'] ?? $assignable->timer ?? null,
                ]
            );

            event(new ActivityAssigned($assignment));
            Log::info("Broadcasting ActivityAssigned event for assignment ID: {$assignment->id}");

            $assignments[] = [
                'assignment_id' => $assignment->id,
                'assignable_id' => $assignable->id,
                'timer' => $assignment->timer,
            ];
        }

        if (empty($assignments)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid assignments were created.'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'assigned_at' => now()->format('d M, Y h:i A'),
            'assignments' => $assignments,
            'message' => count($assignments) > 1 ? 'Live Sets assigned successfully!' : 'Live Set assigned successfully!',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'errors' => $e->errors(),
            'message' => 'Validation failed',
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error assigning meeting: '.$e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred.',
        ], 500);
    }
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
