<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    //
    $superAdminUser = User::firstOrCreate(
      // first array is to constraint checking
      // if conditions is meet, will not create new data
      [
        'id' => 1,
      ],
      [
        'name' => 'Super Admin',
        'email' => 'superadmin@invoke.com',
        'password' => bcrypt('password'),
        'role' => 1,
        'dob' => '1996-01-01',
        'contact_number' => '0123456789',
      ]
    );
  }
}
