<?php

namespace Database\Factories;

use App\Models\HistorialCargoDirectiva;
use App\Models\SocioAlta;
use App\Models\Temporada;
use App\Models\JuntaDirectiva;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HistorialCargoDirectiva>
 */
class HistorialCargoDirectivaFactory extends Factory
{
    protected $model = HistorialCargoDirectiva::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Obtener un socio activo aleatorio
        $socioActivo = SocioAlta::inRandomOrder()->first();
        
        // Obtener una temporada aleatoria (IDs 1-4)
        $temporadaId = $this->faker->numberBetween(1, 4);

        // Obtener un cargo directivo aleatorio
        $cargo = JuntaDirectiva::inRandomOrder()->first();


        return [            
            'a_temporada' => $temporadaId,
            'a_persona' => $socioActivo->a_Persona,
            'a_cargo' => $cargo->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
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
            'a_Persona' => $socioId,
        ]);
    }

    /**
     * Estado: Para un cargo específico
     */
    public function paraCargo(int $cargoId): static
    {
        return $this->state(fn (array $attributes) => [
            'a_cargo' => $cargoId,
        ]);
    }
}
