<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $drops = [
                'stripe_payment_intent_id',
                'stripe_charge_id',
                'gateway_response',
                'refund_amount',
                'refund_reason',
                'refunded_at',
                'metadata',
            ];
            foreach ($drops as $col) {
                if (Schema::hasColumn('payments', $col)) {
                    $table->dropColumn($col);
    }
            }
        });
        // Nếu muốn thu gọn enum: payment_method về ['cash','online','vnpay'] có thể dùng raw
        // DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cash','online','vnpay') NULL");
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_charge_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->text('refund_reason')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->json('metadata')->nullable();
        });
    }
};
