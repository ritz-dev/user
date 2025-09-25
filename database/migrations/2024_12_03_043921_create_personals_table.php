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
        Schema::create('personals', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('full_name');
            $table->date('birth_date');
            $table->enum('gender',['male','female']);
            $table->string('region_code');
            $table->string('township_code');
            $table->string('citizenship');
            $table->string('serial_number');
            $table->string('nationality')->nullable();
            $table->string('religion')->nullable();
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();
            
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['region_code', 'township_code', 'serial_number'], 'region_township_serial_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personals');
    }
};
