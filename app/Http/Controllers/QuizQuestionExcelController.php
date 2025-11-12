<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\QuizQuestion;
use App\Models\QuizOption;

class QuizQuestionExcelController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
            'quiz_id' => 'required|exists:quizzes,id',
        ]);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Skip header

            $questionText = $row[0];
            $questionType = $row[1] ?? 'multiple_choice';
            $options = array_slice($row, 2, -1);
            $correctOptionIndex = $row[count($row) - 1];

            $question = QuizQuestion::create([
                'quiz_id' => $request->quiz_id,
                'questionText' => $questionText,
                'questionType' => $questionType,
            ]);

            foreach ($options as $key => $optionText) {
                QuizOption::create([
                    'question_id' => $question->id,
                    'optionText' => $optionText,
                    'isCorrect' => ($key + 1 == $correctOptionIndex),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Questions imported successfully!');
    }
}
