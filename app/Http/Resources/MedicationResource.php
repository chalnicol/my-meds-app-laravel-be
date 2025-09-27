<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicationResource extends JsonResource
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
            'brandName' => $this->brandName,
            'genericName' => $this->genericName,
            'dosage' => $this->dosage,
            'status' => $this->status,
            'frequencyType' => $this->frequencyType,
            'frequency' => $this->frequency,
            'dailySchedule' => $this->dailySchedule,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'remainingStock' => $this->remainingStock,
            'user_id' => $this->user_id,
            'totalQuantity' => $this->total_quantity ?? 0,
            'totalValue' => $this->total_value ?? 0,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
