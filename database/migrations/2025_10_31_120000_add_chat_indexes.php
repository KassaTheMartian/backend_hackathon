<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            // Speed up polling: WHERE chat_session_id = ? AND id > ? ORDER BY id
            $table->index(['chat_session_id', 'id'], 'chat_messages_session_id_id_idx');

            // Optional: if ordering by created_at is used elsewhere
            $table->index(['chat_session_id', 'created_at'], 'chat_messages_session_id_created_at_idx');
        });

        Schema::table('chat_sessions', function (Blueprint $table) {
            // Useful for housekeeping and recent-activity queries
            $table->index('last_activity', 'chat_sessions_last_activity_idx');
            $table->index(['is_active', 'last_activity'], 'chat_sessions_active_last_activity_idx');
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex('chat_messages_session_id_id_idx');
            $table->dropIndex('chat_messages_session_id_created_at_idx');
        });

        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->dropIndex('chat_sessions_last_activity_idx');
            $table->dropIndex('chat_sessions_active_last_activity_idx');
        });
    }
};


