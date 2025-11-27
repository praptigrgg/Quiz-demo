<?php

use App\Events\ZoomMessageSent;
use App\Http\Controllers\Auth\StudentAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ZoomController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\CascadeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\QuizQuestionController;
use App\Http\Controllers\CourseSectionController;
use App\Http\Controllers\CustomSetAnswerController;
use App\Http\Controllers\CustomSetController;
use App\Http\Controllers\CustomSetQuestionController;
use App\Http\Controllers\QuizQuestionExcelController;

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


    Route::post('quizzes/{id}/assign', [QuizController::class, 'assignQuiz'])
        ->name('quizzes.assign');
});

Route::prefix('admin')->name('admin.')->group(function () {

    // Custom Sets CRUD
    Route::get('custom_sets', [CustomSetController::class, 'index'])->name('custom_sets.index');
    Route::get('custom_sets/create', [CustomSetController::class, 'create'])->name('custom_sets.create');
    Route::post('custom_sets', [CustomSetController::class, 'store'])->name('custom_sets.store');
    Route::get('custom_sets/{set}/edit', [CustomSetController::class, 'edit'])->name('custom_sets.edit');
    Route::put('custom_sets/{set}', [CustomSetController::class, 'update'])->name('custom_sets.update');
    Route::delete('custom_sets/{set}', [CustomSetController::class, 'destroy'])->name('custom_sets.destroy');

    // Publish/unpublish toggle
    Route::patch('custom_sets/{set}/toggle-publish', [CustomSetController::class, 'togglePublish'])
        ->name('custom_sets.update-publish-status');

    // Assign Custom Set to Meeting (AJAX)
    Route::post('custom_sets/{set}/assign', [CustomSetController::class, 'assignToMeeting'])
        ->name('custom_sets.assign');
});
Route::prefix('admin/custom_sets/{set}/questions')->name('admin.custom_sets.questions.')->group(function () {

    // List questions
    Route::get('/', [CustomSetQuestionController::class, 'index'])->name('index');

    // Create/store new question
    Route::post('/', [CustomSetQuestionController::class, 'store'])->name('store');


    Route::get('import', [CustomSetQuestionController::class, 'import'])->name('import');

    // Update/delete specific question
    Route::put('{question}', [CustomSetQuestionController::class, 'update'])->name('update');

    Route::delete('{question}', [CustomSetQuestionController::class, 'destroy'])->name('destroy');
});
Route::prefix('admin/custom_sets/questions/{question}/answers')->name('questions.answers.')->group(function () {

    // Store new answer
    Route::post('/', [CustomSetAnswerController::class, 'store'])->name('store');

    // Update/delete specific answer
    Route::put('{answer}', [CustomSetAnswerController::class, 'update'])->name('update');
    Route::delete('{answer}', [CustomSetAnswerController::class, 'destroy'])->name('destroy');
});




//Frontend
Route::get('/login', [StudentAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [StudentAuthController::class, 'login'])->name('login.post');

Route::get('/register', [StudentAuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [StudentAuthController::class, 'register'])->name('register.post');

Route::post('/logout', [StudentAuthController::class, 'logout'])->name('logout');

Route::middleware('auth:student')->group(function () {
    // Show Zoom join form

    Route::get('/zoom/join', [ZoomController::class, 'showJoinForm'])->name('zoom.joinForm');
    // Handle join form submission
    Route::post('/zoom/join', [ZoomController::class, 'handleJoin'])->name('zoom.handleJoin');

    // Load meeting page with generated signature
    Route::get('/zoom/meeting/{meetingId}', [ZoomController::class, 'meeting'])->name('zoom.meeting');
});



Route::get('/send-test-popup', function () {
    event(new ZoomMessageSent("Hello! Test popup"));
    return "Message sent!";
});
