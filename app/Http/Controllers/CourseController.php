<?php


namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::latest()->paginate(10);
        return view('pages.admin.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('pages.admin.courses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'thumbnail' => 'nullable|image|max:2048',
            'demo_video' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        $data = $request->only(['title', 'demo_video', 'description']);

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        Course::create($data);

        return redirect()->route('admin.courses.index')->with('success', 'Course created successfully!');
    }

    public function show(Course $course)
    {
        return view('pages.admin.courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        return view('pages.admin.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'thumbnail' => 'nullable|image|max:2048',
            'demo_video' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        $data = $request->only(['title', 'demo_video', 'description']);

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $course->update($data);

        return redirect()->route('admin.courses.index')->with('success', 'Course updated successfully!');
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('admin.courses.index')->with('success', 'Course deleted successfully!');
    }

   
}
