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
        Schema::create('quizzes_questions', function (Blueprint $table) {
       $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->string('group_name')->nullable();
            $table->longText('questionText');
            $table->string('questionType'); // e.g., multiple_choice, single_choice, true_false
            $table->boolean('isMandatory')->default(true);
            $table->longText('explanation')->nullable();
            $table->boolean('isQuestionShuffle')->default(false);
            $table->boolean('isAnswerShuffle')->default(false);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes_questions');
    }
};
