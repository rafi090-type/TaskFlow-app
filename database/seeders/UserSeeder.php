<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'User One',
                'email' => 'user1@example.com',
                'password' => Hash::make('password123')
            ],
            [
                'name' => 'User Two',
                'email' => 'user2@example.com',
                'password' => Hash::make('password123')
            ],
            [
                'name' => 'User Three',
                'email' => 'user3@example.com',
                'password' => Hash::make('password123')
            ]
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
