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
        Schema::table('promotion_usages', function (Blueprint $table) {
            if (!Schema::hasColumn('promotion_usages', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotion_usages', function (Blueprint $table) {
            if (Schema::hasColumn('promotion_usages', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
};


