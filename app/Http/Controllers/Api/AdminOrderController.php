<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    /**
     * عرض جميع الطلبات مع بيانات العميل (للأدمن فقط)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['user', 'items.product'])->orderBy('created_at', 'desc');

        // فلترة بالحالة
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // بحث برقم الطلب أو اسم العميل
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => OrderResource::collection($orders->items()),
            'meta' => [
                'total' => $orders->total(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
            ],
        ]);
    }

    /**
     * عرض تفاصيل طلب واحد
     */
    public function show($id): JsonResponse
    {
        $order = Order::with(['user', 'items.product'])->find($id);

        if (! $order) {
            return response()->json(['status' => 'error', 'message' => 'الطلب غير موجود'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * تعديل حالة طلب معين مع إدارة المخزون
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order = Order::with('items.product')->find($id);

        if (! $order) {
            return response()->json(['status' => 'error', 'message' => 'الطلب غير موجود'], 404);
        }

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // حالة 1: إلغاء الطلب -> إرجاع المنتجات للمخزن
        if ($oldStatus !== 'cancelled' && $newStatus === 'cancelled') {
            DB::transaction(function () use ($order) {
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock_quantity', $item->quantity);
                    }
                }
            });
        }
        // حالة 2: تغيير حالة طلب ملغي إلى حالة نشطة -> خصم المنتجات من المخزن مجدداً
        elseif ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
            // التحقق من توفر كمية كافية أولاً
            foreach ($order->items as $item) {
                if ($item->product && $item->product->stock_quantity < $item->quantity) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "لا يمكن إعادة تنشيط الطلب لأن كمية المنتج '{$item->product->title_ar}' المتوفرة بالمخزن غير كافية."
                    ], 422);
                }
            }

            DB::transaction(function () use ($order) {
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->decrement('stock_quantity', $item->quantity);
                    }
                }
            });
        }

        $order->update(['status' => $newStatus]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تعديل حالة الطلب بنجاح',
            'data' => new OrderResource($order->load(['user', 'items.product'])),
        ]);
    }

    /**
     * تحديث ملاحظات الإدارة على طلب معين
     */
    public function updateNotes(Request $request, $id): JsonResponse
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $order = Order::find($id);

        if (! $order) {
            return response()->json(['status' => 'error', 'message' => 'الطلب غير موجود'], 404);
        }

        $order->update(['admin_notes' => $request->admin_notes]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم حفظ الملاحظات الإدارية بنجاح',
            'data' => new OrderResource($order->load(['user', 'items.product'])),
        ]);
    }
}
