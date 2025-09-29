<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; 


class StoreNutritionLogRequest extends FormRequest
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
            'food_name'   => 'required|string|max:255',
            'calories'    => 'required|integer|min:0',
            'protein_g'   => 'required|numeric|min:0',
            'carbs_g'     => 'required|numeric|min:0',
            'fat_g'       => 'required|numeric|min:0',
            'meal_type'   => ['required', Rule::in(['sarapan', 'makan_siang', 'makan_malam', 'camilan'])],
            'consumed_at' => 'nullable|date',
        ];
    }
}
