<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'avatar' => null,
            'is_admin' => false,
        ]);

        Plan::create([
            "name" => 'Free',
            "description" => '',
            "price" => 0,
            "task_limit" => 2,
        ]);
    }
}
