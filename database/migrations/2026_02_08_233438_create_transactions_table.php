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
    Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            // Relaciones
            $table->foreignId('card_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained()->onDelete('cascade');

            // Datos del Comercio y Transacción
            $table->string('merchant_name'); // Ej: "COMERCIO_01"
            $table->string('reference')->unique(); // Ej: TXN-123456 (Trazabilidad)
            $table->string('type')->default('PURCHASE'); // PURCHASE, REFUND, TRANSFER

            // Dinero (Decimales para precisión financiera)
            $table->decimal('amount', 15, 2); // El monto de la compra
            $table->decimal('fee', 10, 2)->default(0.00); // La comisión generada
            $table->string('currency', 3)->default('USD');

            // Estado
            $table->enum('status', ['approved', 'declined', 'pending', 'routing'])->default('pending');
            $table->string('response_message')->nullable(); // Para guardar por qué falló

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
