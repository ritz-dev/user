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
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('personal_slug');
            $table->string('student_slug');
            $table->enum('relation', ['father', 'mother', 'guardian']);
            $table->string('occupation')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->softDeletes();
            $table->timestamps();

            //Foreign key
            $table->foreign('personal_slug')->references('slug')->on('personals')->onDelete('cascade');
            $table->foreign('student_slug')->references('slug')->on('students')->onDelete('cascade');

            $table->unique(['student_slug', 'relation']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
