<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles básicos
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'inspector']);
        Role::firstOrCreate(['name' => 'viewer']);

        // Crear usuario admin inicial
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('secret123'),
            ]
        );

        $admin->assignRole('admin');
    }
}
