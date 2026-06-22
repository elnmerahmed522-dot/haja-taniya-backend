<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict'); // يمنع حذف العميل لو لديه فواتير
            $table->string('order_number')->unique(); // رقم الطلب الفريد (مثل: HT-2026-0001)
            $table->decimal('subtotal', 10, 2); // مجموع أسعار المنتجات
            $table->decimal('shipping_cost', 10, 2)->default(0.00); // تكلفة الشحن
            $table->decimal('tax', 10, 2)->default(0.00); // الضرائب
            $table->decimal('total', 10, 2); // المجموع النهائي الكلي
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->text('shipping_address'); // عنوان التوصيل بالتفصيل
            $table->string('phone'); // رقم هاتف المستلم
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
