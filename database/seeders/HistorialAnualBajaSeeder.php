<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\HistorialAnualBaja;
use App\Models\SocioBaja;
use App\Models\Temporada;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HistorialAnualBajaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Limpiar tabla si existe (opcional)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        HistorialAnualBaja::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('🔄 Generando historial de cuotas de socios dados de baja...');

        // Detectar estructura de la tabla socios_baja
        // $this->detectarCampoIdSocioBaja();

        // Obtener todos los socios dados de baja
        $sociosBaja = SocioBaja::all();
        
        if ($sociosBaja->isEmpty()) {
            $this->command->warn('⚠️  No hay socios dados de baja');
            return;
        }

        $totalSociosBaja = $sociosBaja->count();
        $this->command->info("📊 Total de socios dados de baja: {$totalSociosBaja}");

        // Obtener temporadas (IDs 1-4)
        $temporadas = Temporada::whereIn('id', [1, 2, 3, 4])->get();

        if ($temporadas->isEmpty()) {
            $this->command->error('⚠️  No se encontraron temporadas con IDs 1-4');
            return;
        }

        $this->command->info("📅 Temporadas encontradas: {$temporadas->count()}");
        $this->command->info("🔑 Campo ID detectado: {a_Persona}");

        // DEBUG: Ver estructura del primer socio de baja
        $primerSocio = $sociosBaja->first();
        if ($primerSocio) {
            $this->command->info("🔍 Estructura del primer socio de baja:");
            $this->command->info("   ID: " . $primerSocio->a_Persona);
        }

        $totalCuotas = 0;
        $cuotasPagadas = 0;
        $cuotasPendientes = 0;
        $cuotasExentas = 0;
        $totalImportePendiente = 0;

        // Por cada socio de baja
        foreach ($sociosBaja as $socioBaja) {
            // Obtener el ID del socio
            $idSocioBaja = $socioBaja->a_Persona;//$this->obtenerIdSocioBaja($socioBaja);

            // Si no se encuentra el ID, saltar este socio
            if ($idSocioBaja === null) {
                $this->command->warn("⚠️  Socio de baja sin ID válido, saltando...");
                continue;
            }

            // Por cada temporada
            foreach ($temporadas as $temporada) {
                // 80% de probabilidad de que tenga cuota en esta temporada
                if (fake()->boolean(80)) {
                    
                    // Determinar importe base
                    $importeBase = fake()->randomFloat(2, 30, 40);

                    // 10% de probabilidad de ser exento
                    $exento = fake()->boolean(10);

                    // Si es exento, cuota_pagada = false pero importe_pendiente = 0
                    if ($exento) {
                        HistorialAnualBaja::create([
                            'a_socio_baja' => $idSocioBaja,
                            'a_temporada' => $temporada->id,
                            'cuota_pagada' => false,
                            'exento' => true,
                            'importe_pendiente' => 0,
                        ]);
                        
                        $cuotasExentas++;
                    } else {
                        // 70% probabilidad de que esté pagada
                        $cuotaPagada = fake()->boolean(70);

                        if ($cuotaPagada) {
                            // Cuota pagada: importe_pendiente = 0
                            HistorialAnualBaja::create([
                                'a_socio_baja' => $idSocioBaja,
                                'a_temporada' => $temporada->id,
                                'cuota_pagada' => true,
                                'exento' => false,
                                'importe_pendiente' => 0,
                            ]);
                            
                            $cuotasPagadas++;
                        } else {
                            // Cuota pendiente: importe_pendiente > 0
                            HistorialAnualBaja::create([
                                'a_socio_baja' => $idSocioBaja,
                                'a_temporada' => $temporada->id,
                                'cuota_pagada' => false,
                                'exento' => false,
                                'importe_pendiente' => $importeBase,
                            ]);
                            
                            $cuotasPendientes++;
                            $totalImportePendiente += $importeBase;
                        }
                    }

                    $totalCuotas++;
                }
            }
        }

        $this->command->newLine();
        $this->command->info('📊 RESUMEN DE CUOTAS DE BAJAS GENERADAS:');
        $this->command->info("   Total cuotas: {$totalCuotas}");
        $this->command->info("   ✅ Pagadas: {$cuotasPagadas} (" . ($totalCuotas > 0 ? round(($cuotasPagadas / $totalCuotas) * 100, 2) : 0) . "%)");
        $this->command->info("   ⏳ Pendientes: {$cuotasPendientes} (" . ($totalCuotas > 0 ? round(($cuotasPendientes / $totalCuotas) * 100, 2) : 0) . "%)");
        $this->command->info("   🆓 Exentas: {$cuotasExentas} (" . ($totalCuotas > 0 ? round(($cuotasExentas / $totalCuotas) * 100, 2) : 0) . "%)");
        $this->command->info("   💰 Total importe pendiente: " . number_format($totalImportePendiente, 2, ',', '.') . " €");
        $this->command->newLine();
    }
}
