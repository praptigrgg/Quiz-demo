<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('live_sets', function (Blueprint $table) {
    $table->id();
    $table->longText('description')->nullable();
    $table->integer('timer')->default(30); // in seconds
    $table->unsignedBigInteger('created_by')->nullable();
    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('live_sets');
    }
};
