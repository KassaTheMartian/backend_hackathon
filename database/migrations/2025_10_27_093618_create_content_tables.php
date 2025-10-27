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
        // Post Categories table
        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('slug')->unique();
            $table->json('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('post_categories')->onDelete('set null');
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index('slug');
            $table->index('parent_id');
        });

        // Posts table
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('post_categories')->onDelete('restrict');
            
            $table->json('title');
            $table->json('slug')->comment('{"vi": "slug-vi", "en": "slug-en"}');
            $table->json('excerpt')->nullable();
            $table->json('content');
            
            $table->string('featured_image', 500)->nullable();
            $table->json('images')->nullable();
            
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            
            $table->integer('views_count')->default(0);
            $table->integer('reading_time')->nullable()->comment('Estimated reading time in minutes');
            
            $table->boolean('is_featured')->default(false);
            $table->boolean('allow_comments')->default(true);
            
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            
            $table->timestamps();

            $table->index('author_id');
            $table->index('category_id');
            $table->index('status');
            $table->index('published_at');
            // Fulltext index not supported for JSON columns
        });

        // Post Tags table
        Schema::create('post_tags', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('slug')->unique();
            $table->timestamps();

            $table->index('slug');
        });

        // Post Tag Pivot table
        Schema::create('post_tag_pivot', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('post_tags')->onDelete('cascade');
            
            $table->primary(['post_id', 'tag_id']);
        });

        // Promotions table
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->json('name');
            $table->json('description')->nullable();
            
            $table->enum('discount_type', ['percentage', 'fixed_amount']);
            $table->decimal('discount_value', 10, 2);
            
            $table->decimal('min_amount', 10, 2)->default(0.00);
            $table->decimal('max_discount', 10, 2)->nullable();
            
            $table->integer('max_uses')->nullable();
            $table->integer('max_uses_per_user')->default(1);
            $table->integer('used_count')->default(0);
            
            $table->enum('applicable_to', ['all', 'services', 'specific'])->default('all');
            $table->json('applicable_services')->nullable()->comment('Array of service IDs');
            
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_to')->nullable();
            
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();

            $table->index('code');
            $table->index(['valid_from', 'valid_to']);
        });

        // Promotion Usages table
        Schema::create('promotion_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->decimal('discount_amount', 10, 2);
            $table->timestamp('created_at')->useCurrent();

            $table->index('promotion_id');
            $table->index('user_id');
        });

        // Chat Sessions table
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            
            $table->enum('status', ['active', 'closed', 'transferred'])->default('active');
            $table->foreignId('assigned_to')->nullable()->constrained('staff')->onDelete('set null')->comment('Staff member ID');
            
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            
            $table->json('metadata')->nullable();
            
            $table->index('session_id');
            $table->index('user_id');
            $table->index('status');
        });

        // Chat Messages table
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('chat_sessions')->onDelete('cascade');
            $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('sender_type', ['user', 'staff', 'bot']);
            
            $table->text('message');
            $table->enum('message_type', ['text', 'image', 'file', 'system'])->default('text');
            
            $table->boolean('is_bot')->default(false);
            $table->decimal('bot_confidence', 3, 2)->nullable();
            
            $table->json('metadata')->nullable();
            
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('session_id');
            $table->index('created_at');
        });

        // Contact Submissions table
        Schema::create('contact_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            
            $table->enum('status', ['new', 'in_progress', 'resolved', 'closed'])->default('new');
            
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('response')->nullable();
            $table->timestamp('responded_at')->nullable();
            
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();

            $table->index('email');
            $table->index('status');
            $table->index('created_at');
        });

        // Settings table
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->enum('type', ['string', 'number', 'boolean', 'json'])->default('string');
            $table->string('group_name', 100)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index('key');
            $table->index('group_name');
        });

        // Activity Logs table
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('log_name');
            $table->text('description');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('causer_type')->nullable();
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->json('properties')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index(['subject_type', 'subject_id']);
            $table->index(['causer_type', 'causer_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('contact_submissions');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_sessions');
        Schema::dropIfExists('promotion_usages');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('post_tag_pivot');
        Schema::dropIfExists('post_tags');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('post_categories');
    }
};