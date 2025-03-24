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
        Schema::create('departments', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string("name", 100); // Department name with a max length of 100 characters and unique constraint
            $table->unsignedTinyInteger("semester")->unique(); // Positive integer for semester (e.g., 1-255)
            $table->unsignedInteger("cost"); // Positive integer for cost
            $table->timestamps(); // Created at and updated at timestamps

            // Indexing for performance optimization
            $table->index('name', 'idx_departments_name'); // Explicitly named index for clarity
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments'); // Drops the table if it exists
    }
};