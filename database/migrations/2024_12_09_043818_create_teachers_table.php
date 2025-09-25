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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->uuid('slug')->unique();
            $table->string('personal_slug')->unique();
            $table->string('teacher_name');
            $table->string('teacher_code')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('address')->nullable();
            $table->string('qualification')->nullable();
            $table->string('subject')->nullable();
            $table->integer('experience_years')->default(0);
            $table->decimal('salary',10,2);
            $table->date('hire_date');
            $table->enum('status', ['active', 'resigned', 'on_leave'])->default('active');
            $table->enum('employment_type', ['fulltime', 'parttime', 'contract'])->default('fulltime');

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
        Schema::dropIfExists('teachers');
    }
};
