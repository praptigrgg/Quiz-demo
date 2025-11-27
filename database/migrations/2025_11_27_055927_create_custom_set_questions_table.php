<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_set_questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('set_id')
                ->constrained('custom_sets')
                ->onDelete('cascade');

            $table->string('group_name')->nullable();
            $table->longText('questionText');

            // Types: multiple_choice, single_choice, true_false, subjective
            $table->string('questionType');

            $table->boolean('isMandatory')->default(true);
            $table->longText('explanation')->nullable();

            // For objective types
            $table->boolean('shuffleQuestions')->default(false);
            $table->boolean('shuffleAnswers')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_set_questions');
    }
};
