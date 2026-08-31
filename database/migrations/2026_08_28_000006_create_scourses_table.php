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
        Schema::create('scourses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('studentId');
            $table->json('contents')->nullable();
            $table->timestamp('createdAt')->nullable();

            $table->foreign('studentId')->references('id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scourses');
    }
};
