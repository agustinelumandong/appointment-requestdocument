<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bname' => 'Santo Tomas',
            'email' => 'kjbascogil@gmail.com',
            'phone' => '+91 8447 525 204',
            'currency' => 'PHP',
            'meta_title' => 'Santo',
        ];
    }
}
