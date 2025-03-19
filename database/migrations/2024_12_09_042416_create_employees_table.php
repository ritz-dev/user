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
            $table->string('slug');
            $table->unsignedBigInteger('personal_id');
            $table->foreign('personal_id')->references('id')->on('personals')->onDelete('cascade');
            $table->string('email')->unique()->nullable();
            $table->string('phonenumber')->unique();
            $table->string('department');
            $table->decimal('salary',10,2);
            $table->date('hire_date');
            $table->enum('status',['active', 'inactive', 'supspened', 'disabled'])->default('active');
            $table->enum('employment_type', ['full-time', 'part-time', 'contract'])->default('full-time');
            $table->rememberToken();
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
