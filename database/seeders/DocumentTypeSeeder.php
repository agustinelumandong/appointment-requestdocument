<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
use App\Models\Employee;
use App\Models\Category;
use App\Models\DocumentType;
use App\Models\Service;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DocumentTypeSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run()
  {
    DocumentType::insert([
      ['slug' => 'birth-certificate', 'name' => 'Birth Certificate', 'description' => 'Certified copy of birth record'],
      ['slug' => 'death-certificate', 'name' => 'Death Certificate', 'description' => 'Certified copy of death record'],
      ['slug' => 'marriage-contract', 'name' => 'Marriage Contract', 'description' => 'Certified copy of marriage contract'],
    ]);
  }


}
