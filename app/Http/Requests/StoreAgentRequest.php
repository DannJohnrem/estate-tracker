<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAgentRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name'      => ['required', 'string', 'max:255'],
            'middle_name'     => ['nullable', 'string', 'max:255'],
            'last_name'       => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255', 'unique:agents,email'],
            'phone_number'    => ['nullable', 'string', 'max:50'],
            'license_number'  => ['nullable', 'string', 'max:100'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'status'          => ['required', 'in:active,inactive'],
        ];
    }
}
