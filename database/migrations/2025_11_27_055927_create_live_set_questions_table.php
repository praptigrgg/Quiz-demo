<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_set_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_set_id')->constrained('live_sets')->onDelete('cascade');
            $table->string('questionType'); // mcq, true_false, subjective
            $table->longText('questionText');
            $table->boolean('isMandatory')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_set_questions');
    }
};
