<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NutritionLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'food_name'   => $this->food_name,
            'calories'    => $this->calories,
            'protein_g'   => (float) $this->protein_g,
            'carbs_g'     => (float) $this->carbs_g,
            'fat_g'       => (float) $this->fat_g,
            'meal_type'   => $this->meal_type,
            'consumed_at' => $this->consumed_at->toDateTimeString(),
            'created_at'  => $this->created_at->toDateTimeString(),
        ];    }
}
