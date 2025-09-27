<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'fullname' => $this->fullname,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_blocked' => (bool) $this->is_blocked, 
            'social_user' => (bool) $this->firebase_uid != null,
            'firebase_uid' => $this->firebase_uid,
            'timezone' => $this->timezone,
            
            $this->mergeWhen(isset($this->medications_count), [
                'medications_count' => $this->medications_count,
            ]),
            $this->mergeWhen(isset($this->stocks_count), [
                'stocks_count' => $this->stocks_count,
            ]),
          
            // Include roles and permissions
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name'); // Just send role names
            }),
            'permissions' => $this->all_permissions, // Use the accessor we defined in the User model
            // If you added 'can_access' accessor
            // 'can_access' => $this->can_access,
        ];
    }
}
