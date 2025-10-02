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
            'brand_name' => $this->brand_name,
            'generic_name' => $this->generic_name,
            'dosage' => $this->dosage,
            'drug_form' => $this->drug_form,
            'status' => $this->status,
            'frequency_type' => $this->frequency_type,
            'frequency' => $this->frequency,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'remaining_stock' => $this->remaining_stock,
            'total_quantity' => $this->total_quantity ?? 0,
            'total_value' => $this->total_value ?? 0,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            // 'time_schedules' => $this->whenLoaded('timeSchedules', function () {
            //     // Return the collection of schedules, sorted by time
            //     return $this->timeSchedules->sortBy('schedule_time')->values()->toArray();  
            // }),
            'time_schedules' => $this->whenLoaded('timeSchedules', function () {
                return TimeScheduleResource::collection($this->timeSchedules);
            })
        ];
    }
}
