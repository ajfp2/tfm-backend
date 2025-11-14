<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Menu::truncate();

        // 1. Dashboard
        Menu::create([
            'label' => 'Dashboard',
            'icon' => 'bi-house-door',
            'route' => '/dashboard',
            'order' => 1,
            'roles' => [1, 2],
            'activo' => true
        ]);

        // 2. Peña
        $pena = Menu::create([
            'label' => 'Peña',
            'icon' => 'bi-file-earmark-text',
            'route' => null,
            'order' => 2,
            'roles' => [1],
            'activo' => true
        ]);

        Menu::create([
            'label' => 'Datos Básicos',
            'route' => '/pena/datosbasicos',
            'order' => 1,
            'parent_id' => $pena->id,
            'roles' => [1],
            'activo' => true
        ]);

        Menu::create([
            'label' => 'Datos Bancarios',
            'route' => '/pena/datosbanco',
            'order' => 2,
            'parent_id' => $pena->id,
            'roles' => [1],
            'activo' => true
        ]);

        Menu::create([
            'label' => 'Temporadas',
            'route' => '/pena/temporadas',
            'order' => 3,
            'parent_id' => $pena->id,
            'roles' => [1],
            'activo' => true
        ]);

        Menu::create([
            'label' => 'Junta Directiva',
            'route' => '/pena/junta',
            'order' => 4,
            'parent_id' => $pena->id,
            'roles' => [1],
            'activo' => true
        ]);

        Menu::create([
            'label' => 'Contactos',
            'route' => '/pena/contactos',
            'order' => 5,
            'parent_id' => $pena->id,
            'roles' => [1],
            'activo' => true
        ]);

        // 3. Administrar Socios
        $adminSocios = Menu::create([
            'label' => 'Administrar Socios',
            'icon' => 'bi-person-vcard',
            'route' => null,
            'order' => 3,
            'roles' => [1],
            'activo' => true
        ]);

        // Nivel 2: Socios
        $socios = Menu::create([
            'label' => 'Socios',
            'route' => null,
            'order' => 1,
            'parent_id' => $adminSocios->id,
            'roles' => [1],
            'activo' => true
        ]);

        // Nivel 3: socios-alta-baja-menores
        Menu::create([
            'label' => 'Socios Alta',
            'route' => '/socios/alta',
            'order' => 1,
            'parent_id' => $socios->id,
            'roles' => [1],
            'activo' => true
        ]);

        Menu::create([
            'label' => 'Socios Baja',
            'route' => '/socios/baja',
            'order' => 2,
            'parent_id' => $socios->id,
            'roles' => [1],
            'activo' => true
        ]);

        Menu::create([
            'label' => 'Socios Menores',
            'route' => '/socios/menores',
            'order' => 3,
            'parent_id' => $socios->id,
            'roles' => [1],
            'activo' => true
        ]);

        // Nivel 2: Tipos de Socios
        Menu::create([
            'label' => 'Tipos de Socios',
            'route' => '/socios/tipos',
            'order' => 2,
            'parent_id' => $adminSocios->id,
            'roles' => [1],
            'activo' => true
        ]);

        // 4. Contabilidad
        Menu::create([
            'label' => 'Contabilidad',
            'icon' => 'bi-bank2',
            'route' => '/contabilidad',
            'order' => 4,
            'roles' => [1],
            'activo' => true
        ]);

        // 5. Correspondencia
        $correspondencia = Menu::create([
            'label' => 'Correspondencia',
            'icon' => 'bi-mailbox-flag',
            'route' => null,
            'order' => 5,
            'roles' => [1],
            'activo' => true
        ]);

        Menu::create([
            'label' => 'Gmail',
            'route' => '/correspondencia/gmail',
            'order' => 1,
            'parent_id' => $correspondencia->id,
            'roles' => [1],
            'activo' => true
        ]);

        Menu::create([
            'label' => 'Socios',
            'route' => '/correspondencia/socios',
            'order' => 2,
            'parent_id' => $correspondencia->id,
            'roles' => [1],
            'activo' => true
        ]);

        // 6. Usuarios
        $usuarios = Menu::create([
            'label' => 'Usuarios',
            'icon' => 'bi-people',
            'route' => null,
            'order' => 6,
            'roles' => [1],
            'activo' => true
        ]);

        Menu::create([
            'label' => 'Lista de Usuarios',
            'route' => '/usuarios/list',
            'order' => 1,
            'parent_id' => $usuarios->id,
            'roles' => [1],
            'activo' => true
        ]);

        Menu::create([
            'label' => 'Roles',
            'route' => '/usuarios/roles',
            'order' => 2,
            'parent_id' => $usuarios->id,
            'roles' => [1],
            'activo' => true
        ]);

        // 7. Configuración
        Menu::create([
            'label' => 'Configuración',
            'icon' => 'bi-gear',
            'route' => '/config',
            'order' => 7,
            'roles' => [1],
            'activo' => true
        ]);
    }
}
