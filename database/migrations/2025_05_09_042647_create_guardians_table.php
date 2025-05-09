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
            $table->string('slug')->uniqid();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('personal_id')->constrained('personals')->onDelete('cascade');
            $table->enum('relation', ['father', 'mother', 'guardian']);
            $table->string('occupation')->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['student_id', 'relation']);
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
