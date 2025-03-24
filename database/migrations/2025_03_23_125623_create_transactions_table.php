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
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id(); // Primary key
                $table->string('code')->unique(); // Unique transaction code
                $table->enum('payment_method', ['cash', 'transfer', 'digital_wallet'])->nullable();
                $table->enum('payment_status', ['complete', 'pending', 'failed'])->nullable();
                $table->string('payment_proof')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('department_id');
                $table->timestamps();
    
                // Foreign key constraints
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
    
                // Indexing for performance optimization
                $table->index('code', 'idx_transactions_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions'); // Drops the table if it exists
    }
};