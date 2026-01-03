<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingTransactionRequest extends FormRequest
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
            'name' => 'required|string|max:225',
            'phone' => 'required|string|max:225',
            'email' => 'required|string|max:225',
            'city' => 'required|string|max:225',
            'address' => 'required|string|max:225',
            'post_code' => 'required|string|max:225',
            'proof' => 'required|file|mimes:png,jpg,jpeg|max:2048',
            'cosmetic_ids' => 'required|array',
            'cosmetic_ids.*.id' => 'required|integer|exists:cosmetics,id',
            'cosmetic_ids.*.quantity' => 'required|integer|min:1
            ',
        ];
    }
}
