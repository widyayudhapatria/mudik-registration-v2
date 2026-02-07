<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AssignAdminRolesSeeder extends Seeder
{
    public function run(): void
    {
        // Assign role super_admin ke admin dengan role super_admin
        $superAdmins = Admin::where('role', 'super_admin')->get();
        
        foreach ($superAdmins as $admin) {
            $admin->assignRole('super_admin');
            $this->command->info("✅ Assigned super_admin role to: {$admin->email}");
        }

        // Assign role validator ke admin dengan role validator
        $validators = Admin::where('role', 'validator')->get();
        
        foreach ($validators as $admin) {
            $admin->assignRole('validator');
            $this->command->info("✅ Assigned validator role to: {$admin->email}");
        }

        // Assign role scanner ke admin dengan can_scan = true
        $scanners = Admin::where('can_scan', true)->get();
        
        foreach ($scanners as $admin) {
            if (!$admin->hasRole('super_admin')) {
                $admin->assignRole('scanner');
                $this->command->info("✅ Assigned scanner role to: {$admin->email}");
            }
        }
    }
}