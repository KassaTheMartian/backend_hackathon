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
        Schema::table('staff', function (Blueprint $table) {
            // First, migrate any existing data from staff to users if user_id is null
            // This ensures we don't lose any data
            $this->migrateStaffDataToUsers();
            
            // Drop the existing foreign key constraint
            $table->dropForeign(['user_id']);
            
            // Make user_id NOT NULL and unique
            $table->foreignId('user_id')->nullable(false)->change();
            $table->unique('user_id');
            
            // Recreate foreign key constraint with CASCADE
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Remove duplicate fields that should come from users table
            $table->dropColumn(['name', 'email', 'phone', 'avatar']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            // Add back the duplicate fields
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('avatar', 500)->nullable();
            
            // Drop the CASCADE foreign key constraint
            $table->dropForeign(['user_id']);
            
            // Remove unique constraint and make user_id nullable again
            $table->dropUnique(['user_id']);
            $table->foreignId('user_id')->nullable()->change();
            
            // Recreate the original foreign key constraint with SET NULL
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Migrate staff data to users table for staff records without user_id
     */
    private function migrateStaffDataToUsers(): void
    {
        // Get all staff records that don't have a user_id
        $staffWithoutUser = DB::table('staff')
            ->whereNull('user_id')
            ->whereNotNull('name')
            ->get();

        foreach ($staffWithoutUser as $staff) {
            // Create a user record for this staff member
            $userId = DB::table('users')->insertGetId([
                'name' => $staff->name,
                'email' => $staff->email,
                'phone' => $staff->phone,
                'avatar' => $staff->avatar,
                'is_active' => $staff->is_active,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update the staff record with the new user_id
            DB::table('staff')
                ->where('id', $staff->id)
                ->update(['user_id' => $userId]);
        }
    }
};
