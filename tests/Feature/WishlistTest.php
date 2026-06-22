<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;

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
    }

    public function test_authenticated_user_can_view_wishlist(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/wishlist');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => []
            ]);
    }

    public function test_user_can_add_to_wishlist(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/wishlist', [
                'product_id' => $this->product->id
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ]);
    }

    public function test_user_can_remove_from_wishlist(): void
    {
        Wishlist::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/wishlist/{$this->product->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ]);
    }
}
