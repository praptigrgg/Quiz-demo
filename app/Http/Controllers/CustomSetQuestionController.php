<?php

namespace App\Http\Controllers;

use App\Models\CustomSet;
use App\Models\CustomSetQuestion;
use Illuminate\Http\Request;

class CustomSetQuestionController extends Controller
{
    // List questions for a custom set
    public function index($setId)
    {
        $customSet = CustomSet::with('questions.options')->findOrFail($setId);

        // Get all questions (no groups)
        $questions = $customSet->questions;

        return view('pages.admin.custom_sets.questions', compact('customSet', 'questions'));
    }

    // Store new question
    public function store(Request $request, $setId)
    {
        $customSet = CustomSet::findOrFail($setId);

        $validated = $request->validate([
            'questionText' => 'required|string',
            'questionType' => 'required|in:multiple_choice,true_false,subjective',
            'isQuestionShuffle' => 'nullable|boolean',
            'isAnswerShuffle' => 'nullable|boolean',
            'explanation' => 'nullable|string',
            'options' => 'nullable|array',          // MCQ / true_false
            'subjectiveAnswer' => 'nullable|string' // Subjective type
        ]);

        $validated['set_id'] = $setId;

        $question = CustomSetQuestion::create($validated);

        // Save options if MCQ / True/False
        if (in_array($validated['questionType'], ['multiple_choice', 'true_false']) && !empty($validated['options'])) {
            foreach ($validated['options'] as $key => $opt) {
                $question->options()->create([
                    'optionText' => $opt['text'],
                    'isCorrect' => isset($request->correct_option) && $request->correct_option == $key,
                ]);
            }
        }

        // Save subjective answer if type is subjective
        if ($validated['questionType'] === 'subjective' && !empty($validated['subjectiveAnswer'])) {
            $question->options()->create([
                'subjectiveAnswer' => $validated['subjectiveAnswer'],
            ]);
        }

        return back()->with('success', 'Question added.');
    }

    // Delete question
    public function destroy($setId, $questionId)
    {
        $question = CustomSetQuestion::findOrFail($questionId);
        $question->delete();

        return back()->with('success', 'Question deleted.');
    }

    // Optional: Import view
    public function import($setId)
    {
        $customSet = CustomSet::findOrFail($setId);
        return view('pages.admin.custom_sets.import', compact('customSet'));
    }
}
