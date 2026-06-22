<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Get all wishlist products.
     */
    public function index(Request $request): JsonResponse
    {
        $wishlistItems = $request->user()->wishlist()
            ->with(['product.category', 'product.sizes', 'product.colors'])
            ->get();

        $products = $wishlistItems->map(function ($item) {
            return $item->product;
        })->filter(); // إزالة أي عناصر فارغة إن وجدت

        return response()->json([
            'status' => 'success',
            'data' => ProductResource::collection($products)
        ]);
    }

    /**
     * Add a product to the wishlist.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id
        ]);

        return $this->index($request);
    }

    /**
     * Remove a product from the wishlist.
     */
    public function destroy(Request $request, $productId): JsonResponse
    {
        Wishlist::where([
            'user_id' => $request->user()->id,
            'product_id' => $productId
        ])->delete();

        return $this->index($request);
    }

    /**
     * Empty the user's wishlist.
     */
    public function clear(Request $request): JsonResponse
    {
        $request->user()->wishlist()->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success'),
            'data' => []
        ]);
    }

    /**
     * Bulk sync local storage wishlist items into the database.
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'required|exists:products,id'
        ]);

        $userId = $request->user()->id;

        foreach ($request->product_ids as $productId) {
            Wishlist::firstOrCreate([
                'user_id' => $userId,
                'product_id' => $productId
            ]);
        }

        return $this->index($request);
    }
}
