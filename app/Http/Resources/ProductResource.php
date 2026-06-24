<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // استخراج اللغة بأمان من الهيدر (يتعامل مع قيم المتصفح مثل "en-US,en;q=0.9")
        $rawLocale = $request->header('Accept-Language', 'en');
        $primaryLocale = strtolower(explode(',', explode('-', $rawLocale)[0])[0]);
        $locale = in_array($primaryLocale, ['ar', 'en']) ? $primaryLocale : 'en';


        return [
            'id' => $this->id,
            'title' => $this->{"title_{$locale}"}, // إرجاع العنوان باللغة المطلوبة تلقائياً
            'price' => (double) $this->price,
            'oldPrice' => $this->old_price ? (double) $this->old_price : null,
            'discount' => $this->discount,
            'category' => $this->category->parent ? $this->category->parent->{"name_{$locale}"} : $this->category->{"name_{$locale}"},
            'subCategory' => $this->category->parent ? $this->category->{"name_{$locale}"} : null,
            'image' => filter_var($this->image_url, FILTER_VALIDATE_URL) ? $this->image_url : url($this->image_url), // يدعم روابط Unsplash والروابط المرفوعة محلياً
            'isBestseller' => (boolean) $this->is_bestseller,
            'isNew' => (boolean) $this->is_new,
            'description' => $this->{"description_{$locale}"}, // الوصف باللغة المطلوبة
            'stockQuantity' => (int) $this->stock_quantity,
            'categoryId' => $this->category_id,
            'titleEn' => $this->title_en,
            'titleAr' => $this->title_ar,
            'descriptionEn' => $this->description_en,
            'descriptionAr' => $this->description_ar,
            'sizeIds' => $this->sizes->pluck('id')->toArray(),
            'colorIds' => $this->colors->pluck('id')->toArray(),
            
            // تحويل المقاسات لمصفوفة نصوص بسيطة كما يتوقعها الـ React
            'sizes' => $this->sizes->map(function ($size) use ($locale) {
                return $size->{"name_{$locale}"};
            })->toArray(),

            // تحويل الألوان لصيغة الـ Object المطلوبة بالـ React
            'colors' => $this->colors->map(function ($color) use ($locale) {
                return [
                    'id' => $color->id,
                    'name' => $color->{"name_{$locale}"},
                    'hex' => $color->hex
                ];
            })->toArray(),
        ];
    }
}