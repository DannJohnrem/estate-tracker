<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
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
            'client_id' => ['required', 'uuid', 'exists:clients,id'],
            'agent_id' => ['nullable', 'uuid', 'exists:agents,id'],
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'lot_number' => ['required', 'string', 'max:50'],
            'block_number' => ['nullable', 'string', 'max:50'],
            'subdivision' => ['required', 'string', 'max:150'],
            'phase' => ['nullable', 'string', 'max:50'],
            'lot_area' => ['nullable', 'numeric', 'min:0'],
            'reservation_fee' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'reservation_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:reservation_date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
