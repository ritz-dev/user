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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->unsignedBigInteger('personal_id');
            $table->foreign('personal_id')->references('id')->on('personals')->onDelete('cascade');
            $table->string('student_code');
            $table->string('name');
            $table->string('address');
            $table->string('email');
            $table->string('phonenumber');
            $table->string('pob');
            $table->string('nationality');
            $table->string('religion');
            $table->enum('blood_type', ['A', 'B', 'AB','O']);
            $table->enum('status', ['active', 'graduated', 'suspended', 'dropped']);
            $table->string('academic_level');
            $table->string('academic_year');
            $table->date('enrollment_date');
            $table->date('graduation_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
