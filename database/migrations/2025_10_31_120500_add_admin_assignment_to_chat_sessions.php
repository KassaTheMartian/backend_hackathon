<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_user_id')->nullable()->after('user_id')->index();
            $table->timestamp('assigned_at')->nullable()->after('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->dropColumn(['assigned_user_id', 'assigned_at']);
        });
    }
};


