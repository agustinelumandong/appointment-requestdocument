<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class DocumentRequestPermissionsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Define document request permissions
    $documentRequestPermissions = [
      'document-requests.view',
      'document-requests.create',
      'document-requests.edit',
      'document-requests.delete',
    ];

    // Create each permission if it doesn't exist
    foreach ($documentRequestPermissions as $permissionName) {
      Permission::firstOrCreate([
        'name' => $permissionName,
        'guard_name' => 'web'
      ]);
    }

    // Assign all document request permissions to admin role
    $adminRole = Role::where('name', 'admin')->first();
    if ($adminRole) {
      $permissions = Permission::whereIn('name', $documentRequestPermissions)->get();
      $adminRole->syncPermissions($adminRole->permissions->merge($permissions));
    }

    // Assign view and edit permissions to moderator role
    $moderatorRole = Role::where('name', 'moderator')->first();
    if ($moderatorRole) {
      $moderatorPermissions = [
        'document-requests.view',
        'document-requests.edit'
      ];
      $permissions = Permission::whereIn('name', $moderatorPermissions)->get();
      $moderatorRole->syncPermissions($moderatorRole->permissions->merge($permissions));
    }

    $this->command->info('Document request permissions created and assigned successfully.');
  }
}
