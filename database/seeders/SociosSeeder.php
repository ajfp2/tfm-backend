<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SocioPersona;
use App\Models\SocioAlta;
use App\Models\SocioBaja;
use Illuminate\Support\Facades\DB;

class SociosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Quitamos claves ajenas temporalmente
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Si se quiere vacias las tablas (opcional)
        // SocioBaja::truncate();
        // SocioAlta::truncate();
        // SocioPersona::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Obtener el último número de socio
        $ultimoNsocio = SocioAlta::max('nsocio') ?? 0;

        // Generar 50 socios ACTIVOS
        $this->command->info('Generando 50 socios activos...');
        
        for ($i = 1; $i <= 50; $i++) {
            // Crear persona
            $persona = SocioPersona::factory()->completo()->create();
            
            // Crear alta
            $ultimoNsocio++;
            SocioAlta::factory()->create([
                'a_Persona' => $persona->Id_Persona,
                'nsocio' => $ultimoNsocio,
            ]);
            
            $this->command->info("Socio activo {$i}/50 creado (Nº {$ultimoNsocio})");
        }

        // Generar 20 socios DE BAJA
        $this->command->info('Generando 20 socios de baja...');
        for ($i = 1; $i <= 20; $i++) {
            // Crear persona
            $persona = SocioPersona::factory()->completo()->create();
            
            // Obtener datos para la baja
            $ultimoNsocio++;
            $fechaAlta = fake()->dateTimeBetween('-10 years', '-2 years');
            $fechaBaja = fake()->dateTimeBetween($fechaAlta, 'now');
            
            // Crear baja (sin crear alta)
            SocioBaja::factory()->create([
                'a_Persona' => $persona->Id_Persona,
                'nsocio' => $ultimoNsocio,
                'fk_tipoSocio' => fake()->numberBetween(1, 6),
                'fecha_alta' => $fechaAlta,
                'fecha_baja' => $fechaBaja,
            ]);
            
            $this->command->info("Socio de baja {$i}/20 creado (Nº {$ultimoNsocio})");
        }

        // Generar 5 socios de baja DEUDORES
        $this->command->info('Generando 5 socios deudores...');
        
        for ($i = 1; $i <= 5; $i++) {
            $persona = SocioPersona::factory()->completo()->create();
            
            $ultimoNsocio++;
            $fechaAlta = fake()->dateTimeBetween('-10 years', '-1 year');
            $fechaBaja = fake()->dateTimeBetween($fechaAlta, 'now');
            
            SocioBaja::factory()->deudor()->create([
                'a_Persona' => $persona->Id_Persona,
                'nsocio' => $ultimoNsocio,
                'fk_tipoSocio' => fake()->numberBetween(1, 6),
                'fecha_alta' => $fechaAlta,
                'fecha_baja' => $fechaBaja,
                'motivo_baja' => 'Falta de pago de cuotas',
            ]);
            
            $this->command->info("Socio deudor {$i}/5 creado (Nº {$ultimoNsocio})");
        }

        $this->command->info('¡Seeders completados!');
        $this->command->info('Total: 50 activos + 25 bajas (5 deudores)');
    }
}
