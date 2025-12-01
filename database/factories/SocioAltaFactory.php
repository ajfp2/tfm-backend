<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SocioAlta>
 */
class SocioAltaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fk_tipoSocio' => $this->faker->numberBetween(1, 6), // Ajustar según tus tipos
            'fecha_alta' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'n_carnet' => $this->faker->numberBetween(0, 9999),
            'sin_correspondencia' => $this->faker->boolean(10), // 10% sin correspondencia
            'c_carta' => $this->faker->boolean(70), // 70% reciben cartas
            'c_email' => $this->faker->boolean(80), // 80% reciben emails
            'formaPago' => $this->faker->randomElement([1, 2]), // 1=Banco, 2=Efectivo
            'fichaMadrid' => $this->faker->boolean(90), // 90% tienen ficha Madrid
        ];
    }

    public function pagoBanco(): static
    {
        return $this->state(fn (array $attributes) => [
            'formaPago' => 1,
        ]);
    }

    public function pagoEfectivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'formaPago' => 2,
        ]);
    }
}
