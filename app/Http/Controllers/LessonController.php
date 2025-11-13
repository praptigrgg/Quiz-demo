<?php

namespace App\Http\Controllers;

use App\Models\CourseSection;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function create(CourseSection $section)
    {
        return view('pages.admin.lessons.create', compact('section'));
    }

    public function store(Request $request, CourseSection $section)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'video_url' => 'nullable|string',
        ]);

        $section->lessons()->create($request->only('title', 'description', 'order', 'video_url'));

        return redirect()->route('admin.courses.sections.index', $section->course_id)
            ->with('success', 'Lesson added successfully!');
    }

    public function edit(CourseSection $section, Lesson $lesson)
    {
        return view('pages.admin.lessons.edit', compact('section', 'lesson'));
    }

    public function update(Request $request, CourseSection $section, Lesson $lesson)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'video_url' => 'nullable|string',
        ]);

        $lesson->update($request->only('title', 'description', 'order', 'video_url'));

        return redirect()->route('admin.courses.sections.index', $section->course_id)
            ->with('success', 'Lesson updated successfully!');
    }

    public function destroy(CourseSection $section, Lesson $lesson)
    {
        $lesson->delete();

        return redirect()->route('admin.courses.sections.index', $section->course_id)
            ->with('success', 'Lesson deleted successfully!');
    }


}

