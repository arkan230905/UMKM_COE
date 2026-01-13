<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeLoginRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'kode_perusahaan' => 'required|string|min:3|max:10',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            
            'kode_perusahaan.required' => 'Kode perusahaan wajib diisi.',
            'kode_perusahaan.string' => 'Kode perusahaan harus berupa teks.',
            'kode_perusahaan.min' => 'Kode perusahaan minimal 3 karakter.',
            'kode_perusahaan.max' => 'Kode perusahaan maksimal 10 karakter.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'email' => 'email',
            'kode_perusahaan' => 'kode perusahaan',
        ];
    }
}