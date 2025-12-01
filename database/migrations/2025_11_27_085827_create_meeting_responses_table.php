<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('meeting_responses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('meeting_assignment_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            // Polymorphic question reference
            $table->morphs('questionable'); // adds question_id + question_type (quiz_question/custom_set_question)

            // MCQ answer
            $table->foreignId('selected_option_id')->nullable(); // for MCQ only
            $table->boolean('is_correct')->nullable();           // null for subjective

            // Subjective answer
            $table->longText('subjective_answer')->nullable();

            $table->float('score')->default(0);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('meeting_responses');
    }
};

