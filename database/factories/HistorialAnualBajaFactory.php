<?php

namespace Database\Factories;

use App\Models\HistorialAnualBaja;
use App\Models\SocioBaja;
use App\Models\Temporada;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HistorialAnualBaja>
 */
class HistorialAnualBajaFactory extends Factory
{

    protected $model = HistorialAnualBaja::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Obtener un socio activo aleatorio
        $socioBaja = SocioBaja::inRandomOrder()->first();
        
        // Obtener una temporada aleatoria ya creada(IDs 1-4)
        $temporadaId = $this->faker->numberBetween(1, 4);

        // Importe aleatorio entre 20 y 50 euros
        $importe = $this->faker->randomFloat(2, 20, 50);

        // 80% de probabilidad de que esté pagado
        $pagado = $this->faker->boolean(80);

        return [
            'a_socio_baja' => $socioBaja->a_Persona,
            'a_temporada' => $temporadaId,            
            'cuota_pagada' => $pagado,
            'exento' => 0,
            'importe_pendiente' => ($pagado == false) ? $importe : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Estado: Cuota pagada (importe_pendiente = 0)
     */
    public function pagada(): static
    {
        return $this->state(fn (array $attributes) => [
            'cuota_pagada' => true,
            'exento' => false,
            'importe_pendiente' => 0,
        ]);
    }

    /**
     * Estado: Cuota pendiente (con importe)
     */
    public function pendiente(): static
    {
        return $this->state(fn (array $attributes) => [
            'cuota_pagada' => false,
            'exento' => false,
            'importe_pendiente' => $this->faker->randomFloat(2, 20, 50),
        ]);
    }

    /**
     * Estado: Exento de cuota (importe_pendiente = 0)
     */
    public function exento(): static
    {
        return $this->state(fn (array $attributes) => [
            'cuota_pagada' => false,
            'exento' => true,
            'importe_pendiente' => 0,
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
    public function paraSocioBaja(int $socioId): static
    {
        return $this->state(fn (array $attributes) => [
            'a_Persona' => $socioId,
        ]);
    }

    /**
     * Estado: Con importe pendiente específico
     */
    public function conImportePendiente(float $importe): static
    {
        return $this->state(fn (array $attributes) => [
            'cuota_pagada' => false,
            'exento' => false,
            'importe_pendiente' => $importe,
        ]);
    }
}
