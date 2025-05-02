<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->string('family_id'); // NO SIRI KELUARGA
            $table->string('student_name');        // NAMA MURID
            $table->string('class_name');           // KELAS
            $table->enum('payment_status', ['pending', 'paid'])->default('pending');
            $table->decimal('amount_due', 8, 2)->default(100.00); // Default PIBG Fee RM100
            $table->decimal('amount_paid', 8, 2)->nullable();
            $table->string('payment_reference')->nullable(); // ToyyibPay Ref
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};