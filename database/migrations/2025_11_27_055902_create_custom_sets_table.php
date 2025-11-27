<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_sets', function (Blueprint $table) {
            $table->id();

            // Basic info
            $table->string('title');
            $table->string('slug')->nullable();
            $table->longText('description')->nullable();

       
            // Pricing and validity
            $table->string('pricingType')->default('free'); // free, paid
            $table->integer('valid_days_after_enrollment')->nullable();
            $table->decimal('normal_price', 8, 2)->nullable();
            $table->decimal('discount_price', 8, 2)->nullable();
            $table->date('discount_start_date')->nullable();
            $table->date('discount_end_date')->nullable();
            $table->unsignedInteger('enrollCount')->default(0);

            // Grouping structure
            $table->boolean('has_groups')->default(false);
            $table->json('groups')->nullable();

            // Status
            $table->boolean('enable_review')->default(false);
            $table->boolean('is_published')->default(false);
            $table->boolean('show_in_list')->default(false);

            // Owner
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_sets');
    }
};
