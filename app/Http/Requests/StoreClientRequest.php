<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the valid ID options.
     */
    private array $validIdOptions = [
        "Driver's License",
        'Passport',
        'SSS ID',
        'GSIS ID',
        'PhilHealth ID',
        'Pag-IBIG ID',
        'Postal ID',
        "Voter's ID",
        'National ID (PhilSys)',
        'PRC ID',
        'TIN ID',
        'Senior Citizen ID',
        'PWD ID',
        'OFW ID',
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:clients'],
            'phone_number' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'valid_id_type' => ['required', 'string', 'in:' . implode(',', $this->validIdOptions)],
            'valid_id_number' => ['required', 'string', 'unique:clients,valid_id_number'],
        ];
    }
}
