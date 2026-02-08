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

        // 1. Crear infraestructura para el usuario Demo:
        // Flujo: 1 Usuario -> 2 Cuentas -> Cada cuenta 1 Tarjeta -> Cada tarjeta 10 Transacciones
        Account::factory(2)
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
