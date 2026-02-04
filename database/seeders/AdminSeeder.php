<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@mudiklebaran.id',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'can_scan' => true,
            'is_active' => true,
        ]);

        Admin::create([
            'name' => 'Admin Validator',
            'email' => 'validator@mudiklebaran.id',
            'password' => Hash::make('password'),
            'role' => 'validator',
            'can_scan' => false,
            'is_active' => true,
        ]);

        Admin::create([
            'name' => 'Admin Scanner',
            'email' => 'scanner@mudiklebaran.id',
            'password' => Hash::make('password'),
            'role' => 'scanner',
            'can_scan' => true,
            'is_active' => true,
        ]);

        $this->command->info('Admin users created successfully!');
        $this->command->info('Super Admin: superadmin@mudiklebaran.id / password');
        $this->command->info('Validator: validator@mudiklebaran.id / password');
        $this->command->info('Scanner: scanner@mudiklebaran.id / password');
    }
}