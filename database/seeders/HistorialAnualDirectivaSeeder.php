<?php

namespace Database\Seeders;

// use Database\Factories\HistorialCargoDirectivoFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\HistorialCargoDirectiva;
use App\Models\SocioAlta;
use App\Models\Temporada;
use App\Models\JuntaDirectiva;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Seeder;

class HistorialAnualDirectivaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar tabla si existe (opcional)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        HistorialCargoDirectiva::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('🔄 Generando historial de cargos directivos...');

        // Obtener todas las personas (socios)
        $personas = SocioAlta::all();
        
        if ($personas->isEmpty()) {
            $this->command->error('⚠️  No hay personas en socios_personas');
            return;
        }

        // Obtener cargos de junta directiva
        $cargos = JuntaDirectiva::all();
        
        if ($cargos->isEmpty()) {
            $this->command->error('⚠️  No hay cargos en junta_directiva. Ejecuta JuntaDirectivaSeeder primero');
            return;
        }

        // Obtener temporadas (IDs 1-4)
        $temporadas = Temporada::whereIn('id', [1, 2, 3, 4])->get();

        if ($temporadas->isEmpty()) {
            $this->command->error('⚠️  No se encontraron temporadas con IDs 1-4');
            return;
        }

        $this->command->info("👥 Personas disponibles: {$personas->count()}");
        $this->command->info("👔 Cargos disponibles: {$cargos->count()}");
        $this->command->info("📅 Temporadas: {$temporadas->count()}");

        $totalAsignaciones = 0;

        // Por cada temporada
        foreach ($temporadas as $temporada) {
            $this->command->info("📋 Asignando cargos para temporada: {$temporada->temporada}");

            // Estrategia: Asignar un conjunto de personas a la junta directiva
            // Seleccionar 7-12 personas aleatorias para formar la junta de esta temporada
            $numMiembrosJunta = rand(7, min(12, $personas->count()));
            $miembrosJunta = $personas->random($numMiembrosJunta);

            // Cargos principales que siempre deben estar (si existen)
            $cargosPrincipales = [
                'Presidente',
                'Vicepresidente',
                'Secretario',
                'Tesorero'
            ];

            $cargosAsignados = collect();
            $personasAsignadas = collect();

            // 1. Asignar cargos principales primero (1 persona por cargo)
            foreach ($cargosPrincipales as $nombreCargo) {
                $cargo = $cargos->firstWhere('cargo', $nombreCargo);
                
                if (!$cargo) {
                    continue; // Si no existe el cargo, continuar
                }

                // Seleccionar una persona que aún no tenga este cargo en esta temporada
                $personaDisponible = $miembrosJunta->first(function ($persona) use ($personasAsignadas) {
                    return !$personasAsignadas->contains($persona->a_Persona);
                });

                if ($personaDisponible) {
                    // Crear asignación
                    HistorialCargoDirectiva::create([
                        'a_temporada' => $temporada->id,
                        'a_persona' => $personaDisponible->a_Persona,
                        'a_cargo' => $cargo->id,
                    ]);

                    $personasAsignadas->push($personaDisponible->a_Persona);
                    $cargosAsignados->push($cargo->id);
                    $totalAsignaciones++;
                }
            }

            // 2. Asignar cargos restantes (vocales, etc.) a las personas restantes
            $personasRestantes = $miembrosJunta->reject(function ($persona) use ($personasAsignadas) {
                return $personasAsignadas->contains($persona->Id_Persona);
            });

            $cargosRestantes = $cargos->reject(function ($cargo) use ($cargosAsignados) {
                return $cargosAsignados->contains($cargo->id);
            });

            foreach ($personasRestantes as $persona) {
                if ($cargosRestantes->isEmpty()) {
                    break; // No hay más cargos disponibles
                }

                // Asignar un cargo aleatorio de los restantes
                $cargoAleatorio = $cargosRestantes->random();

                // Crear asignación
                HistorialCargoDirectiva::create([
                    'a_temporada' => $temporada->id,
                    'a_persona' => $persona->a_Persona,
                    'a_cargo' => $cargoAleatorio->id,
                ]);

                // Remover cargo de disponibles (cada cargo solo una vez por temporada)
                $cargosRestantes = $cargosRestantes->reject(function ($cargo) use ($cargoAleatorio) {
                    return $cargo->id == $cargoAleatorio->id;
                });

                $totalAsignaciones++;
            }

            $asignacionesTemporada = $personasAsignadas->count() + $personasRestantes->count();
            $this->command->info("✅ Cargos asignados para {$temporada->temporada}: {$asignacionesTemporada}");
        }

        $this->command->newLine();
        $this->command->info('📊 RESUMEN DE CARGOS ASIGNADOS:');
        $this->command->info("   Total asignaciones: {$totalAsignaciones}");
        $this->command->info("   Promedio por temporada: " . round($totalAsignaciones / $temporadas->count(), 1));
        $this->command->newLine();
    }

    
}
