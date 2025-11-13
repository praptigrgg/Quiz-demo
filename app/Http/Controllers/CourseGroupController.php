<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\CourseGroup;
use Illuminate\Http\Request;

class CourseGroupController extends Controller
{
    // Show form to create a new group under a lesson
    public function create(Lesson $lesson)
    {
        return view('pages.admin.groups.create', compact('lesson'));
    }

    // Store new group
    public function store(Request $request, Lesson $lesson)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $lesson->courseGroups()->create($request->all());

        // Redirect back to sections index page for the course
        return redirect()->route('admin.courses.sections.index', $lesson->section->course_id)
                         ->with('success', 'Group added successfully!');
    }

    // Show form to edit a group
    public function edit(Lesson $lesson, CourseGroup $courseGroup)
    {
        return view('pages.admin.groups.edit', compact('lesson', 'courseGroup'));
    }

    // Update existing group
    public function update(Request $request, Lesson $lesson, CourseGroup $courseGroup)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $courseGroup->update($request->all());

        return redirect()->route('admin.courses.sections.index', $lesson->section->course_id)
                         ->with('success', 'Group updated successfully!');
    }

    // Delete group
    public function destroy(Lesson $lesson, CourseGroup $courseGroup)
    {
        $courseGroup->delete();

        return redirect()->route('admin.courses.sections.index', $lesson->section->course_id)
                         ->with('success', 'Group deleted successfully!');
    }

   
}
