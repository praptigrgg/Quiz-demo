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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();

            // Basic quiz info
            $table->text('quizTitle');
            $table->text('quizSlug')->nullable();
            $table->longText('quizDescription')->nullable();

            $table->unsignedBigInteger('course_id')->nullable();
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');

            // Pricing and enrollment
            $table->string('pricingType')->default('free'); // free, paid
            $table->integer('valid_days_after_enrollment')->nullable();
            $table->decimal('normal_price', 8, 2)->nullable();
            $table->decimal('discount_price', 8, 2)->nullable();
            $table->date('discount_start_date')->nullable();
            $table->date('discount_end_date')->nullable();
            $table->unsignedInteger('enrollCount')->default(0);

            // Quiz settings
            $table->unsignedInteger('quizDuration')->default(30);
            $table->unsignedInteger('quizTotalMarks')->default(100);
            $table->unsignedInteger('quizPassMark')->default(40);
            $table->float('correctAnswerMarks', 8, 2)->default(1);
            $table->float('negativeMarking', 8, 2)->default(0);

            // Quiz types and timing
            $table->boolean('is_one_time')->default(false);
            $table->boolean('is_live_quiz')->default(false);
            $table->dateTime('live_start_time')->nullable();
            $table->dateTime('live_end_time')->nullable();
            $table->boolean('is_quiz_group')->default(false);
            $table->json('quiz_groups')->nullable();

            // Media and display
            $table->string('quizThumbnail')->nullable();
            $table->unsignedBigInteger('selectedThumbnailId')->nullable();

            // Status and visibility
            $table->boolean('enableReview')->default(false);
            $table->boolean('is_publish')->default(false);
            $table->boolean('showInListQuiz')->default(false);

            // Ownership
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
