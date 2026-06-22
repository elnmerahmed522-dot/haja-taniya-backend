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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->string('title_en'); // العنوان بالإنجليزي
            $table->string('title_ar'); // العنوان بالعربي
            $table->string('slug')->unique();
            $table->text('description_en'); // الوصف بالإنجليزي
            $table->text('description_ar'); // الوصف بالعربي
            $table->decimal('price', 10, 2);
            $table->decimal('old_price', 10, 2)->nullable();
            $table->string('discount')->nullable();
            $table->string('image_url');
            $table->boolean('is_bestseller')->default(false);
            $table->boolean('is_new')->default(true);
            $table->integer('stock_quantity')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
