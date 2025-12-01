<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SocioBaja>
 */
class SocioBajaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $motivos = [
            'Falta de pago de cuotas',
            'Traslado a otra ciudad',
            'Baja voluntaria',
            'Desacuerdo con la directiva',
            'Motivos personales',
            'Defunción'
        ];

        return [            
            'fecha_baja' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'motivo_baja' => $this->faker->randomElement($motivos),
            'deudor' => $this->faker->boolean(20), // 20% son deudores
            'deuda' => function (array $attributes) {
                return $attributes['deudor'] ? $this->faker->randomFloat(2, 20, 500) : 0;
            },
            'n_carnet' => $this->faker->numberBetween(0, 9999),
            'sin_correspondencia' => $this->faker->boolean(10),
            'c_carta' => $this->faker->boolean(70),
            'c_email' => $this->faker->boolean(80),
            'formaPago' => $this->faker->randomElement([1, 2]),
            'fichaMadrid' => $this->faker->boolean(90),
        ];        
    }

    public function deudor(): static
    {
        return $this->state(fn (array $attributes) => [
            'deudor' => true,
            'deuda' => $this->faker->randomFloat(2, 50, 1000),
        ]);
    }
}
