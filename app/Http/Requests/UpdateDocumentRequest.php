<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'], // PDF only, max 20 MB
        ];
    }

    public function messages(): array
    {
        return [
            'file.uploaded' => 'The file failed to upload. It may be larger than the server upload limit.',
            'file.mimes' => 'The file must be a PDF, JPG, JPEG or PNG.',
            'file.max' => 'The file may not be larger than 20 MB.',
        ];
    }
}
