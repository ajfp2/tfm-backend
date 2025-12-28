<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            SociosSeeder::class
        ]);

        // 4. Historial de cuotas anuales
        $this->command->info('💰 PASO 4: Generando historial de cuotas');
        $this->call([
            HistorialAnualSeeder::class,
        ]);
        $this->command->newLine();

        // 5. Historial de cargos directivos
        $this->command->info('👔 PASO 5: Generando historial de cargos directivos');
        $this->call([
            HistorialAnualDirectivaSeeder::class,
        ]);
        $this->command->newLine();

        $this->command->info('✅ ¡Todos los seeders ejecutados correctamente!');
        $this->command->newLine();
        
    }
}

