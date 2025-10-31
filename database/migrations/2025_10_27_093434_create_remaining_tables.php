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
        // Staff table
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('position', 100)->nullable();
            $table->json('specialization')->nullable()->comment('Array of service IDs or specialties');
            $table->json('bio')->nullable();
            $table->string('avatar', 500)->nullable();
            $table->integer('years_of_experience')->nullable();
            $table->json('certifications')->nullable();
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('total_reviews')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('branch_id');
            $table->index('rating');
        });

        // Branch Services pivot table
        Schema::create('branch_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->boolean('is_available')->default(true);
            $table->decimal('custom_price', 10, 2)->nullable()->comment('Override service price if needed');
            $table->timestamps();

            $table->unique(['branch_id', 'service_id']);
            $table->index('branch_id');
            $table->index('service_id');
        });

        // Staff Services pivot table
        Schema::create('staff_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['staff_id', 'service_id']);
        });

        // Bookings table
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 20)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')->comment('NULL for guest bookings');
            
            // Guest information (if user_id is NULL)
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('guest_phone', 20)->nullable();
            
            // Booking details
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('restrict');
            $table->foreignId('staff_id')->nullable()->constrained()->onDelete('set null');
            
            $table->date('booking_date');
            $table->time('booking_time');
            $table->integer('duration')->comment('Duration in minutes');
            
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('cancelled_at')->nullable();
            
            // Pricing
            $table->decimal('service_price', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);
            
            // Payment
            $table->enum('payment_status', ['pending', 'paid', 'refunded', 'failed'])->default('pending');
            $table->enum('payment_method', ['cash', 'card', 'online', 'stripe'])->nullable();
            
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            
            // Notifications
            $table->boolean('reminder_sent')->default(false);
            $table->boolean('confirmation_sent')->default(false);
            
            $table->timestamps();

            $table->index('user_id');
            $table->index('guest_email');
            $table->index('guest_phone');
            $table->index('booking_date');
            $table->index('booking_time');
            $table->index('status');
            $table->index(['branch_id', 'booking_date']);
            $table->index(['staff_id', 'booking_date']);
            $table->index('booking_code');
        });

        // Booking Status History table is created by dedicated migration 2025_10_31_000000_create_booking_status_histories_table

        // Payments table
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->string('payment_code', 50)->unique();
            
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            
            $table->enum('payment_method', ['cash', 'card', 'stripe', 'paypal', 'bank_transfer']);
            
            // Stripe/Payment Gateway
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_charge_id')->nullable();
            $table->json('gateway_response')->nullable();
            
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->text('refund_reason')->nullable();
            
            $table->json('metadata')->nullable();
            
            $table->timestamps();

            $table->index('booking_id');
            $table->index('status');
            $table->index('stripe_payment_intent_id');
            $table->index('payment_code');
        });

        // Reviews table
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            
            $table->integer('rating')->check('rating BETWEEN 1 AND 5');
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            
            // Detailed ratings (optional)
            $table->integer('service_quality_rating')->nullable()->check('service_quality_rating BETWEEN 1 AND 5');
            $table->integer('staff_rating')->nullable()->check('staff_rating BETWEEN 1 AND 5');
            $table->integer('cleanliness_rating')->nullable()->check('cleanliness_rating BETWEEN 1 AND 5');
            $table->integer('value_rating')->nullable()->check('value_rating BETWEEN 1 AND 5');
            
            $table->json('images')->nullable()->comment('Array of review images');
            
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_featured')->default(false);
            
            $table->text('admin_response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->integer('helpful_count')->default(0);
            
            $table->timestamps();

            $table->index('service_id');
            $table->index('user_id');
            $table->index('rating');
            $table->index('is_approved');
        });

        // OTP Verifications table
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('phone_or_email');
            $table->string('otp', 6);
            $table->enum('type', ['email', 'phone']);
            $table->enum('purpose', ['registration', 'password_reset', 'phone_verification']);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index('phone_or_email');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('booking_status_history');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('staff_services');
        Schema::dropIfExists('branch_services');
        Schema::dropIfExists('staff');
    }
};