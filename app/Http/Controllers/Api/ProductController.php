<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // ==========================================
    // 1. READ (قراءة المنتجات والأقسام والتقييمات)
    // ==========================================

    // جلب جميع المنتجات مع دعم الفلترة والبحث
public function index(\Illuminate\Http\Request $request)
{
    try {
        $query = Product::with(['category', 'colors', 'sizes']);

        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->has('size')) {
            $query->whereHas('sizes', function ($q) use ($request) {
                $q->where('sizes.id', $request->size);
            });
        }

        if ($request->has('color')) {
            $query->whereHas('colors', function ($q) use ($request) {
                $q->where('colors.id', $request->color);
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title_en', 'like', "%{$search}%")
                    ->orWhere('title_ar', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->get();

        // محاولة إرجاع البيانات عبر الـ Resource المصمم
        try {
            return response()->json([
                'status' => 'success',
                'data' => \App\Http\Resources\ProductResource::collection($products)
            ]);
        } catch (\Exception $resourceException) {
            // إذا فشل الـ Resource لأي سبب، سنرجع البيانات الخام مباشرة لكي لا يتعطل الموقع
            return response()->json([
                'status' => 'success',
                'debug_mode' => 'fallback_to_raw_data due to resource error',
                'error_message' => $resourceException->getMessage(),
                'data' => $products
            ]);
        }

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'حدث خطأ في السيرفر',
            'error' => $e->getMessage()
        ], 500);
    }
}

    // عرض تفاصيل منتج واحد محدد
    public function show($id)
    {
        $product = Product::with(['category', 'images', 'colors', 'sizes'])->find($id);

        if (!$product) {
            return response()->json(['status' => 'error', 'message' => 'المنتج غير موجود'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => new \App\Http\Resources\ProductResource($product)
        ]);
    }

    // ==========================================
    // 2. CREATE (إضافة منتج جديد بقاعدة البيانات)
    // ==========================================
    public function store(Request $request)
    {
        // التحقق من صحة البيانات المرسلة من الفرونت آند
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title_en' => 'required|string|max:255',
            'title_ar' => 'required|string|max:255',
            'description_en' => 'required|string',
            'description_ar' => 'required|string',
            'price' => 'required|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048', // للتحقق من رفع صورة حقيقية
            'is_bestseller' => 'boolean',
            'is_new' => 'boolean',
            'stock_quantity' => 'required|integer|min:0',
            'sizes' => 'nullable|array', // مصفوفة الـ IDs للمقاسات المتاحة
            'sizes.*' => 'exists:sizes,id',
            'colors' => 'nullable|array', // مصفوفة الـ IDs للألوان المتاحة
            'colors.*' => 'exists:colors,id',
        ]);

        // معالجة ورفع الصورة لمجلد التخزين (Storage)
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $imageUrl = Storage::url($path); // توليد رابط الصورة للفرونت آند
        }

        // إنشاء المنتج
        $product = Product::create([
            'category_id' => $request->category_id,
            'title_en' => $request->title_en,
            'title_ar' => $request->title_ar,
            'slug' => Str::slug($request->title_en), // توليد الـ slug تلقائياً
            'description_en' => $request->description_en,
            'description_ar' => $request->description_ar,
            'price' => $request->price,
            'old_price' => $request->old_price,
            'discount' => $request->discount,
            'image_url' => $imageUrl,
            'is_bestseller' => $request->is_bestseller ?? false,
            'is_new' => $request->is_new ?? true,
            'stock_quantity' => $request->stock_quantity,
        ]);

        // ربط المقاسات والألوان بالجداول الوسيطة تلقائياً
        if ($request->has('sizes')) {
            $product->sizes()->attach($request->sizes);
        }
        if ($request->has('colors')) {
            $product->colors()->attach($request->colors);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم إضافة المنتج بنجاح!',
            'data' => $product->load(['category', 'sizes', 'colors'])
        ], 201);
    }

    // ==========================================
    // 3. UPDATE (تعديل منتج موجود بالفعل)
    // ==========================================
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['status' => 'error', 'message' => 'المنتج غير موجود'], 404);
        }

        $request->validate([
            'category_id' => 'exists:categories,id',
            'title_en' => 'string|max:255',
            'title_ar' => 'string|max:255',
            'price' => 'numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'stock_quantity' => 'integer|min:0',
            'sizes' => 'nullable|array',
            'colors' => 'nullable|array',
        ]);

        // تحديث الصورة القديمة إذا تم رفع صورة جديدة
        if ($request->hasFile('image')) {
            // حذف الصورة القديمة من السيرفر لتوفير المساحة
            if ($product->image_url) {
                $oldPath = str_replace('/storage/', '', $product->image_url);
                Storage::disk('public')->delete($oldPath);
            }
            // رفع الصورة الجديدة
            $path = $request->file('image')->store('products', 'public');
            $product->image_url = Storage::url($path);
        }

        // تحديث الحقول الأخرى المرسلة فقط
        $product->update($request->except(['image', 'sizes', 'colors']));

        // تحديث الـ slug لو تم تعديل العنوان
        if ($request->has('title_en')) {
            $product->slug = Str::slug($request->title_en);
            $product->save();
        }

        // استخدام دالة sync السحرية لمزامنة المقاسات والألوان وحذف القديم غير المختار
        if ($request->has('sizes')) {
            $product->sizes()->sync($request->sizes);
        }
        if ($request->has('colors')) {
            $product->colors()->sync($request->colors);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم تعديل المنتج بنجاح!',
            'data' => $product->load(['category', 'sizes', 'colors'])
        ]);
    }

    // ==========================================
    // 4. DELETE (حذف منتج - حذف مؤقت Soft Delete)
    // ==========================================
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['status' => 'error', 'message' => 'المنتج غير موجود'], 404);
        }

        $product->delete(); // سيقوم بالحذف المؤقت لأننا قمنا بإعداد SoftDeletes في الموديل

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف المنتج بنجاح وأرشفته!'
        ]);
    }

    public function categories()
    {
        return response()->json(['status' => 'success', 'data' => Category::whereNull('parent_id')->with('children')->get()]);
    }

    public function colors()
    {
        return response()->json(['status' => 'success', 'data' => \App\Models\Color::all()]);
    }

    public function sizes()
    {
        return response()->json(['status' => 'success', 'data' => \App\Models\Size::all()]);
    }

    public function testimonials()
    {
        return response()->json(['status' => 'success', 'data' => Testimonial::with('user')->latest()->take(6)->get()]);
    }
}
