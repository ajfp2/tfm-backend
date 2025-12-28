<?php

namespace Database\Factories;

use App\Models\HistorialAnual;
use App\Models\SocioAlta;
use App\Models\Temporada;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HistorialAnual>
 */
class HistorialAnualFactory extends Factory
{
    protected $model = HistorialAnual::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Obtener un socio activo aleatorio
        $socioActivo = SocioAlta::inRandomOrder()->first();
        
        // Obtener una temporada aleatoria ya creada(IDs 1-4)
        $temporadaId = $this->faker->numberBetween(1, 4);

        // Importe aleatorio entre 20 y 50 euros
        $importe = $this->faker->randomFloat(2, 20, 50);

        // 75% de probabilidad de que esté pagado
        $pagado = $this->faker->boolean(75);

        return [
            'a_socio' => $socioActivo->a_Persona,
            'a_temporada' => $temporadaId,            
            'cuota_pagada' => $pagado,
            'exento' => 0,
            'importe_pendiente' => ($pagado == false) ? $importe : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Estado: Cuota pagada
     */
    public function pagada(): static
    {
        return $this->state(fn (array $attributes) => [
            'pagado' => true,
            'fecha_pago' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'metodo_pago' => $this->faker->randomElement([
                'Efectivo',
                'Transferencia',
                'Domiciliación',
                'Bizum',
                'Tarjeta'
            ]),
        ]);
    }

    /**
     * Estado: Cuota pendiente
     */
    public function pendiente(): static
    {
        return $this->state(fn (array $attributes) => [
            'pagado' => false,
            'fecha_pago' => null,
            'metodo_pago' => null,
        ]);
    }

    /**
     * Estado: Para una temporada específica
     */
    public function paraTemporada(int $temporadaId): static
    {
        return $this->state(fn (array $attributes) => [
            'a_temporada' => $temporadaId,
        ]);
    }

    /**
     * Estado: Para un socio específico
     */
    public function paraSocio(int $socioId): static
    {
        return $this->state(fn (array $attributes) => [
            'a_socio' => $socioId,
        ]);
    }

    /**
     * Estado: Con importe específico
     */
    public function conImporte(float $importe): static
    {
        return $this->state(fn (array $attributes) => [
            'importe_pendiente' => $importe,
        ]);
    }
}
