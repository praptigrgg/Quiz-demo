<?php

namespace App\Http\Controllers;

use App\Events\QuizAssigned;
use App\Models\Quiz;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        try {
            $quizzes = Quiz::with('course') // eager load course
                ->when($request->searchQuizzes, function ($query, $search) {
                    return $query->where('quizTitle', 'like', "%{$search}%");
                })
                ->latest()
                ->paginate(10);

            return view('pages.admin.quizzes.index', compact('quizzes'));
        } catch (\Exception $e) {
            Log::error('Quiz index error: ' . $e->getMessage());
            $quizzes = Quiz::latest()->paginate(10);
            return view('pages.admin.quizzes.index', compact('quizzes'));
        }
    }

    public function create()
    {
        $courses = Course::all(); // get all courses
        return view('pages.admin.quizzes.create', compact('courses'));
    }

    public function store(Request $request)
    {
        Log::info('Quiz store method called', ['request_data' => $request->all()]);

        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'quizTitle' => 'required|string|max:255',
            'quizDescription' => 'required|string',
            'quizDuration' => 'required|integer|min:1',
            'quizTotalMarks' => 'required|integer|min:1',
            'quizPassMark' => 'required|integer|min:1',
            'correctAnswerMarks' => 'required|integer|min:1',
            'negativeMarking' => 'nullable|integer|min:0|max:100',
            'pricingType' => 'required|in:free,paid',
            'quizThumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'valid_days_after_enrollment' => 'nullable|integer|min:1',
            'normal_price' => $request->pricingType === 'paid' ? 'required|numeric|min:0' : 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'discount_start_date' => 'nullable|date|required_with:discount_price',
            'discount_end_date' => 'nullable|date|after:discount_start_date|required_with:discount_price',
            'is_one_time' => 'boolean',
            'is_live_quiz' => 'boolean',
            'live_start_time' => 'nullable|date|required_if:is_live_quiz,1',
            'live_end_time' => 'nullable|date|after:live_start_time|required_if:is_live_quiz,1',
            'is_quiz_group' => 'boolean',
            'quiz_groups' => 'required_if:is_quiz_group,1|array|min:1',
            'quiz_groups.*' => 'required_if:is_quiz_group,1|string|min:1|max:100',
            'enableReview' => 'boolean',
            'showInListQuiz' => 'boolean',
            'is_publish' => 'boolean',
        ]);

        try {
            $quizThumbnailPath = null;
            if ($request->hasFile('quizThumbnail')) {
                $quizThumbnailPath = $request->file('quizThumbnail')->store('quiz-thumbnails', 'public');
            }

            $quizGroups = null;
            if ($request->is_quiz_group && $request->quiz_groups) {
                $validGroups = array_filter($request->quiz_groups, fn($group) => !empty(trim($group)));
                if (!empty($validGroups)) {
                    $quizGroups = array_values($validGroups);
                }
            }

            $quizData = [
                'course_id' => $request->course_id, // link to course
                'quizTitle' => $request->quizTitle,
                'quizDescription' => $request->quizDescription,
                'quizDuration' => $request->quizDuration,
                'quizTotalMarks' => $request->quizTotalMarks,
                'quizPassMark' => $request->quizPassMark,
                'correctAnswerMarks' => $request->correctAnswerMarks,
                'negativeMarking' => $request->negativeMarking ?? 0,
                'pricingType' => $request->pricingType,
                'quizThumbnail' => $quizThumbnailPath,
                'valid_days_after_enrollment' => $request->valid_days_after_enrollment,
                'normal_price' => $request->normal_price,
                'discount_price' => $request->discount_price,
                'discount_start_date' => $request->discount_start_date,
                'discount_end_date' => $request->discount_end_date,
                'is_one_time' => $request->boolean('is_one_time'),
                'is_live_quiz' => $request->boolean('is_live_quiz'),
                'live_start_time' => $request->live_start_time,
                'live_end_time' => $request->live_end_time,
                'is_quiz_group' => $request->boolean('is_quiz_group'),
                'quiz_groups' => $quizGroups,
                'enableReview' => $request->boolean('enableReview'),
                'showInListQuiz' => $request->boolean('showInListQuiz'),
                'is_publish' => $request->boolean('is_publish'),
                'created_by' => Auth::id(),
            ];

            $quiz = Quiz::create($quizData);

            return redirect()->route('admin.quizzes.index')->with('success', 'Quiz created successfully!');
        } catch (\Exception $e) {
            Log::error('Quiz creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to create quiz: ' . $e->getMessage())->withInput();
        }
    }

    public function updatePublishStatus(Request $request, $id)
    {
        try {
            $quiz = Quiz::findOrFail($id);
            $quiz->update(['is_publish' => !$quiz->is_publish]);

            $message = $quiz->is_publish ? 'published' : 'unpublished';
            return redirect()->route('admin.quizzes.index')->with('success', "Quiz {$message} successfully.");
        } catch (\Exception $e) {
            Log::error('Publish status update failed: ' . $e->getMessage());
            return redirect()->route('admin.quizzes.index')->with('error', 'Failed to update quiz publish status.');
        }
    }

    public function edit($id)
    {
        $quiz = Quiz::findOrFail($id);
        $courses = Course::all(); // get all courses
        return view('pages.admin.quizzes.edit', compact('quiz', 'courses'));
    }

    public function update(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);

        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'quizTitle' => 'required|string|max:255',
            'quizDescription' => 'required|string',
            'quizDuration' => 'required|integer|min:1',
            'quizTotalMarks' => 'required|integer|min:1',
            'quizPassMark' => 'required|integer|min:1',
            'correctAnswerMarks' => 'required|integer|min:1',
            'pricingType' => 'required|in:free,paid',
        ]);

        try {
            $quiz->update($request->all());
            return redirect()->route('admin.quizzes.index')->with('success', 'Quiz updated successfully!');
        } catch (\Exception $e) {
            Log::error('Quiz update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update quiz.')->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $quiz = Quiz::findOrFail($id);
            $quiz->delete();

            return redirect()->route('admin.quizzes.index')->with('success', 'Quiz deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Quiz deletion failed: ' . $e->getMessage());
            return redirect()->route('admin.quizzes.index')->with('error', 'Failed to delete quiz.');
        }
    }

    public function getGroups($id)
    {
        $quiz = Quiz::findOrFail($id);
        $groups = $quiz->quiz_groups ?? [];

        return response()->json([
            'groups' => $groups
        ]);
    }

   
}
