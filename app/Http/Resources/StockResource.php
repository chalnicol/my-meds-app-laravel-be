<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
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
            'quantity' => $this->quantity,
            'price' => $this->price,
            'source' => $this->source,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'medication_id' => $this->medication_id,
            'medication' => new UserResource($this->whenLoaded('medication')),
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
