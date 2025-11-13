<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quizzes_options', function (Blueprint $table) {
                 $table->id();
            $table->foreignId('question_id')->constrained('quizzes_questions')->onDelete('cascade');
            $table->longText('optionText');
            $table->boolean('isCorrect')->default(false);
            // $table->boolean('isAnswerShuffle')->default(false);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes_options');
    }
};
