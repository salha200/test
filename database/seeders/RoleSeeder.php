<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء الأدوار
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $developer = Role::firstOrCreate(['name' => 'Developer']);
        $tester = Role::firstOrCreate(['name' => 'Tester']);

        // صلاحيات المدير (Admin)
        $adminPermissions = [
            'manage-users',
            'create-task',
            'edit-task',
            'delete-task',
            'assign-task',
            'reassign-task',
            'generate-reports',
            'view-all-tasks',
            'manage-dependencies',
            'view-delayed-tasks',
            'manage-attachments',
            'manage-comments'
        ];

        // صلاحيات المدير (Manager)
        $managerPermissions = [
            'create-task',
            'edit-task',
            'assign-task',
            'reassign-task',
            'view-team-tasks',
            'generate-reports',
            'manage-dependencies',
            'add-comment',
            'add-attachment'
        ];

        // صلاحيات المطور (Developer)
        $developerPermissions = [
            'update-task-status',
            'view-assigned-tasks',
            'add-comment',
            'add-attachment',
            'view-dependencies',
            'resolve-dependency'
        ];

        // صلاحيات المختبر (Tester)
        $testerPermissions = [
            'view-assigned-tasks',
            'add-comment',
            'add-attachment',
            'update-task-status',
            'report-bug',
            'view-task-history'
        ];

        // إعطاء الصلاحيات للدور Admin
        $admin->givePermissionTo($adminPermissions);

        // إعطاء الصلاحيات للدور Manager
        $manager->givePermissionTo($managerPermissions);

        // إعطاء الصلاحيات للدور Developer
        $developer->givePermissionTo($developerPermissions);

        // إعطاء الصلاحيات للدور Tester
        $tester->givePermissionTo($testerPermissions);
    }
}
