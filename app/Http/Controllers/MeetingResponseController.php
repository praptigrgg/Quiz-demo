<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MeetingResponse;
use Illuminate\Support\Facades\Auth;

class MeetingResponseController extends Controller
{
   public function store(Request $request)
{
    $studentId = Auth::id();
    $responses = $request->input('responses', []);

    try {
        foreach ($responses as $resp) {
            // Validate required fields
            if (empty($resp['meeting_assignment_id']) || empty($resp['questionable_id']) || empty($resp['questionable_type'])) {
                throw new \Exception('Missing required fields in response payload');
            }

            MeetingResponse::create([
                'meeting_assignment_id' => (int)$resp['meeting_assignment_id'],
                'student_id' => $studentId,
                'questionable_id' => (int)$resp['questionable_id'],
                'questionable_type' => $resp['questionable_type'],
                'selected_option_id' => isset($resp['selected_option_id']) ? (int)$resp['selected_option_id'] : null,
                'subjective_answer' => $resp['subjective_answer'] ?? null,
                'is_correct' => isset($resp['is_correct']) ? (bool)$resp['is_correct'] : null,
                'score' => isset($resp['is_correct']) ? ($resp['is_correct'] ? 1 : 0) : 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Responses saved successfully'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

}
