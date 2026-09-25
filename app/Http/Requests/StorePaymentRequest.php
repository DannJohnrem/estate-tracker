<?php

namespace App\Http\Requests;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Quick record from the Lot page: the lot comes from the URL (/lots/{lot}/payments)
        if ($lot = $this->route('lot')) {
            $this->merge(['lot_id' => is_object($lot) ? $lot->getKey() : $lot]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lot_id' => ['required', 'integer', Rule::exists('lots', 'id')->whereNull('deleted_at')],
            'amount' => ['required', 'numeric', 'min:1', 'max:99999999.99'],
            'paid_at' => ['required', 'date'],
            'method' => ['nullable', Rule::in(Payment::METHODS)],
            'or_number' => ['nullable', 'string', 'max:50', 'unique:payments,or_number'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
