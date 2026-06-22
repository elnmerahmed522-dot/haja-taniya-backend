<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
public function run(): void
{
    // 1. إنشاء المستخدم بطريقة create المباشرة (بدون factory)
    // تأكد من استيراد الموديل فوق: use App\Models\User;
    User::create([
        'name' => 'admin',
        'email' => 'admin@email.com',
        'password' => bcrypt('password123'), // تشفير كلمة المرور
        'role' => 'admin', // الدور الافتراضي
    ]);

    // 2. استدعاء بقية الـ Seeders للمنتجات والأقسام
    $this->call([
        ProductSeeder::class,
    ]);
}
}
