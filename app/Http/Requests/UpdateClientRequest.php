<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
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
            'first_name'      => 'required|string|max:100',
            'middle_name'     => 'nullable|string|max:100',
            'last_name'       => 'required|string|max:100',
            'email'           => "required|email|unique:clients,email,{$this->client->id}",
            'phone_number'    => 'nullable|string|max:20',
            'address'         => 'nullable|string|max:255',
            'valid_id_type'   => "nullable|string|max:100|in:" . implode(',', $this->validIdOptions),
            'valid_id_number' => 'nullable|string|max:100',
        ];
    }
}
