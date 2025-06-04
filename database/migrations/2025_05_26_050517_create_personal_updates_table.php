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
        Schema::create('personal_updates', function (Blueprint $table) {
            $table->id('updated_id');
            $table->string('personal_slug');

            // Snapshot fields
            $table->string('full_name');
            $table->date('birth_date');
            $table->enum('gender', ['male', 'female']);
            $table->string('region_code');
            $table->string('township_code');
            $table->string('citizenship');
            $table->string('serial_number');
            $table->string('nationality')->nullable();
            $table->string('religion')->nullable();
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();

            // Source role (e.g., Student, Employee)
            $table->string('updatable_slug');
            $table->string('updatable_type');
            
            $table->timestamps();

            $table->foreign('personal_slug')->references('slug')->on('personals')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_updates');
    }
};
