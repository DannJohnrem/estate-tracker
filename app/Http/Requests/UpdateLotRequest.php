<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLotRequest extends FormRequest
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
            'client_id'             => 'required|exists:clients,id',
            'lot_number'            => 'required|string|max:50',
            'block_number'          => 'nullable|string|max:50',
            'subdivision'           => 'required|string|max:150',
            'phase'                 => 'nullable|string|max:50',
            'lot_area'              => 'required|numeric|min:1',
            'total_contract_price'  => 'required|numeric|min:1',
            'down_payment'          => 'required|numeric|min:0',
            'monthly_amortization'  => 'required|numeric|min:1',
            'term_months'           => 'required|integer|min:1',
            'months_paid'           => 'required|integer|min:0',
            'start_date'            => 'required|date',
            'next_due_date'         => 'nullable|date',
            'status'                => 'required|in:active,delinquent,fully_paid,cancelled',
        ];
    }
}
