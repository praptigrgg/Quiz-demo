<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Firebase\JWT\JWT;
use App\Events\QuizAssigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ZoomController extends Controller
{
    // public function welcome(){
    //     return view('welcome');
    // }
    // Show join form (meeting number, name, passcode)
    public function showJoinForm()
    {
        return view('zoom.join');
    }

    // Handle join form submission and save session
    public function handleJoin(Request $request)
    {
        $request->validate([
            'meeting_number' => 'required|numeric',
            'user_name'      => 'required|string|max:50',
            'passcode'       => 'nullable|string',
            'role'           => 'nullable|in:0,1',
        ]);

        // Save user info to session for later use
        session([
            'zoom_user_name' => $request->user_name,
            'zoom_role'      => (int) $request->role,
            'zoom_passcode'  => $request->passcode,
        ]);

        return redirect()->route('zoom.meeting', ['meetingId' => $request->meeting_number]);
    }

    // Load meeting page and generate static signature
    public function meeting(Request $request, $meetingId)
    {
        $userName = session('zoom_user_name', 'Guest');
        $role     = (int) session('zoom_role', 0);
        $passcode = session('zoom_passcode', '');

        return view('zoom', [
            'meetingId' => $meetingId,
            'signature' => $this->generateStaticSignature($meetingId, $role),
            'sdkKey'    => config('zoom.sdk_key'),
            'userName'  => $userName,
            'passCode'  => $passcode,
            'isHost'    => $role === 1,
        ]);
    }

    /**
     * Generate Zoom Web SDK signature (static)
     *
     * @param string $meetingNumber
     * @param int    $role 0 = participant, 1 = host
     * @return string JWT signature
     */
    private function generateStaticSignature(string $meetingNumber, int $role): string
    {
        $sdkKey    = config('zoom.sdk_key');       // Zoom SDK Key
        $sdkSecret = config('zoom.sdk_secret');    // Zoom SDK Secret

        $timestamp = round(microtime(true) * 1000) - 30000; // Zoom requires ms timestamp

        $payload = [
            'appKey'    => $sdkKey,
            'sdkKey'    => $sdkKey,
            'mn'        => $meetingNumber,
            'role'      => $role,
            'iat'       => floor($timestamp / 1000),
            'exp'       => floor($timestamp / 1000) + 7200, // 2 hours
            'tokenExp'  => floor($timestamp / 1000) + 7200,
        ];

        return JWT::encode($payload, $sdkSecret, 'HS256');
    }
    
}
