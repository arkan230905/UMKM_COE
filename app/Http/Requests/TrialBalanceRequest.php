<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrialBalanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'owner']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'bulan' => 'nullable|integer|min:1|max:12',
            'tahun' => 'nullable|integer|min:2020|max:2030',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'bulan.integer' => 'Bulan harus berupa angka',
            'bulan.min' => 'Bulan minimal 1 (Januari)',
            'bulan.max' => 'Bulan maksimal 12 (Desember)',
            'tahun.integer' => 'Tahun harus berupa angka',
            'tahun.min' => 'Tahun minimal 2020',
            'tahun.max' => 'Tahun maksimal 2030',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'bulan' => $this->bulan ?: date('m'),
            'tahun' => $this->tahun ?: date('Y'),
        ]);
    }
}