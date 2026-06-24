<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $rawLocale = $request->header('Accept-Language', 'en');
        $primaryLocale = strtolower(explode(',', explode('-', $rawLocale)[0])[0]);
        $locale = in_array($primaryLocale, ['ar', 'en']) ? $primaryLocale : 'en';


        return [
            'id' => $this->product_id, // يُطابق الـ product_id كما تتوقعه صفحات الـ React
            'cartItemId' => $this->id,  // المعرّف الفريد للجدول في قاعدة البيانات
            'quantity' => (int) $this->quantity,
            'title' => $this->product->{"title_{$locale}"},
            'price' => (double) $this->product->price,
            'oldPrice' => $this->product->old_price ? (double) $this->product->old_price : null,
            'discount' => $this->product->discount,
            'category' => $this->product->category->parent ? $this->product->category->parent->{"name_{$locale}"} : $this->product->category->{"name_{$locale}"},
            'subCategory' => $this->product->category->parent ? $this->product->category->{"name_{$locale}"} : null,
            'image' => filter_var($this->product->image_url, FILTER_VALIDATE_URL) ? $this->product->image_url : url($this->product->image_url),
            'isBestseller' => (boolean) $this->product->is_bestseller,
            'isNew' => (boolean) $this->product->is_new,
            'description' => $this->product->{"description_{$locale}"},
            'selectedSize' => $this->size ? $this->size->{"name_{$locale}"} : null,
            'selectedColor' => $this->color ? [
                'name' => $this->color->{"name_{$locale}"},
                'hex' => $this->color->hex
            ] : null,
        ];
    }
}
