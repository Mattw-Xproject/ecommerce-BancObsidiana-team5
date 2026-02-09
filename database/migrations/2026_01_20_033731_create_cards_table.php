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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');

            // Datos Sensibles (Se guardarán encriptados, por eso TEXT o String largo)
            $table->text('card_number');
            $table->text('cvv');

            // Datos de Visualización y Enrutamiento
            //$table->string('bin', 6)->index(); // Primeros 6 dígitos (Prefijo 05 + 4)
            //$table->string('last_four', 4);    // Últimos 4 dígitos
            $table->date('expiration_date');
            $table->string('brand')->default('Obsidiana');

            // Lógica de Crédito (Historia de Usuario)
            $table->decimal('credit_limit', 15, 2);
            $table->enum('status', ['active', 'blocked'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
