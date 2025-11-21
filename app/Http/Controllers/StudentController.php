<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
     public function showRegisterForm() {
        return view('pages.student.register');
    }

    public function register(Request $request) {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:students',
            'password' => 'required|min:6',
        ]);

        Student::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('student.login')->with('success', 'Registration successful!');
    }

    public function showLoginForm() {
        return view('pages.student.login');
    }

    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('student')->attempt($credentials)) {
            return redirect()->route('zoom.joinForm');
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function logout() {
        Auth::guard('student')->logout();
        return redirect()->route('student.login');
    }
}
