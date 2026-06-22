<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    /**
     * عرض جميع الأقسام مع القسم الأب وعدد المنتجات
     */
    public function index(): JsonResponse
    {
        $categories = Category::with('parent')
            ->withCount('products')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * إضافة قسم جديد
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        // توليد الـ slug تلقائياً والتأكد من فرادته
        $slug = Str::slug($request->name_en);
        $originalSlug = $slug;
        $count = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $category = Category::create([
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
            'parent_id' => $request->parent_id,
            'slug' => $slug,
        ]);

        // تحميل علاقة الأب للحصول على البيانات كاملة في الاستجابة
        $category->load('parent');
        $category->products_count = 0;

        return response()->json([
            'status' => 'success',
            'message' => 'تم إضافة القسم بنجاح',
            'data' => $category
        ], 201);
    }

    /**
     * تعديل قسم موجود
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category = Category::findOrFail($id);

        // منع القسم من أن يكون أبًا لنفسه
        if ($request->parent_id == $id) {
            return response()->json([
                'status' => 'error',
                'message' => 'لا يمكن للقسم أن يكون أبًا لنفسه'
            ], 422);
        }

        // منع الدوران (أي تعيين قسم فرعي كقسم أب)
        if ($request->parent_id) {
            $tempParentId = $request->parent_id;
            while ($tempParentId) {
                if ($tempParentId == $id) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'لا يمكن تعيين قسم فرعي كقسم أب لهذا القسم'
                    ], 422);
                }
                $parentCategory = Category::find($tempParentId);
                $tempParentId = $parentCategory ? $parentCategory->parent_id : null;
            }
        }

        // تحديث الـ slug إذا تغير الاسم بالإنجليزية
        if ($category->name_en !== $request->name_en) {
            $slug = Str::slug($request->name_en);
            $originalSlug = $slug;
            $count = 1;
            while (Category::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            $category->slug = $slug;
        }

        $category->name_en = $request->name_en;
        $category->name_ar = $request->name_ar;
        $category->parent_id = $request->parent_id;
        $category->save();

        $category->load('parent');
        $category->loadCount('products');

        return response()->json([
            'status' => 'success',
            'message' => 'تم تعديل القسم بنجاح',
            'data' => $category
        ]);
    }

    /**
     * حذف قسم
     */
    public function destroy($id): JsonResponse
    {
        $category = Category::findOrFail($id);

        // منع الحذف إذا كان هناك منتجات نشطة مرتبطة بالقسم
        if ($category->products()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'لا يمكن حذف القسم لأنه يحتوي على منتجات نشطة'
            ], 409);
        }

        // تعيين parent_id للأقسام الفرعية إلى null لتفادي الحذف المتتالي (cascade delete) أو الروابط المقطوعة
        Category::where('parent_id', $category->id)->update(['parent_id' => null]);

        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف القسم بنجاح'
        ]);
    }
}
