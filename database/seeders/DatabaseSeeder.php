<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use App\Models\Account;
use App\Models\Card;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Usuario de Prueba (Para que puedas hacer Login)
        $demoUser = User::factory()->create([
            'name' => 'Cliente Demo',
            'email' => 'cliente@bancobsidiana.com',
            'password' => bcrypt('password'),
        ]);
        // 2. Cuenta con saldo
    $account = \App\Models\Account::create([
        'user_id' => $demoUser->id,
        'account_number' => '1234567890',
        'balance' => 1500.00,
    ]);

    // 3. LA TARJETA EXACTA PARA POSTMAN
    $card = \App\Models\Card::create([
        'account_id'      => $account->id,
        'card_number'     => '0551116109157796', // Se guarda tal cual
        'cvv'             => '683',             // Se encripta por el modelo
        'expiration_date' => '2031-01-31',
        'brand'           => 'Obsidiana Credit',
        'credit_limit'    => 2000.00,
        'status'          => 'active'
    ]);

        // 1. Crear infraestructura para el usuario Demo:
        // Flujo: 1 Usuario -> 2 Cuentas -> Cada cuenta 1 Tarjeta -> Cada tarjeta 10 Transacciones
        Account::factory(1)
            ->for($demoUser)
            ->has(
                Card::factory()
                    ->count(1)
                    ->has(Transaction::factory()->count(10)) // <-- IMPORTANTE: Ahora cuelga de la Tarjeta
            )
            ->create();

        // 2. Relleno Masivo
        User::factory(3)
            ->has(
                Account::factory()->has(
                    Card::factory()->has(Transaction::factory()->count(5)) // <-- Jerarquía anidada
                )
            )
            ->create();
            }
}
