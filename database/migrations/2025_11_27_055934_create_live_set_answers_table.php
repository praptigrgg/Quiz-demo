<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('live_set_options', function (Blueprint $table) {
    $table->id();
    $table->foreignId('question_id')->constrained('live_set_questions')->onDelete('cascade');
    $table->string('optionText')->nullable();
    $table->boolean('isCorrect')->default(false);
    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('live_set_options');
    }
};
