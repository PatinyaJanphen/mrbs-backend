<?php

namespace Database\Seeders;

use App\Models\Resource;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Users for each role
        // 0: super_admin, 1: admin, 2: staff, 3: user
        $roles = [
            ['name' => 'Super Admin',    'email' => 'superadmin@demo.com',   'role' => 0],
            ['name' => 'Admin',          'email' => 'admin@demo.com',         'role' => 1],
            ['name' => 'Staff Approver', 'email' => 'staff@demo.com',         'role' => 2],
            ['name' => 'User Demo',      'email' => 'user@demo.com',          'role' => 3],
        ];

        foreach ($roles as $r) {
            User::updateOrCreate(
                ['email' => $r['email']],
                [
                    'name' => $r['name'],
                    'google_id' => null,
                    'password' => Hash::make('password'),
                    'role' => $r['role'],
                    'is_active' => true,
                    'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($r['name']) . '&background=random',
                ]
            );
        }

        // Create some Resources (Rooms)
        Resource::firstOrCreate(
            ['name' => 'Meeting Room A'],
            [
                'description' => 'Large room for 12 people with projector.',
                'capacity' => 12,
                'requires_approval' => true,
            ]
        );

        Resource::firstOrCreate(
            ['name' => 'Huddle Room B'],
            [
                'description' => 'Small room for 4 people with TV.',
                'capacity' => 4,
                'requires_approval' => false,
            ]
        );
    }
}
