<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory(3)->create(['role' => 'admin']);
        User::factory(3)->create(['role' => 'manager']);
        User::factory(5)->create(['role' => 'user']);
    }
}
