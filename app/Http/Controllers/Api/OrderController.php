<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()->orders()
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => OrderResource::collection($orders)
        ]);
    }

    /**
     * Display the specified order details.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $order = $request->user()->orders()
            ->with('items.product')
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => new OrderResource($order)
        ]);
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'shipping_address' => 'required|string|max:500',
            'phone'            => 'required|string|max:20',
            'notes'            => 'nullable|string|max:1000',
            'payment_method'   => 'nullable|string|in:cash_on_delivery,bank_transfer',
        ]);

        $cartItems = $request->user()->cartItems()
            ->with(['product', 'size', 'color'])
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your cart is empty.'
            ], 422);
        }

        // التحقق من توفر الكمية الكافية في المخزن لجميع المنتجات في السلة
        foreach ($cartItems as $item) {
            if ($item->product->stock_quantity < $item->quantity) {
                return response()->json([
                    'status' => 'error',
                    'message' => "المنتج '{$item->product->title_ar}' غير متوفر بالكمية المطلوبة. الكمية المتاحة حالياً: {$item->product->stock_quantity}"
                ], 422);
            }
        }

        // حساب المبالغ المالية
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item->product->price * $item->quantity;
        }

        $shippingCost = 0.00; // شحن مجاني
        $tax = 0.00;
        $total = $subtotal + $shippingCost + $tax;

        // توليد رقم طلب فريد
        $orderNumber = 'HT-' . date('Y') . '-' . strtoupper(Str::random(6));
        while (Order::where('order_number', $orderNumber)->exists()) {
            $orderNumber = 'HT-' . date('Y') . '-' . strtoupper(Str::random(6));
        }

        // إتمام العملية باستخدام Transaction لضمان حفظ البيانات بالكامل أو إلغائها
        $order = DB::transaction(function () use ($request, $cartItems, $orderNumber, $subtotal, $shippingCost, $tax, $total) {
            $order = Order::create([
                'user_id'         => $request->user()->id,
                'order_number'    => $orderNumber,
                'subtotal'        => $subtotal,
                'shipping_cost'   => $shippingCost,
                'tax'             => $tax,
                'total'           => $total,
                'status'          => 'pending',
                'shipping_address' => $request->shipping_address,
                'phone'           => $request->phone,
                'notes'           => $request->notes,
                'payment_method'  => $request->payment_method ?? 'cash_on_delivery',
            ]);

            foreach ($cartItems as $item) {
                // تقليل الكمية من مخزن المنتج
                $item->product->decrement('stock_quantity', $item->quantity);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'color_name_en' => $item->color ? $item->color->name_en : null,
                    'color_name_ar' => $item->color ? $item->color->name_ar : null,
                    'color_hex' => $item->color ? $item->color->hex : null,
                    'size_name_en' => $item->size ? $item->size->name_en : null,
                    'size_name_ar' => $item->size ? $item->size->name_ar : null,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price
                ]);
            }

            // تفريغ السلة للمستخدم بعد إتمام الطلب بنجاح
            $request->user()->cartItems()->delete();

            return $order;
        });

        // تحميل العلاقات لإرجاعها في الاستجابة
        $order->load('items.product');

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success'),
            'data' => new OrderResource($order)
        ], 201);
    }
}
