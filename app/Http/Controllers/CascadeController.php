<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;

class CascadeController extends Controller
{
    // Get all courses
    public function getCourses()
    {
        return Course::select('id', 'title as courseName')->get();
    }

    // Get sections by course
    public function getSections($courseId)
    {
        return CourseSection::where('course_id', $courseId)
            ->select('id', 'title as sectionName')
            ->get();
    }

    // Get lessons by section
    public function getLessons($sectionId)
    {
        return Lesson::where('course_section_id', $sectionId)
            ->select('id', 'title as lessonName')
            ->get();
    }
}
