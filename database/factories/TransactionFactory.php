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
    return [
        'merchant_name' => $this->faker->company(),
        'amount' => $this->faker->randomFloat(2, 5, 1000),
        'status' => 'approved',
        // ELIMINA cualquier línea que diga 'account_id' => ...
        // Laravel se encargará de llenar 'card_id' automáticamente
        // gracias a la magia de las relaciones.
    ];
}
}
