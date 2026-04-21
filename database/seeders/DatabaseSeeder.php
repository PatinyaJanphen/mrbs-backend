<?php

namespace Database\Seeders;

use App\Models\Company;
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
        // 1. Create a Company
        $company = Company::create([
            'name' => 'Demo Company',
            'domain' => 'demo.com',
        ]);

        // 2. Create Users for each role
        // 0: super_admin, 1: admin, 2: staff, 3: user
        
        $roles = [
            ['name' => 'Super Admin', 'email' => 'superadmin@demo.com', 'role' => 0],
            ['name' => 'Company Admin', 'email' => 'admin@demo.com', 'role' => 1],
            ['name' => 'Staff Approver', 'email' => 'staff@demo.com', 'role' => 2],
            ['name' => 'Regular User', 'email' => 'user@demo.com', 'role' => 3],
        ];

        foreach ($roles as $r) {
            User::create([
                'company_id' => $company->id,
                'name' => $r['name'],
                'email' => $r['email'],
                'google_id' => 'mock_' . str_replace('@demo.com', '', $r['email']),
                'password' => Hash::make('password'),
                'role' => $r['role'],
                'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($r['name']) . '&background=random',
            ]);
        }

        // 3. Create some Resources (Rooms)
        Resource::create([
            'company_id' => $company->id,
            'name' => 'Meeting Room A',
            'description' => 'Large room for 12 people with projector.',
            'capacity' => 12,
            'requires_approval' => true,
        ]);

        Resource::create([
            'company_id' => $company->id,
            'name' => 'Huddle Room B',
            'description' => 'Small room for 4 people with TV.',
            'capacity' => 4,
            'requires_approval' => false,
        ]);
    }
}
