<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

public function definition(): array
{
    // Primero seleccionamos una tarjeta al azar (o creamos una)
    $card = \App\Models\Card::inRandomOrder()->first() ?? \App\Models\Card::factory()->create();

    return [
        'card_id' => $card->id,
        'account_id' => $card->account_id, // <--- Esto resuelve el error 1364
        'merchant_name' => fake()->company(),
        'amount' => fake()->randomFloat(2, 10, 2000),
        'fee' => fake()->randomFloat(2, 1, 5),
        'reference' => 'TXN-' . strtoupper(fake()->bothify('??###?')),
        'type' => 'PURCHASE',
        'currency' => 'USD',
        'status' => 'approved',
    ];
}
}
