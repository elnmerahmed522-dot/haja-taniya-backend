<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the cart items.
     */
    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->cartItems()
            ->with(['product.category', 'size', 'color'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => CartItemResource::collection($items)
        ]);
    }

    /**
     * Add or increment an item in the cart.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
            'size' => 'nullable|string',
            'color_hex' => 'nullable|string',
        ]);

        $productId = $request->product_id;
        $quantity = $request->quantity ?? 1;

        // حل معرّف المقاس
        $sizeId = null;
        if ($request->filled('size')) {
            $sizeId = Size::where('name_en', $request->size)
                ->orWhere('name_ar', $request->size)
                ->value('id');
        }

        // حل معرّف اللون
        $colorId = null;
        if ($request->filled('color_hex')) {
            $colorId = Color::where('hex', $request->color_hex)->value('id');
        }

        // التحقق من وجود العنصر مسبقاً لدمج الكمية
        $cartItem = CartItem::where([
            'user_id' => $request->user()->id,
            'product_id' => $productId,
            'size_id' => $sizeId,
            'color_id' => $colorId
        ])->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $quantity);
        } else {
            CartItem::create([
                'user_id' => $request->user()->id,
                'product_id' => $productId,
                'size_id' => $sizeId,
                'color_id' => $colorId,
                'quantity' => $quantity
            ]);
        }

        return $this->index($request);
    }

    /**
     * Update the quantity of a specific cart item.
     */
    public function updateQuantity(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
            'size' => 'nullable|string',
            'color_name' => 'nullable|string',
        ]);

        $sizeId = null;
        if ($request->filled('size')) {
            $sizeId = Size::where('name_en', $request->size)
                ->orWhere('name_ar', $request->size)
                ->value('id');
        }

        $colorId = null;
        if ($request->filled('color_name')) {
            $colorId = Color::where('name_en', $request->color_name)
                ->orWhere('name_ar', $request->color_name)
                ->value('id');
        }

        $cartItem = CartItem::where([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
            'size_id' => $sizeId,
            'color_id' => $colorId
        ])->first();

        if ($cartItem) {
            if ($request->quantity < 1) {
                $cartItem->delete();
            } else {
                $cartItem->update(['quantity' => $request->quantity]);
            }
        }

        return $this->index($request);
    }

    /**
     * Update the variant (size/color) of a specific cart item.
     */
    public function updateVariant(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'old_size' => 'nullable|string',
            'old_color_name' => 'nullable|string',
            'new_size' => 'nullable|string',
            'new_color' => 'nullable|array',
            'new_color.hex' => 'required_with:new_color|string',
        ]);

        // حل المعرفات القديمة
        $oldSizeId = null;
        if ($request->filled('old_size')) {
            $oldSizeId = Size::where('name_en', $request->old_size)
                ->orWhere('name_ar', $request->old_size)
                ->value('id');
        }

        $oldColorId = null;
        if ($request->filled('old_color_name')) {
            $oldColorId = Color::where('name_en', $request->old_color_name)
                ->orWhere('name_ar', $request->old_color_name)
                ->value('id');
        }

        // حل المعرفات الجديدة
        $newSizeId = null;
        if ($request->filled('new_size')) {
            $newSizeId = Size::where('name_en', $request->new_size)
                ->orWhere('name_ar', $request->new_size)
                ->value('id');
        }

        $newColorId = null;
        if ($request->filled('new_color')) {
            $newColorId = Color::where('hex', $request->new_color['hex'])->value('id');
        }

        // إيجاد العنصر القديم
        $oldItem = CartItem::where([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
            'size_id' => $oldSizeId,
            'color_id' => $oldColorId
        ])->first();

        if ($oldItem) {
            // التحقق من وجود العنصر الجديد مسبقاً لدمج الكميتين معاً
            $existingNewItem = CartItem::where([
                'user_id' => $request->user()->id,
                'product_id' => $request->product_id,
                'size_id' => $newSizeId,
                'color_id' => $newColorId
            ])->first();

            if ($existingNewItem && $existingNewItem->id !== $oldItem->id) {
                $existingNewItem->increment('quantity', $oldItem->quantity);
                $oldItem->delete();
            } else {
                $oldItem->update([
                    'size_id' => $newSizeId,
                    'color_id' => $newColorId
                ]);
            }
        }

        return $this->index($request);
    }

    /**
     * Remove a specific item from the cart.
     */
    public function removeItem(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'size' => 'nullable|string',
            'color_name' => 'nullable|string',
        ]);

        $sizeId = null;
        if ($request->filled('size')) {
            $sizeId = Size::where('name_en', $request->size)
                ->orWhere('name_ar', $request->size)
                ->value('id');
        }

        $colorId = null;
        if ($request->filled('color_name')) {
            $colorId = Color::where('name_en', $request->color_name)
                ->orWhere('name_ar', $request->color_name)
                ->value('id');
        }

        CartItem::where([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
            'size_id' => $sizeId,
            'color_id' => $colorId
        ])->delete();

        return $this->index($request);
    }

    /**
     * Empty the user's cart.
     */
    public function clear(Request $request): JsonResponse
    {
        $request->user()->cartItems()->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success'),
            'data' => []
        ]);
    }

    /**
     * Bulk merge guest cart items into the database.
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.selectedSize' => 'nullable|string',
            'items.*.selectedColor' => 'nullable|array',
            'items.*.selectedColor.hex' => 'required_with:items.*.selectedColor|string',
        ]);

        $userId = $request->user()->id;

        foreach ($request->items as $item) {
            $productId = $item['id'];
            $quantity = $item['quantity'];

            // حل المقاس
            $sizeId = null;
            if (!empty($item['selectedSize'])) {
                $sizeId = Size::where('name_en', $item['selectedSize'])
                    ->orWhere('name_ar', $item['selectedSize'])
                    ->value('id');
            }

            // حل اللون
            $colorId = null;
            if (!empty($item['selectedColor'])) {
                $colorId = Color::where('hex', $item['selectedColor']['hex'])->value('id');
            }

            // تحديث أو إنشاء
            $cartItem = CartItem::where([
                'user_id' => $userId,
                'product_id' => $productId,
                'size_id' => $sizeId,
                'color_id' => $colorId
            ])->first();

            if ($cartItem) {
                $cartItem->increment('quantity', $quantity);
            } else {
                CartItem::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'size_id' => $sizeId,
                    'color_id' => $colorId,
                    'quantity' => $quantity
                ]);
            }
        }

        return $this->index($request);
    }
}
