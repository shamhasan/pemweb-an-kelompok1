<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'author'  => $this->whenLoaded('user', function () {
                return [
                    'id'    => $this->user->id,
                    'name'  => $this->user->name,
                    'email' => $this->user->email,
                ];
            }, [
                // fallback jika relasi user tidak di-load
                'id' => $this->user_id,
            ]),
            'rating'  => $this->rating,      // bisa null
            'comment' => $this->comment,

            // Timestamps dalam format yang enak dikonsumsi
            'created_at'       => optional($this->created_at)->toISOString(),
            'created_at_human' => optional($this->created_at)->diffForHumans(),
            'updated_at'       => optional($this->updated_at)->toISOString(),
        ];
    }
}
