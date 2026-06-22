<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Color;
use App\Models\Order;
use App\Models\Product;
use App\Models\Size;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $category = Category::create([
            'name_en' => 'Clothing',
            'name_ar' => 'ملابس',
            'slug'    => 'clothing',
        ]);

        $this->product = Product::create([
            'category_id'    => $category->id,
            'title_en'       => 'Test Shirt',
            'title_ar'       => 'قميص تجريبي',
            'slug'           => 'test-shirt',
            'description_en' => 'A test product',
            'description_ar' => 'منتج تجريبي',
            'price'          => 49.99,
            'image_url'      => 'test-shirt.jpg',
            'stock_quantity' => 100,
        ]);

        // Sanctum token
        $this->token = $this->user->createToken('test-token')->plainTextToken;

        // Add item to DB cart so OrderController can read it
        CartItem::create([
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
            'quantity'   => 2,
            'size_id'    => null,
            'color_id'   => null,
        ]);
    }

    // =====================================================
    // ✅ Test 1: Can place an order successfully
    // =====================================================
    public function test_user_can_place_order(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/orders', [
                'shipping_address' => '123 Main St, Cairo',
                'phone'            => '+20 100 000 0000',
            ]);

        $response->assertStatus(201)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         'id',
                         'orderNumber',
                         'status',
                         'subtotal',
                         'total',
                         'shippingAddress',
                         'items',
                     ],
                 ]);

        // Order must exist in DB
        $this->assertDatabaseHas('orders', [
            'user_id'          => $this->user->id,
            'shipping_address' => '123 Main St, Cairo',
        ]);

        // Cart must be cleared after order
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->user->id,
        ]);
    }

    // =====================================================
    // ✅ Test 2: Cannot place order with empty cart
    // =====================================================
    public function test_cannot_place_order_with_empty_cart(): void
    {
        // Clear the cart seeded in setUp
        CartItem::where('user_id', $this->user->id)->delete();

        $response = $this->withToken($this->token)
            ->postJson('/api/orders', [
                'shipping_address' => '123 Main St, Cairo',
                'phone'            => '+20 100 000 0000',
            ]);

        $response->assertStatus(422)
                 ->assertJsonPath('status', 'error');
    }

    // =====================================================
    // ✅ Test 3: Validation requires shipping_address and phone
    // =====================================================
    public function test_order_requires_shipping_address_and_phone(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/orders', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['shipping_address', 'phone']);
    }

    // =====================================================
    // ✅ Test 4: Can list own orders
    // =====================================================
    public function test_user_can_list_own_orders(): void
    {
        // Create an order directly
        Order::create([
            'user_id'          => $this->user->id,
            'order_number'     => 'HT-2026-TEST01',
            'subtotal'         => 49.99,
            'shipping_cost'    => 0,
            'tax'              => 0,
            'total'            => 49.99,
            'status'           => 'pending',
            'shipping_address' => '123 Main St, Cairo',
            'phone'            => '+20 100 000 0000',
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/orders');

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonCount(1, 'data');
    }

    // =====================================================
    // ✅ Test 5: Can show single order detail
    // =====================================================
    public function test_user_can_view_single_order(): void
    {
        $order = Order::create([
            'user_id'          => $this->user->id,
            'order_number'     => 'HT-2026-TEST02',
            'subtotal'         => 99.98,
            'shipping_cost'    => 0,
            'tax'              => 0,
            'total'            => 99.98,
            'status'           => 'pending',
            'shipping_address' => '456 Nile Ave, Giza',
            'phone'            => '+20 100 111 2222',
        ]);

        $response = $this->withToken($this->token)
            ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('data.id', $order->id);
    }

    // =====================================================
    // ✅ Test 6: Cannot view another user's order
    // =====================================================
    public function test_user_cannot_view_other_users_order(): void
    {
        $otherUser = User::factory()->create();
        $order = Order::create([
            'user_id'          => $otherUser->id,
            'order_number'     => 'HT-2026-OTHER',
            'subtotal'         => 50.00,
            'shipping_cost'    => 0,
            'tax'              => 0,
            'total'            => 50.00,
            'status'           => 'pending',
            'shipping_address' => '789 Other St',
            'phone'            => '+20 100 999 9999',
        ]);

        $response = $this->withToken($this->token)
            ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(404);
    }

    // =====================================================
    // ✅ Test 7: Guest cannot access orders
    // =====================================================
    public function test_guest_cannot_access_orders(): void
    {
        $this->getJson('/api/orders')->assertStatus(401);
        $this->postJson('/api/orders', [])->assertStatus(401);
    }
}
