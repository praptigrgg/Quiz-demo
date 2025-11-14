<?php

use App\Http\Controllers\CascadeController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseSectionController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizQuestionController;
use App\Http\Controllers\QuizQuestionExcelController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
    return view('pages.admin.dashboard');
})->name('dashboard');

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/cascade/courses', [CascadeController::class, 'getCourses'])->name('cascade.courses');
    Route::get('/cascade/sections/{course}', [CascadeController::class, 'getSections'])->name('cascade.sections');
    Route::get('/cascade/lessons/{section}', [CascadeController::class, 'getLessons'])->name('cascade.lessons');
});

Route::get('/admin/quizzes/{quiz}/groups', [QuizController::class, 'getGroups']);


Route::prefix('admin')->name('admin.')->group(function () {

    // Quizzes
    Route::resource('quizzes', QuizController::class);
    Route::patch('quizzes/{quiz}/update-publish-status', [QuizController::class, 'updatePublishStatus'])
        ->name('quizzes.update-publish-status');

    // Courses
    Route::resource('courses', CourseController::class);

    Route::prefix('courses/{course}')->name('courses.')->group(function () {
        Route::resource('sections', CourseSectionController::class)->except(['show']);
    });

    // Lessons
    Route::prefix('courses-sections/{section}')->group(function () {
        Route::get('lessons/create', [LessonController::class, 'create'])->name('courses.lessons.create');
        Route::post('lessons', [LessonController::class, 'store'])->name('courses.lessons.store');
        Route::get('lessons/{lesson}/edit', [LessonController::class, 'edit'])->name('courses.lessons.edit');
        Route::put('lessons/{lesson}', [LessonController::class, 'update'])->name('courses.lessons.update');
        Route::delete('lessons/{lesson}', [LessonController::class, 'destroy'])->name('courses.lessons.destroy');
    });

    // Quiz question management
    Route::prefix('quizzes/{quiz}/questions')->name('quizzes.questions.')->group(function () {
        Route::get('/', [QuizQuestionController::class, 'index'])->name('index');
        Route::post('/', [QuizQuestionController::class, 'store'])->name('store');
        Route::get('import', [QuizQuestionController::class, 'import'])->name('import');
    });

    Route::prefix('quizzes-questions')->name('quizzes-questions.')->group(function () {
        Route::get('{id}/edit', [QuizQuestionController::class, 'edit'])->name('edit');
        Route::post('{id}/update', [QuizQuestionController::class, 'update'])->name('update');
        Route::get('{id}/delete', [QuizQuestionController::class, 'destroy'])->name('destroy');
        Route::post('replicate', [QuizQuestionController::class, 'replicate'])->name('replicate');
        Route::post('migrate', [QuizQuestionController::class, 'migrate'])->name('migrate');
        Route::post('filter', [QuizQuestionController::class, 'filter'])->name('filter');
    });

    // Excel upload
    Route::post('quizzes-questions-excel/store', [QuizQuestionExcelController::class, 'store'])
        ->name('quizzes-questions.excel.store');
});
