<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Supervisor default account (NP: T0087)
        User::updateOrCreate(
            ['username' => 'supervisor'],
            [
                'name' => 'Supervisor Produksi',
                'nip' => 'T0087',
                'email' => 'supervisor@pds.co.id',
                'password' => Hash::make('password'),
                'role' => UserRole::SUPERVISOR,
                'is_active' => true,
            ]
        );

        // Petugas Kemas default account (NP: 7426)
        User::updateOrCreate(
            ['username' => 'petugas'],
            [
                'name' => 'Petugas Kemas',
                'nip' => '7426',
                'email' => 'petugas@pds.co.id',
                'password' => Hash::make('password'),
                'role' => UserRole::PETUGAS,
                'is_active' => true,
            ]
        );

        // Production Orders master data sample
        $this->call(ProductionOrderSeeder::class);
    }
}
