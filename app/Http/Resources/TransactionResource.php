<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'amount' => $this->amount,
            'note' => $this->note,
            'created_at' => $this->created_at?->format('Y-m-d'),
            'brand' => $this->whenLoaded('brand', function () {
                return [
                    'id' => $this->brand->id,
                    'name' => $this->brand->name,
                    'category' => $this->brand->category ? [
                        'id' => $this->brand->category->id,
                        'name' => $this->brand->category->name,
                        'type' => $this->brand->category->type,
                        'color' => $this->brand->category->color,
                        'icon' => $this->brand->category->icon,
                    ] : null,
                ];
            }),
        ];
    }
}

