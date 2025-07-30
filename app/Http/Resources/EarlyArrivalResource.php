<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EarlyArrivalResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'student' => [
                'id' => $this->student?->id,
                'name' => $this->student?->name_ar,
            ],
            'guardian' => [
                'id' => $this->guardian?->id,
                'name' => $this->guardian?->name_ar,
            ],
            'school' => [
                'id' => $this->school?->id,
                'name' => $this->school?->name_ar,
            ],
            'branch' => [
                'id' => $this->branch?->id,
                'name' => $this->branch?->name_ar,
            ],
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],
            'pickup_date' => $this->pickup_date,
            'pickup_time' => $this->pickup_time,
            'pickup_reason' => $this->pickup_reason,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
