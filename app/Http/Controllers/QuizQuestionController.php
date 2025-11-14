<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\Lesson;
use App\Models\QuizOption;
use App\Models\CourseGroup;
use Illuminate\Support\Str;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use App\Models\CourseSection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class QuizQuestionController extends Controller
{
    /**
     * Display all questions for a given quiz.
     */
    public function index(Request $request, Quiz $quiz)
    {

        // Load questions with options
        $quiz->load('questions.options');

        // Group questions by group_name
        $groupedQuestions = $quiz->questions->groupBy(function ($q) {
            return $q->group_name ?? 'Ungrouped';
        });

        // Paginate each group
        $paginatedGroups = [];
        $activeGroup = $request->get('active_group');

        foreach ($groupedQuestions as $groupName => $questions) {
            $pageParam = 'page_' . Str::slug($groupName);
            $page = $request->get($pageParam, 1);

            $paginatedGroups[$groupName] = new LengthAwarePaginator(
                $questions->forPage($page, 5), // 5 per group page (adjust as needed)
                $questions->count(),
                5,
                $page,
                [
                    'path' => url()->current(),
                    'query' => array_merge($request->except($pageParam), ['active_group' => $activeGroup]),
                    'pageName' => $pageParam
                ]
            );
        }

        // Load all quizzes (for dropdowns, import modal, etc.)
        $quizzes = Quiz::all();

        return view('pages.admin.quiz_questions', compact('quiz', 'quizzes', 'groupedQuestions', 'paginatedGroups', 'activeGroup'));
    }

    /**
     * Store a new question for a quiz.
     */
    public function store(Request $request, Quiz $quiz)
    {
        $request->validate([
            'questionText' => 'required|string',
            'questionType' => 'required|string',
            'isMandatory' => 'required|boolean',
            'options' => 'required|array|min:1',
            'correct_option' => 'required',
            'group_name' => 'nullable|string',
        ]);

        $question = $quiz->questions()->create([
            'questionText' => $request->questionText,
            'questionType' => $request->questionType,
            'isMandatory' => $request->isMandatory,
            'explanation' => $request->explanation,
            'isQuestionShuffle' => $request->has('isQuestionShuffle'),
            'isAnswerShuffle' => $request->has('isAnswerShuffle'),
            'group_name' => $request->group_name,
            'course' => $request->course,
            'section' => $request->section,
            'lesson' => $request->lesson,
        ]);

        foreach ($request->options as $key => $optionData) {
            $question->options()->create([
                'optionText' => $optionData['text'],
                'isCorrect' => ($key == $request->correct_option),
            ]);
        }

        return redirect()->back()->with('success', 'Question created successfully!');
    }

    /**
     * Edit a specific question.
     */
    public function edit($id)
    {
        $question = QuizQuestion::with('options')->findOrFail($id);
        return view('admin.quizzes-questions.edit', compact('question'));
    }

    /**
     * Update a question.
     */
    public function update(Request $request, $id)
    {
        $question = QuizQuestion::findOrFail($id);

        $question->update([
            'questionText' => $request->questionText,
            'questionType' => $request->questionType,
            'isMandatory' => $request->isMandatory,
            'explanation' => $request->explanation,
            'isQuestionShuffle' => $request->has('isQuestionShuffle'),
            'isAnswerShuffle' => $request->has('isAnswerShuffle'),
            'group_name' => $request->group_name ?? $question->group_name,
            'course' => $request->course ?? $question->course,
            'section' => $request->section ?? $question->section,
            'lesson' => $request->lesson ?? $question->lesson,
        ]);

        // Update options if any
        if ($request->has('options')) {
            foreach ($request->options as $optionId => $optionData) {
                $option = QuizOption::find($optionId);
                if ($option) {
                    $option->update([
                        'optionText' => $optionData['optionText'],
                        'isCorrect' => isset($optionData['isCorrect']),
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Question updated successfully.');
    }

    /**
     * Delete a question.
     */
    public function destroy($id)
    {
        $question = QuizQuestion::findOrFail($id);
        $question->delete();
        return redirect()->back()->with('success', 'Question deleted successfully.');
    }

    /**
     * Import questions for a specific quiz.
     */
    public function import(Quiz $quiz)
    {
        $quiz->load('questions.options');
        $questions = $quiz->questions;
        $quizzes = Quiz::all();
        $courses = Course::all();

        return view('pages.admin.quizzes-questions.import', compact('quiz', 'questions', 'quizzes', 'courses'));
    }

public function replicate(Request $request)
{
    $request->validate([
        'selectedQuestions' => 'required|string',
        'actionType' => 'required|string',
    ]);

    $selectedIds = explode(',', $request->selectedQuestions);
    $destinationQuizId = $request->quiz_id;
    $destinationCourse = $request->course;
    $destinationSection = $request->section;
    $destinationLesson = $request->lesson;
    $destinationGroup = $request->group;   // <-- FIXED

    // Validate destination
    if (!$destinationQuizId && (!$destinationCourse || !$destinationSection || !$destinationLesson)) {
        return response()->json([
            'success' => false,
            'message' => 'Select either cascading destination or quiz'
        ]);
    }

    $questions = QuizQuestion::whereIn('id', $selectedIds)->get();

    DB::beginTransaction();
    try {
        foreach ($questions as $question) {
            $newQuestion = $question->replicate();

            // --- Destination: QUIZ ---
            if ($destinationQuizId) {
                $newQuestion->quiz_id = $destinationQuizId;
                if ($destinationGroup) {
                    $newQuestion->group_name = $destinationGroup;   // <-- FIXED
                }
            }

            // --- Destination: Cascading ---
            else {
                $newQuestion->course = $destinationCourse;
                $newQuestion->section = $destinationSection;
                $newQuestion->lesson = $destinationLesson;
                $newQuestion->group_name = $destinationGroup;       // <-- FIXED
            }

            $newQuestion->save();

            // Replicate options
            foreach ($question->options as $opt) {
                $newQuestion->options()->create([
                    'optionText' => $opt->optionText,
                    'isCorrect' => $opt->isCorrect,
                ]);
            }
        }

        DB::commit();
        return response()->json([
            'success' => true,
            'message' => count($questions) . ' question(s) replicated successfully'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

    // ----- Migrate Questions -----
public function migrate(Request $request)
{
    $request->validate([
        'selectedQuestions' => 'required|string',
        'actionType' => 'required|string',
    ]);

    $selectedIds = explode(',', $request->selectedQuestions);
    $destinationQuizId = $request->quiz_id;
    $destinationCourse = $request->course;
    $destinationSection = $request->section;
    $destinationLesson = $request->lesson;
    $destinationGroup = $request->group;   // <-- FIXED

    if (!$destinationQuizId && (!$destinationCourse || !$destinationSection || !$destinationLesson)) {
        return response()->json([
            'success' => false,
            'message' => 'Select either cascading destination or quiz'
        ]);
    }

    $questions = QuizQuestion::whereIn('id', $selectedIds)->get();

    DB::beginTransaction();
    try {
        foreach ($questions as $question) {

            // --- Destination: QUIZ ---
            if ($destinationQuizId) {
                $question->quiz_id = $destinationQuizId;
                if ($destinationGroup) {
                    $question->group_name = $destinationGroup;   // <-- FIXED
                }
            }

            // --- Destination: Cascading ---
            else {
                $question->course = $destinationCourse;
                $question->section = $destinationSection;
                $question->lesson = $destinationLesson;
                $question->group_name = $destinationGroup;        // <-- FIXED
            }

            $question->save();
        }

        DB::commit();
        return response()->json([
            'success' => true,
            'message' => count($questions) . ' question(s) migrated successfully'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

    public function filter(Request $request)
    {
        $query = QuizQuestion::query()
            ->with('quiz'); // eager load quiz for course info

        // Filter by course via the quizzes table
        if ($request->filled('course')) {
            $query->whereHas('quiz', function ($q) use ($request) {
                $q->where('course_id', $request->course);
            });
        }

        // Filter by quiz_id directly
        if ($request->filled('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }

        // Filter by group
        if ($request->filled('group')) {
            $query->where('group_name', $request->group);
        }

        $questions = $query->get([
            'id',
            'questionText',
            'explanation',
            'questionType',
            'group_name',
            'quiz_id',
        ]);

        return response()->json([
            'success' => true,
            'questions' => $questions,
        ]);
    }

    public function getSections($courseId)
    {
        $sections = CourseSection::where('course_id', $courseId)->pluck('name', 'id');
        return response()->json($sections);
    }

    public function getLessons($courseId, $sectionId)
    {
        $lessons = Lesson::where('course_id', $courseId)->where('section_id', $sectionId)->pluck('name', 'id');
        return response()->json($lessons);
    }
}
