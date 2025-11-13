<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Quiz;
use Illuminate\Http\Request;

class CourseSectionController extends Controller
{
    /**
     * Display a listing of sections for a course.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\View\View
     */
    public function index(Course $course)
    {
        $sections = $course->sections;
        return view('pages.admin.course_sections.index', compact('course', 'sections'));
    }

    /**
     * Show the form for creating a new section.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\View\View
     */
    public function create(Course $course)
    {
        $allQuizzes = Quiz::all();
        return view('pages.admin.course_sections.create', compact('course', 'allQuizzes'));
    }

    /**
     * Store a newly created section in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Course $course)
    {
        /** @var \Illuminate\Http\Request $request */
        /** @var \App\Models\Course $course */

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'quizzes' => 'array',
            'quizzes.*' => 'exists:quizzes,id',
        ]);

        $section = $course->sections()->create($request->only('title', 'description', 'order'));

        if ($request->filled('quizzes')) {
            $section->quizzes()->attach($request->quizzes);
        }

        return redirect()
            ->route('admin.courses.sections.index', $course->id)
            ->with('success', 'Section added successfully!');
    }

    /**
     * Show the form for editing the specified section.
     *
     * @param  \App\Models\Course  $course
     * @param  \App\Models\CourseSection  $section
     * @return \Illuminate\View\View
     */
    public function edit(Course $course, CourseSection $section)
    {
        $allQuizzes = Quiz::all();
        return view('pages.admin.course_sections.edit', compact('course', 'section', 'allQuizzes'));
    }

    /**
     * Update the specified section in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @param  \App\Models\CourseSection  $section
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Course $course, CourseSection $section)
    {
        /** @var \Illuminate\Http\Request $request */
        /** @var \App\Models\CourseSection $section */
        /** @var \App\Models\Course $course */

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'quizzes' => 'array',
            'quizzes.*' => 'exists:quizzes,id',
        ]);

        $section->update($request->only('title', 'description', 'order'));
        $section->quizzes()->sync($request->input('quizzes', []));

        return redirect()
            ->route('admin.courses.sections.index', $course->id)
            ->with('success', 'Section updated successfully!');
    }

    /**
     * Remove the specified section from storage.
     *
     * @param  \App\Models\Course  $course
     * @param  \App\Models\CourseSection  $section
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Course $course, CourseSection $section)
    {
        /** @var \App\Models\CourseSection $section */
        /** @var \App\Models\Course $course */

        $section->delete();

        return redirect()
            ->route('admin.courses.sections.index', $course->id)
            ->with('success', 'Section deleted successfully!');
    }


}
