<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Card>
 */

class CardFactory extends Factory
{
    public function definition(): array
    {
        // Lógica del Prefijo 05 + 14 dígitos aleatorios
        $prefix = '05';
        $randomPart = $this->faker->numerify('##############'); // 14 hash = 14 dígitos
        $pan = $prefix . $randomPart; // Total 16 dígitos

        return [
            'card_number' => $pan, // El modelo lo encriptará solo
            'cvv' => $this->faker->numerify('###'),
            'bin' => substr($pan, 0, 6), // Primeros 6 (ej: 051234)
            'last_four' => substr($pan, -4), // Últimos 4
            'expiration_date' => $this->faker->dateTimeBetween('+1 year', '+5 years'),
            'brand' => 'Obsidiana Credit',
            'credit_limit' => $this->faker->randomElement([1000, 2000, 5000, 10000]),
            'status' => 'active',
        ];
    }
}
