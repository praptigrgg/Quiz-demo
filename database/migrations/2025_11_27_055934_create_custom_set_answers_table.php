<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_set_answers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('question_id')
                ->constrained('custom_set_questions')
                ->onDelete('cascade');

            // For MCQ / objective
            $table->longText('optionText')->nullable();
            $table->boolean('isCorrect')->default(false);

            // For subjective answers
            $table->longText('subjectiveAnswer')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_set_answers');
    }
};
