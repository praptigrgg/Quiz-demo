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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('folder');
            $table->string('file_name');       // e.g. certificate.pdf
            $table->string('user_name');       // learner name
            $table->unsignedInteger('course_id');
            $table->string('course');
            $table->string('serial_no');
            $table->foreignId('template_id')

            ->constrained('certificate_templates');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
