<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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

        // دمج جميع الصلاحيات في مصفوفة واحدة
        $allPermissions = array_merge(
            $adminPermissions,
            $managerPermissions,
            $developerPermissions,
            $testerPermissions
        );

        // حذف التكرارات في حال تداخل الصلاحيات
        $permissions = array_unique($allPermissions);

        // إضافة الصلاحيات إلى قاعدة البيانات
        foreach ($permissions as $permission) {
            // التحقق إذا كانت الصلاحية موجودة بالفعل قبل إضافتها
            if (!Permission::where('name', $permission)->exists()) {
                Permission::create(['name' => $permission]);
            }
        }
    }
}
