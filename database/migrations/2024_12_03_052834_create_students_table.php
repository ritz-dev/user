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
            $table->string('slug')->unique();
            $table->string('personal_slug')->unique();
            $table->string('student_name');
            $table->string('student_number')->unique();
            $table->string('registration_number')->unique()->nullable();
            $table->string('school_name');
            $table->string('school_code')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('address')->nullable();
            $table->enum('status', ['enrolled', 'graduated', 'suspended', 'inactive'])->default('enrolled');
            $table->date('graduation_date')->nullable();
            $table->date('admission_date')->nullable();

            //Foreign key
            $table->foreign('personal_slug')->references('slug')->on('personals')->onDelete('cascade');

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
