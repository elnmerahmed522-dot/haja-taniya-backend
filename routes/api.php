<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AdminOrderController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminCategoryController;

// ==========================================
// 🌍 مسارات عامة (لا تحتاج تسجيل دخول)
// ==========================================
Route::get('/categories', [ProductController::class, 'categories']);
Route::get('/colors', [ProductController::class, 'colors']);
Route::get('/sizes', [ProductController::class, 'sizes']);
Route::get('/testimonials', [ProductController::class, 'testimonials']);

// جلب المنتجات وتفاصيل منتج معين (عام للكل)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);


// ==========================================
// 🔒 مسارات الأدمن (تحتاج تسجيل دخول + دور Admin)
// ==========================================
Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    // ── إدارة المنتجات ──
    Route::post('/products', [ProductController::class, 'store']);
    Route::post('/products/{id}', [ProductController::class, 'update']); // لدعم رفع الصور في التحديث
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::patch('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // ── إدارة الأقسام (Categories) ──
    Route::get('/admin/categories', [AdminCategoryController::class, 'index']);
    Route::post('/admin/categories', [AdminCategoryController::class, 'store']);
    Route::post('/admin/categories/{id}', [AdminCategoryController::class, 'update']);
    Route::delete('/admin/categories/{id}', [AdminCategoryController::class, 'destroy']);

    // ── لوحة الإحصائيات ──
    Route::get('/admin/stats', [AdminDashboardController::class, 'stats']);

    // ── إدارة الطلبات ──
    Route::get('/admin/orders', [AdminOrderController::class, 'index']);
    Route::get('/admin/orders/{id}', [AdminOrderController::class, 'show']);
    Route::patch('/admin/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);
    Route::patch('/admin/orders/{id}/notes', [AdminOrderController::class, 'updateNotes']);

    // ── إدارة المستخدمين ──
    Route::get('/admin/users', [AdminUserController::class, 'index']);
    Route::patch('/admin/users/{id}/role', [AdminUserController::class, 'updateRole']);
    Route::patch('/admin/users/{id}/block', [AdminUserController::class, 'toggleBlock']);
    Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy']);
});



// مسارات المستخدمين المسجلين (تحتاج Sanctum Token)
Route::middleware(['auth:sanctum'])->group(function () {
    // جلب بيانات المستخدم
    Route::get('/user', function (Request $request) {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()
        ]);
    });

    // 🛒 مسارات سلة المشتريات (Cart)
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::post('/cart/sync', [CartController::class, 'sync']);
    Route::put('/cart/quantity', [CartController::class, 'updateQuantity']);
    Route::put('/cart/variant', [CartController::class, 'updateVariant']);
    Route::delete('/cart/item', [CartController::class, 'removeItem']);
    Route::delete('/cart', [CartController::class, 'clear']);

    // 💖 مسارات المفضلة (Wishlist)
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::post('/wishlist/sync', [WishlistController::class, 'sync']);
    Route::delete('/wishlist/{product_id}', [WishlistController::class, 'destroy']);
    Route::delete('/wishlist', [WishlistController::class, 'clear']);

    // 📦 مسارات الطلبات (Orders)
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
});

// ==========================================
// 🔑 مسارات المصادقة (Login, Register, Logout)
// ==========================================
require __DIR__.'/auth.php';