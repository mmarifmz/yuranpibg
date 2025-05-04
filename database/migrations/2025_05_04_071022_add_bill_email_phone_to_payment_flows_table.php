<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('payment_flow', function (Blueprint $table) {
        $table->string('bill_email')->nullable()->after('bill_code');
        $table->string('bill_phone')->nullable()->after('bill_email');
    });
}

    public function down(): void
    {
        Schema::table('payment_flow', function (Blueprint $table) {
            $table->dropColumn(['bill_email', 'bill_phone']);
        });
    }
};
