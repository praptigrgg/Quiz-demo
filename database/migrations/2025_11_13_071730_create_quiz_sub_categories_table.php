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
        Schema::create('quiz_sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('quiz_categories')->onDelete('cascade');
            $table->text('subCategoryName');
            $table->text('subCategorySlug')->unique();
            $table->longText('subCategoryDescription')->nullable();
            $table->string('subCategoryThumbnail')->nullable();
            $table->boolean('is_publish')->default(false);
            $table->unsignedBigInteger('position')->default(0)->unique();


            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_sub_categories');
    }
};
