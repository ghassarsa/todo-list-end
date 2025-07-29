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

        User::create([
            'name' => 'Admin',
            'email' => 'Admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'avatar' => null,
            'is_admin' => true,
        ]);

        Plan::create([
            'name' => 'Free',
            'description' => 'Free plan with basic features',
            'price' => 0,
            'task_limit' => 1,
        ]);

        Plan::create([
            'name' => 'Pro',
            'description' => 'Pro plan with advanced features',
            'price' => 199000,
            'task_limit' => 10,
        ]);

        Plan::create([
            'name' => 'Company',
            'description' => 'Premium plan with all features',
            'price' => 399000,
            'task_limit' => 100,
        ]);
    }
}
