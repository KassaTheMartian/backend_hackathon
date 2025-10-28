<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // payments: add transaction_id used by VNPay flow
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'transaction_id')) {
                $table->string('transaction_id', 100)->nullable()->after('payment_code');
            }
            $table->index('transaction_id');
        });

        // reviews: strengthen lookup and duplicate-prevention at DB level
        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['service_id', 'is_approved']);
            $table->index(['branch_id', 'is_approved']);
            // Prevent duplicate reviews per booking by same user (booking_id can be NULL; MySQL allows multiple NULLs)
            $table->unique(['user_id', 'booking_id']);
        });

        // otp_verifications: align enum values or widen to string if MySQL
        try {
            $driver = DB::getDriverName();
            if ($driver === 'mysql') {
                // Align enum set with actual application purposes
                DB::statement("ALTER TABLE otp_verifications MODIFY COLUMN purpose ENUM('verify_email','password_reset','guest_booking') NOT NULL");
            } else {
                // Fallback: add composite index to speed up lookups
                Schema::table('otp_verifications', function (Blueprint $table) {
                    $table->index(['phone_or_email', 'purpose', 'created_at']);
                });
            }
        } catch (\Throwable $e) {
            // If ALTER fails (e.g., unsupported driver), at least add the composite index
            Schema::table('otp_verifications', function (Blueprint $table) {
                $table->index(['phone_or_email', 'purpose', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // payments
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['transaction_id']);
            if (Schema::hasColumn('payments', 'transaction_id')) {
                $table->dropColumn('transaction_id');
            }
        });

        // reviews
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['service_id', 'is_approved']);
            $table->dropIndex(['branch_id', 'is_approved']);
            $table->dropUnique(['user_id', 'booking_id']);
        });

        // otp_verifications: best-effort revert composite index
        Schema::table('otp_verifications', function (Blueprint $table) {
            try {
                $table->dropIndex(['phone_or_email', 'purpose', 'created_at']);
            } catch (\Throwable $e) {
                // ignore
            }
        });
        // We don't attempt to revert enum set to previous unknown definition
    }
};


