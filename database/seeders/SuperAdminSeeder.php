<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء مستخدم Admin
        $admin = User::create([
            'name' => 'Javed Ur Rehman',
            'email' => 'javed@allphptricks.com',
            'password' => Hash::make('javed1234'),
            'roles_name' => 'Admin',
        ]);
        $admin->assignRole('Admin');

        // إنشاء مستخدم Manager
        $manager = User::create([
            'name' => 'Syed Ahsan Kamal',
            'email' => 'ahsan@allphptricks.com',
            'password' => Hash::make('ahsan1234'),
            'roles_name' => 'Manager',
        ]);
        $manager->assignRole('Manager');

        // إنشاء مستخدم Developer
        $developer = User::create([
            'name' => 'Abdul Muqeet',
            'email' => 'muqeet@allphptricks.com',
            'password' => Hash::make('muqeet1234'),
            'roles_name' => 'Developer',
        ]);
        $developer->assignRole('Developer');

        // إنشاء مستخدم Tester
        $tester = User::create([
            'name' => 'Imran Khan',
            'email' => 'imran@allphptricks.com',
            'password' => Hash::make('imran1234'),
            'roles_name' => 'Tester',
        ]);
        $tester->assignRole('Tester');
    }
}
