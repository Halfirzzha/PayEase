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
        // Create users table
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name', 100); // User's full name with max length of 100 characters
            $table->string('email', 150)->unique(); // Unique email address with max length of 150 characters
            $table->timestamp('email_verified_at')->nullable(); // Email verification timestamp
            $table->string('password', 255); // Encrypted password
            $table->string('phone', 15)->nullable(); // Optional phone number with max length of 15 characters
            $table->string('photo')->nullable(); // Optional profile photo (e.g., file path)
            $table->string('scan_certificate')->nullable(); // Optional scanned certificate (e.g., file path)
            $table->rememberToken(); // Token for "remember me" functionality
            $table->timestamps(); // Created at and updated at timestamps

            // Indexing for performance optimization
            $table->index('email', 'idx_users_email'); // Explicitly named index for clarity
            $table->index('phone', 'idx_users_phone'); // Index for phone number
        });

        // Create password reset tokens table
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 150)->primary(); // Primary key for email with max length of 150 characters
            $table->string('token'); // Reset token
            $table->timestamp('created_at')->nullable(); // Token creation timestamp
        });

        // Create sessions table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // Primary key for session ID
            $table->foreignId('user_id')->nullable()->index(); // Foreign key to users table
            $table->string('ip_address', 45)->nullable(); // IP address (IPv4/IPv6)
            $table->text('user_agent')->nullable(); // User agent string
            $table->longText('payload'); // Session payload
            $table->integer('last_activity')->index(); // Last activity timestamp

            // Indexing for performance optimization
            $table->index('user_id', 'idx_sessions_user_id'); // Index for user ID
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users'); // Drops the users table
        Schema::dropIfExists('password_reset_tokens'); // Drops the password reset tokens table
        Schema::dropIfExists('sessions'); // Drops the sessions table
    }
};