<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use App\Models\User;
use App\Models\CartItem;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $size;
    protected $color;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $category = Category::create([
            'name_en' => 'Clothing',
            'name_ar' => 'ملابس',
            'slug' => 'clothing'
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'title_en' => 'Test Product',
            'title_ar' => 'منتج تجريبي',
            'slug' => 'test-product',
            'description_en' => 'Description',
            'description_ar' => 'وصف',
            'price' => 100.00,
            'image_url' => 'https://via.placeholder.com/150',
            'is_bestseller' => false,
            'is_new' => true,
            'stock_quantity' => 10
        ]);

        $this->size = Size::create([
            'name_en' => 'M',
            'name_ar' => 'M'
        ]);

        $this->color = Color::create([
            'name_en' => 'Red',
            'name_ar' => 'أحمر',
            'hex' => '#FF0000'
        ]);
    }

    public function test_authenticated_user_can_view_cart(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/cart');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => []
            ]);
    }

    public function test_user_can_add_item_to_cart(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/cart', [
                'product_id' => $this->product->id,
                'quantity' => 2,
                'size' => 'M',
                'color_hex' => '#FF0000'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'size_id' => $this->size->id,
            'color_id' => $this->color->id
        ]);
    }

    public function test_user_can_update_cart_item_quantity(): void
    {
        $cartItem = CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'size_id' => $this->size->id,
            'color_id' => $this->color->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($this->user)
            ->putJson('/api/cart/quantity', [
                'product_id' => $this->product->id,
                'quantity' => 5,
                'size' => 'M',
                'color_name' => 'Red'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 5
        ]);
    }

    public function test_user_can_remove_item_from_cart(): void
    {
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'size_id' => $this->size->id,
            'color_id' => $this->color->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/cart/item', [
                'product_id' => $this->product->id,
                'size' => 'M',
                'color_name' => 'Red'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ]);
    }

    public function test_user_can_sync_guest_cart(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/cart/sync', [
                'items' => [
                    [
                        'id' => $this->product->id,
                        'quantity' => 3,
                        'selectedSize' => 'M',
                        'selectedColor' => [
                            'name' => 'Red',
                            'hex' => '#FF0000'
                        ]
                    ]
                ]
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 3
        ]);
    }
}
