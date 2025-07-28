<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentCallResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'call_type' => $this->callType->ctype_name_ar ?? null,
            'student' => $this->student->name_ar ?? null,
            'school' => $this->school->name_ar ?? null,
            'branch' => $this->branch->name_ar ?? null,
            'user' => $this->user->name ?? null,
            'call_cdate' => $this->call_cdate,
            'call_edate' => $this->call_edate,
            'status' => $this->status,
            'caller_type' => $this->caller_type,
            'call_level' => $this->call_level,
        ];
    }
}
