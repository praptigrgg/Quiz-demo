<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('meeting_assignments', function (Blueprint $table) {
            $table->id();

            // The meeting identifier (can be Zoom ID or internal ID)
            $table->string('meeting_id');

            // Polymorphic relation fields
            $table->morphs('assignable'); // creates assignable_id and assignable_type

            $table->timestamp('assigned_at')->nullable();
            $table->integer('timer')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('meeting_assignments');
    }
};
