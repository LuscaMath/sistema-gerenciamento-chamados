<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority->value,
            'status' => $this->status->value,
            'solution' => $this->solution,

            'requester' => [
                'id' => $this->requester->id,
                'name' => $this->requester->name,
            ],

            'technician' => $this->technician ? [
                'id' => $this->technician->id,
                'name' => $this->technician->name,
            ] : null,

            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ],

            'resolved_at' => $this->resolved_at,
            'closed_at' => $this->closed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
