<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // استيراد الـ Hash أفضل وأسرع

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. استخدام updateOrCreate لمنع تعارض الـ Duplicate Entry
        User::updateOrCreate(
            ['email' => 'admin@email.com'], // بيبحث بالإيميل ده أولاً
            [
                'name' => 'admin',
                'password' => Hash::make('password123'), // الطريقة الأحدث للتشفير
                'role' => 'admin',
            ]
        );

        // 2. استدعاء بقية الـ Seeders للمنتجات
        $this->call([
            ProductSeeder::class,
        ]);
    }
}