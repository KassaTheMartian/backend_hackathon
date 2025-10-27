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
        // Add indexes for users table
        Schema::table('users', function (Blueprint $table) {
            // Index for email lookups (already has unique constraint, but explicit index for performance)
            $table->index('email');
            
            // Index for admin user queries
            $table->index('is_admin');
            
            // Composite index for admin + email queries
            $table->index(['is_admin', 'email']);
            
            // Index for created_at for sorting and filtering
            $table->index('created_at');
        });

        // Add indexes for demos table
        Schema::table('demos', function (Blueprint $table) {
            // Index for user_id foreign key lookups
            $table->index('user_id');
            
            // Index for is_active filtering (most common query)
            $table->index('is_active');
            
            // Composite index for user_id + is_active (common query pattern)
            $table->index(['user_id', 'is_active']);
            
            // Index for title searches and sorting
            $table->index('title');
            
            // Index for created_at sorting
            $table->index('created_at');
            
            // Composite index for active demos sorted by creation date
            $table->index(['is_active', 'created_at']);
            
            // Composite index for user's demos sorted by creation date
            $table->index(['user_id', 'created_at']);
        });

        // Add indexes for personal_access_tokens table
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Note: tokenable_type and tokenable_id indexes are already created by morphs()
            // in the create_personal_access_tokens_table migration
            
            // Index for last_used_at for cleanup operations
            $table->index('last_used_at');
            
            // Index for created_at for sorting
            $table->index('created_at');
        });

        // Add indexes for password_reset_tokens table
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            // Index for email lookups (already primary, but explicit for clarity)
            $table->index('email');
            
            // Index for created_at for cleanup operations
            $table->index('created_at');
        });

        // Add indexes for sessions table
        Schema::table('sessions', function (Blueprint $table) {
            // Note: user_id and last_activity indexes already exist from create_users_table migration
            
            // Composite index for user sessions cleanup
            $table->index(['user_id', 'last_activity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes for users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['is_admin']);
            $table->dropIndex(['is_admin', 'email']);
            $table->dropIndex(['created_at']);
        });

        // Drop indexes for demos table
        Schema::table('demos', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['user_id', 'is_active']);
            $table->dropIndex(['title']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['is_active', 'created_at']);
            $table->dropIndex(['user_id', 'created_at']);
        });

        // Drop indexes for personal_access_tokens table
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropIndex(['last_used_at']);
            $table->dropIndex(['created_at']);
        });

        // Drop indexes for password_reset_tokens table
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['created_at']);
        });

        // Drop indexes for sessions table
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'last_activity']);
        });
    }
};
