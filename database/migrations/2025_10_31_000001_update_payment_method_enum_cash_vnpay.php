<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Thu gọn enum còn 'cash' và 'vnpay'
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cash','vnpay') NULL");
    }

    public function down(): void
    {
        // Khôi phục enum cũ rộng hơn nếu cần (điền theo schema trước đây)
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cash','card','stripe','paypal','bank_transfer','online','vnpay') NULL");
    }
};
