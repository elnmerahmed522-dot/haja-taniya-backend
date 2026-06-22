<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'orderNumber' => $this->order_number,
            'subtotal' => (double) $this->subtotal,
            'shippingCost' => (double) $this->shipping_cost,
            'tax' => (double) $this->tax,
            'total' => (double) $this->total,
            'status' => $this->status,
            'shippingAddress' => $this->shipping_address,
            'phone' => $this->phone,
            'createdAt' => $this->created_at->toIso8601String(),
            'customerName' => $this->user?->name ?? 'Guest',
            'customerEmail' => $this->user?->email ?? '',
            'notes' => $this->notes,
            'adminNotes' => $this->admin_notes,
            'paymentMethod' => $this->payment_method,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
