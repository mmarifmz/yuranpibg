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
        Schema::create('payment_flow', function (Blueprint $table) {
            $table->id();
            $table->string('family_id');
            $table->string('status'); // 'initiated', 'redirected', 'success', 'failed'
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('redirected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('bill_code')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_flow');
    }
};
