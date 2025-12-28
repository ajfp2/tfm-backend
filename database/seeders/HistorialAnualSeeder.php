<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\HistorialAnual;
use App\Models\SocioAlta;
use App\Models\Temporada;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HistorialAnualSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar tabla si existe (opcional)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        HistorialAnual::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('🔄 Generando historial de cuotas anuales...');

        // Obtener todos los socios activos
        $sociosActivos = SocioAlta::all();
        $totalSocios = $sociosActivos->count();

        $this->command->info("📊 Total de socios activos: {$totalSocios}");

        // Obtener temporadas (IDs 1-4)
        $temporadas = Temporada::whereIn('id', [1, 2, 3, 4])->get();

        if ($temporadas->isEmpty()) {
            $this->command->error('⚠️  No se encontraron temporadas con IDs 1-4');
            return;
        }

        $this->command->info("📅 Temporadas encontradas: {$temporadas->count()}");

        $totalCuotas = 0;
        $cuotasPagadas = 0;
        $cuotasPendientes = 0;

        // Por cada temporada
        foreach ($temporadas as $temporada) {
            $this->command->info("📋 Generando cuotas para temporada: {$temporada->temporada}");

            // Por cada socio activo
            foreach ($sociosActivos as $socio) {
                // Determinar importe según tipo de socio (si existe relación)
                $importe = $this->determinarImporteCuota($socio);

                // 75% probabilidad de que esté pagada
                $pagado = fake()->boolean(75);

                if ($pagado) {
                    $cuotasPagadas++;
                } else {
                    $cuotasPendientes++;
                }

                // Crear cuota
                HistorialAnual::factory()
                    ->paraSocio($socio->a_Persona)
                    ->paraTemporada($temporada->id)
                    ->conImporte($importe)
                    ->create();

                $totalCuotas++;
            }

            $this->command->info("✅ Cuotas generadas para {$temporada->temporada}: {$sociosActivos->count()}");
        }

        $this->command->newLine();
        $this->command->info('📊 RESUMEN DE CUOTAS GENERADAS:');
        $this->command->info("   Total cuotas: {$totalCuotas}");
        $this->command->info("   ✅ Pagadas: {$cuotasPagadas} (" . round(($cuotasPagadas / $totalCuotas) * 100, 2) . "%)");
        $this->command->info("   ⏳ Pendientes: {$cuotasPendientes} (" . round(($cuotasPendientes / $totalCuotas) * 100, 2) . "%)");
        $this->command->newLine();
    }

    /**
     * Determinar importe de cuota según tipo de socio
     */
    private function determinarImporteCuota($socio): float
    {
        // Obtener tipo de socio si existe la relación
        try {
            $tipoSocio = $socio->tipoSocio->tipo ?? 'Ordinario';

            return match ($tipoSocio) {
                'Infantil' => fake()->randomFloat(2, 15, 20),
                'Juvenil' => fake()->randomFloat(2, 20, 25),
                'Ordinario' => fake()->randomFloat(2, 30, 40),
                'Honorífico' => 0, // Sin cuota
                'Protector' => fake()->randomFloat(2, 50, 100),
                default => fake()->randomFloat(2, 30, 40),
            };
        } catch (\Exception $e) {
            // Si no existe la relación, usar importe por defecto
            return fake()->randomFloat(2, 30, 40);
        }
    }
}
