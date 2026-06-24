<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'id' => $this->id,
            'productId' => $this->product_id,
            'title' => $this->product->{"title_{$locale}"},
            'image' => filter_var($this->product->image_url, FILTER_VALIDATE_URL) ? $this->product->image_url : url($this->product->image_url),
            'quantity' => (int) $this->quantity,
            'price' => (double) $this->price,
            'colorName' => $this->{"color_name_{$locale}"},
            'colorHex' => $this->color_hex,
            'sizeName' => $this->{"size_name_{$locale}"},
        ];
    }
}
