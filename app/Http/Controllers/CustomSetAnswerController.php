<?php

namespace App\Http\Controllers;

use App\Models\CustomSetAnswer;
use App\Models\CustomSetQuestion;
use Illuminate\Http\Request;

class CustomSetAnswerController extends Controller
{
    // Store a new answer for a question
    public function store(Request $request, $questionId)
    {
        $question = CustomSetQuestion::findOrFail($questionId);

        $validated = $request->validate([
            'optionText' => 'nullable|string',
            'subjectiveAnswer' => 'nullable|string',
            'isCorrect' => 'sometimes|boolean',
        ]);

        $validated['question_id'] = $questionId;
        $validated['isCorrect'] = $validated['isCorrect'] ?? false;

        CustomSetAnswer::create($validated);

        return back()->with('success', 'Answer added successfully.');
    }

    // Update an existing answer
    public function update(Request $request, $id)
    {
        $answer = CustomSetAnswer::findOrFail($id);

        $validated = $request->validate([
            'optionText' => 'nullable|string',
            'subjectiveAnswer' => 'nullable|string',
            'isCorrect' => 'sometimes|boolean',
        ]);

        $validated['isCorrect'] = $validated['isCorrect'] ?? false;

        $answer->update($validated);

        return back()->with('success', 'Answer updated successfully.');
    }

    // Delete an answer
    public function destroy($id)
    {
        $answer = CustomSetAnswer::findOrFail($id);
        $answer->delete();

        return back()->with('success', 'Answer deleted successfully.');
    }
}
