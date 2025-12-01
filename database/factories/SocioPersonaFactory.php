<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SocioPersona>
 */
class SocioPersonaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sexo = $this->faker->randomElement(['H', 'M']);
        return [
            'Nombre' => $sexo === 'H' ? $this->faker->firstNameMale() : $this->faker->firstNameFemale(),
            'Apellidos' => $this->faker->lastName() . ' ' . $this->faker->lastName(),
            'DNI' => $this->generarDNI(),
            'Movil' => $this->faker->optional(0.8)->numerify('6########'),
            'Email' => $this->faker->optional(0.7)->safeEmail(),
            'Talla' => $this->faker->optional(0.6)->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
            'Sexo' => $sexo,
            'FNac' => $this->faker->optional(0.8)->dateTimeBetween('-70 years', '-18 years'),
            'Direccion' => $this->faker->optional(0.7)->streetAddress(),
            'CP' => $this->faker->optional(0.7)->numerify('#####'),
            'Poblacion' => $this->faker->optional(0.7)->numberBetween(1, 100), // Municipio al azar
            'Provincia' => $this->faker->optional(0.7)->numberBetween(1, 52), // Provincia al azar
            'Pais' => 60, // España por defecto
            'Nacionalidad' => 60, // Española por defecto
            'IBAN' => $this->faker->optional(0.5)->iban('ES'),
            'BIC' => $this->faker->optional(0.5)->swiftBicNumber(),
        ];
    }

    private function generarDNI(): string
    {
        $numero = $this->faker->numberBetween(10000000, 99999999);
        $letras = 'TRWAGMYFPDXBNJZSQVHLCKE';
        $letra = $letras[$numero % 23];
        
        return $numero . $letra;
    }

    public function completo(): static
    {
        return $this->state(fn (array $attributes) => [
            'Movil' => $this->faker->numerify('6########'),
            'Email' => $this->faker->safeEmail(),
            'Talla' => $this->faker->randomElement(['S', 'M', 'L', 'XL']),
            'FNac' => $this->faker->dateTimeBetween('-70 years', '-18 years'),
            'Direccion' => $this->faker->streetAddress(),
            'CP' => $this->faker->numerify('#####'),
            'IBAN' => $this->faker->iban('ES'),
            'BIC' => $this->generarBIC(),
        ]);
    }

    // public function joven(): static
    // {
    //     return $this->state(fn (array $attributes) => [
    //         'FNac' => $this->faker->dateTimeBetween('-30 years', '-18 years'),
    //     ]);
    // }


    // public function mayor(): static
    // {
    //     return $this->state(fn (array $attributes) => [
    //         'FNac' => $this->faker->dateTimeBetween('-80 years', '-50 years'),
    //     ]);
    // }

    private function generarBIC(): string
    {
        $bics = [
            'CAIXESBBXXX', // CaixaBank
            'BSCHESMMXXX', // Santander
            'BBVAESMMXXX', // BBVA
            'BSABESBBXXX', // Sabadell
            'BKBKESMMXXX', // Bankinter
            'INGDESMMXXX', // ING
            'OPENESMMXXX', // Openbank
        ];
        
        return $this->faker->randomElement($bics);
    }
}
