<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    /**
     * إحصائيات لوحة الأدمن
     */
    public function stats(): JsonResponse
    {
        $totalOrders = Order::count();
        $totalRevenue = Order::whereIn('status', ['delivered', 'shipped', 'processing'])->sum('total');
        $totalProducts = Product::count();
        $totalUsers = User::where('role', 'user')->count();

        // إحصائيات الطلبات حسب الحالة
        $ordersByStatus = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // آخر 7 طلبات
        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->take(7)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'orderNumber' => $order->order_number,
                    'customerName' => $order->user?->name ?? 'Guest',
                    'total' => (double) $order->total,
                    'status' => $order->status,
                    'createdAt' => $order->created_at->toIso8601String(),
                ];
            });

        // المبيعات خلال آخر 7 أيام (لرسم بياني بسيط)
        $salesChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $dailyTotal = Order::whereDate('created_at', $date)->sum('total');
            $salesChart[] = [
                'date' => $date,
                'total' => (double) $dailyTotal,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'totalOrders' => $totalOrders,
                'totalRevenue' => (double) $totalRevenue,
                'totalProducts' => $totalProducts,
                'totalUsers' => $totalUsers,
                'ordersByStatus' => $ordersByStatus,
                'recentOrders' => $recentOrders,
                'salesChart' => $salesChart,
            ],
        ]);
    }
}
