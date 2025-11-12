<?php

use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizQuestionController;
use App\Http\Controllers\QuizQuestionExcelController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
    return view('pages.admin.dashboard');
})->name('dashboard');

Route::prefix('admin')->name('admin.')->group(function () {

    // --- QUIZZES ---
    Route::resource('quizzes', QuizController::class);
    Route::patch('quizzes/{quiz}/update-publish-status', [QuizController::class, 'updatePublishStatus'])
        ->name('quizzes.update-publish-status');

    // --- QUIZ QUESTIONS ---
    Route::prefix('quizzes/{quiz}/questions')->name('quizzes.questions.')->group(function () {
        Route::get('/', [QuizQuestionController::class, 'index'])->name('index');
        Route::post('/', [QuizQuestionController::class, 'store'])->name('store');
        Route::get('import', [QuizQuestionController::class, 'import'])->name('import');
    });

    // --- QUIZ QUESTION MANAGEMENT ---
    Route::prefix('quizzes-questions')->name('quizzes-questions.')->group(function () {
        Route::get('{id}/edit', [QuizQuestionController::class, 'edit'])->name('edit');
        Route::post('{id}/update', [QuizQuestionController::class, 'update'])->name('update');
        Route::get('{id}/delete', [QuizQuestionController::class, 'destroy'])->name('destroy');
        Route::post('replicate', [QuizQuestionController::class, 'replicate'])->name('replicate');
        Route::post('migrate', [QuizQuestionController::class, 'migrate'])->name('migrate');
    });

    // --- EXCEL UPLOAD ---
    Route::post('quizzes-questions-excel/store', [QuizQuestionExcelController::class, 'store'])
        ->name('quizzes-questions.excel.store');
});

