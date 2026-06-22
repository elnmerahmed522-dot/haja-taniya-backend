<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * عرض جميع المستخدمين مع عدد طلباتهم
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::withCount('orders')->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'isBlocked' => $user->is_blocked,
                    'ordersCount' => $user->orders_count,
                    'createdAt' => $user->created_at->toIso8601String(),
                ];
            }),
            'meta' => [
                'total' => $users->total(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
            ],
        ]);
    }

    /**
     * تغيير دور مستخدم (user <-> admin)
     */
    public function updateRole(Request $request, $id): JsonResponse
    {
        $request->validate([
            'role' => 'required|in:customer,admin',
        ]);

        $user = User::find($id);

        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'المستخدم غير موجود'], 404);
        }

        // منع الأدمن من تغيير دور نفسه
        if ($user->id === $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'لا يمكنك تعديل دورك بنفسك'], 403);
        }

        $user->update(['role' => $request->role]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تعديل دور المستخدم بنجاح',
            'data' => ['id' => $user->id, 'role' => $user->role],
        ]);
    }

    /**
     * حظر أو إلغاء حظر مستخدم
     */
    public function toggleBlock(Request $request, $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'المستخدم غير موجود'], 404);
        }

        // منع الأدمن من حظر نفسه
        if ($user->id === $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'لا يمكنك حظر نفسك بنفسك'], 403);
        }

        $user->is_blocked = !$user->is_blocked;
        $user->save();

        // إذا تم حظره، نقوم بإلغاء جميع التوكنات النشطة لديه فوراً
        if ($user->is_blocked) {
            $user->tokens()->delete();
        }

        $message = $user->is_blocked ? 'تم حظر المستخدم وإلغاء جميع جلساته بنجاح' : 'تم إلغاء حظر المستخدم بنجاح';

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => [
                'id' => $user->id,
                'isBlocked' => $user->is_blocked
            ]
        ]);
    }

    /**
     * حذف مستخدم نهائياً (Soft Delete)
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'المستخدم غير موجود'], 404);
        }

        // منع الأدمن من حذف نفسه
        if ($user->id === $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'لا يمكنك حذف حسابك بنفسك'], 403);
        }

        // إلغاء الجلسات وحذف التوكنات
        $user->tokens()->delete();

        // حذف مؤقت للحساب
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف حساب المستخدم بنجاح'
        ]);
    }
}
