<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class AccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Genera un número de cuenta tipo 'OBS-KW82...'
            'account_number' => 'OBS-' . strtoupper($this->faker->bothify('##??####')),
            'balance' => $this->faker->randomFloat(2, 0, 100000), // Saldo entre 0 y 100k
            'currency' => 'USD',
            // user_id se llenará automáticamente cuando uses ->for($user) o magic methods
            'user_id' => User::factory(),
        ];
    }
}
