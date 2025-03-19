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
            $table->string('slug');
            $table->string('name');
            $table->enum('gender',['male','female']);
            $table->date('dob');
            $table->string('address');
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->string('register_code')->nullable();
            $table->unique(['state', 'district', 'register_code'], 'unique_state_district_register_code');
            $table->softDeletes();
            $table->timestamps();
        });

        // Check constraint (MySQL 8.0+ or PostgreSQL)
        // DB::statement("
        // ALTER TABLE personals
        //     ADD CONSTRAINT state_district_register_null_check
        //     CHECK (
        //         (state IS NULL AND district IS NULL AND register_code IS NULL) OR
        //         (state IS NOT NULL AND district IS NOT NULL AND register_code IS NOT NULL)
        //     )
        // ");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        // Schema::table('personals', function (Blueprint $table) {
        //     // Drop the constraint if supported by your DB
        //     DB::statement("ALTER TABLE personals DROP CONSTRAINT state_district_register_null_check");

        //     $table->dropUnique(['state', 'district', 'register_code']);
        // });

        Schema::dropIfExists('personals');
    }
};
