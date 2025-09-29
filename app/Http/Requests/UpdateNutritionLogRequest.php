<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNutritionLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */ 
    public function rules(): array
    {
        return [
            'food_name'   => 'sometimes|required|string|max:255',
            'calories'    => 'sometimes|required|integer|min:0',
            'protein_g'   => 'sometimes|required|numeric|min:0',
            'carbs_g'     => 'sometimes|required|numeric|min:0',
            'fat_g'       => 'sometimes|required|numeric|min:0',
            'meal_type'   => 'sometimes|required|in:sarapan,makan_siang,makan_malam,camilan',
            'consumed_at' => 'sometimes|nullable|date',
        ];
    }
}
