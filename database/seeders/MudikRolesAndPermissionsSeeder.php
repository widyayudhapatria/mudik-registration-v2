<?php

namespace Database\Seeders;

use App\Enums\Permissions\MudikPermissions;
use App\Enums\Permissions\MudikRoleList;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MudikRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all permissions
        foreach (MudikPermissions::cases() as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission->value],
                ['guard_name' => 'admin'] 
            );
        }

        $this->command->info('✅ Created ' . count(MudikPermissions::cases()) . ' permissions');

        // Create roles and assign permissions
        foreach (MudikRoleList::cases() as $roleEnum) {
            $role = Role::updateOrCreate(
                ['name' => $roleEnum->value],
                ['guard_name' => 'admin'] 
            );
            
            $role->syncPermissions($roleEnum->permissions());
            
            $this->command->info("✅ Created role: {$roleEnum->value} with " . count($roleEnum->permissions()) . " permissions");
        }
    }
}