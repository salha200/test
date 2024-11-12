<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // تشغيل Seeder الخاص بالصلاحيات
        $this->call(PermissionSeeder::class);

        // تشغيل Seeder الخاص بالأدوار
        $this->call(RoleSeeder::class);

        // يمكنك إضافة seeders أخرى هنا إن كان لديك بيانات أخرى لتعبئة قاعدة البيانات بها
    }
}
