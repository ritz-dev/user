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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->uuid('slug')->unique();
            $table->string('personal_slug')->unique();
            $table->string('employee_name');
            $table->string('employee_code')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('address')->nullable();
            $table->string('position')->nullable(); // e.g., Accountant, Janitor, Librarian
            $table->string('department')->nullable(); // e.g., Finance, Maintenance, Library
            $table->enum('employment_type', ['full-time', 'part-time', 'contract'])->default('full-time');
            $table->date('hire_date');
            $table->date('resign_date')->nullable();
            $table->integer('experience_years')->default(0);
            $table->decimal('salary', 10, 2)->default(0);
            $table->enum('status', ['active', 'resigned', 'on_leave', 'terminated'])->default('active');

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
        Schema::dropIfExists('employees');
    }
};
