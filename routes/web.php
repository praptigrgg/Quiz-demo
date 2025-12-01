<?php

use App\Events\ZoomMessageSent;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ZoomController;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\CascadeController;
use App\Http\Controllers\LiveSetController;
use App\Http\Controllers\CustomSetController;
use App\Http\Controllers\QuizQuestionController;
use App\Http\Controllers\CourseSectionController;
use App\Http\Controllers\CustomSetAnswerController;
use App\Http\Controllers\MeetingResponseController;
use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\CustomSetQuestionController;
use App\Http\Controllers\MeetingAssignmentController;
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

Route::prefix('admin/live')->group(function () {
       Route::get('index', [LiveSetController::class, 'index'])->name('admin.live.assign.index');
    Route::get('assign', [LiveSetController::class, 'assignPage'])->name('admin.live.assign.page');
    Route::post('store-and-assign', [LiveSetController::class, 'storeAndAssign'])->name('admin.live.storeAndAssign');
    Route::post('assign-to-meeting', [LiveSetController::class, 'assignToMeeting'])->name('admin.live.assignToMeeting');
    Route::get('search', [LiveSetController::class, 'search'])->name('admin.live.search');

    Route::get('assign/{assignment}/participants', [LiveSetController::class, 'participants'])
        ->name('admin.live.participants');
        Route::get('assign/{assignment}/student/{student}/answers', [LiveSetController::class, 'studentAnswers'])
    ->name('admin.live.student.answers');

    Route::delete('assign/{id}', [LiveSetController::class, 'destroy'])->name('admin.live.destroy');
});




Route::post('/pusher/auth', function (Request $request) {
    return Broadcast::auth($request);
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

    Route::post('/meeting-responses', [MeetingResponseController::class, 'store'])->name('meeting.responses.store');
});



Route::get('/send-test-popup', function () {
    event(new ZoomMessageSent("Hello! Test popup"));
    return "Message sent!";
});
