<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\CourseSection;
use App\Models\Lesson;

class CascadeController extends Controller
{
    // 🟩 Get all courses
    public function getCourses()
    {
        $courses = Course::select('id', 'title as courseName')->get();
        return response()->json($courses);
    }

    // 🟦 Get sections by course
    public function getSections($courseId)
    {
        $sections = CourseSection::where('course_id', $courseId)
            ->select('id', 'title as sectionName')
            ->get();

        return response()->json($sections);
    }

    // 🟨 Get lessons by section
    public function getLessons($sectionId)
    {
        $lessons = Lesson::where('course_section_id', $sectionId)
            ->select('id', 'title as lessonName')
            ->get();

        return response()->json($lessons);
    }

    // 🟧 Get groups by lesson
    public function getGroups($lessonId)
    {
        $groups = CourseGroup::where('lesson_id', $lessonId)
            ->select('title as name')
            ->get();

        return response()->json($groups);
    }
}
