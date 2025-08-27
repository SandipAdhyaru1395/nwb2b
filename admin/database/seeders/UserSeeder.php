<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Upsert a predictable test user
    User::updateOrCreate(
      ['email' => 'superadmin@example.com'],
      [
        'name' => 'admin',
        'role_id' => 1,
        'image' => asset('assets/img/avatars/5.png'),
        'password' => 'password', // hashed by model cast
      ]
    );

    User::updateOrCreate(
      ['email' => 'admin@example.com'],
      [
        'name' => 'admin',
        'role_id' => 2,
        'image' => asset('assets/img/avatars/5.png'),
        'password' => 'password', // hashed by model cast
      ]
    );


    User::updateOrCreate(
      ['email' => 'manager1@example.com'],
      [
        'name' => 'manager',
        'role_id' => 3,
         'image' => asset('assets/img/avatars/12.png'),
        'password' => 'password', // hashed by model cast
      ]
    );
    User::updateOrCreate(
      ['email' => 'manager2@example.com'],
      [
        'name' => 'manager',
        'role_id' => 3,
         'image' => asset('assets/img/avatars/6.png'),
        'password' => 'password', // hashed by model cast
      ]
    );
    User::updateOrCreate(
      ['email' => 'manager3@example.com'],
      [
        'name' => 'manager',
        'role_id' => 3,
         'image' => asset('assets/img/avatars/3.png'),
        'password' => 'password', // hashed by model cast
      ]
    );
    User::updateOrCreate(
      ['email' => 'manager4@example.com'],
      [
        'name' => 'manager',
        'role_id' => 3,
         'image' => asset('assets/img/avatars/5.png'),
        'password' => 'password', // hashed by model cast
      ]
    );
    User::updateOrCreate(
      ['email' => 'manager5@example.com'],
      [
        'name' => 'manager',
        'role_id' => 3,
         'image' => asset('assets/img/avatars/12.png'),
        'password' => 'password', // hashed by model cast
      ]
    );

     User::updateOrCreate(
      ['email' => 'manager6@example.com'],
      [
        'name' => 'manager',
        'role_id' => 3,
         'image' => asset('assets/img/avatars/6.png'),
        'password' => 'password', // hashed by model cast
      ]
    );

     User::updateOrCreate(
      ['email' => 'user@example.com'],
      [
        'name' => 'user',
        'role_id' => 4,
         'image' => asset('assets/img/avatars/3.png'),
        'password' => 'password', // hashed by model cast
      ]
    );
  }
}
