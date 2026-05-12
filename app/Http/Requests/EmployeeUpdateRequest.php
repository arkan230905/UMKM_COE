<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $pegawaiId = $this->route('pegawai') ?? $this->route('id') ?? $this->input('id');
        
        return [
            'nama' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('pegawais', 'email')->ignore($pegawaiId)
            ],
            'no_telp' => 'required|string|max:20',
            'alamat' => 'required|string|max:500',
            'jenis_kelamin' => 'required|in:L,P',
            'jabatan' => 'required|string|max:255',
            'gaji_pokok' => 'required|numeric|min:0',
            'tarif_per_jam' => 'required|numeric|min:0',
            'jam_kerja_per_minggu' => 'nullable|numeric|min:0|max:168',
            'tunjangan' => 'nullable|numeric|min:0',
            'jenis_pegawai' => 'required|in:tetap,kontrak,harian,btkl,btktl',
            'kategori_tenaga_kerja' => 'nullable|in:BTKL,BTKTL',
            'bank' => 'nullable|string|max:100',
            'nomor_rekening' => 'nullable|string|max:50',
            'nama_rekening' => 'nullable|string|max:255',
            'asuransi' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama pegawai wajib diisi.',
            'nama.string' => 'Nama pegawai harus berupa teks.',
            'nama.max' => 'Nama pegawai maksimal 255 karakter.',
            
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar untuk pegawai lain.',
            
            'no_telp.required' => 'Nomor telepon wajib diisi.',
            'no_telp.string' => 'Nomor telepon harus berupa teks.',
            'no_telp.max' => 'Nomor telepon maksimal 20 karakter.',
            
            'alamat.required' => 'Alamat wajib diisi.',
            'alamat.string' => 'Alamat harus berupa teks.',
            'alamat.max' => 'Alamat maksimal 500 karakter.',
            
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan).',
            
            'jabatan.required' => 'Jabatan wajib diisi.',
            'jabatan.string' => 'Jabatan harus berupa teks.',
            'jabatan.max' => 'Jabatan maksimal 255 karakter.',
            
            'gaji_pokok.required' => 'Gaji pokok wajib diisi.',
            'gaji_pokok.numeric' => 'Gaji pokok harus berupa angka.',
            'gaji_pokok.min' => 'Gaji pokok tidak boleh kurang dari 0.',
            
            'tarif_per_jam.required' => 'Tarif per jam wajib diisi.',
            'tarif_per_jam.numeric' => 'Tarif per jam harus berupa angka.',
            'tarif_per_jam.min' => 'Tarif per jam tidak boleh kurang dari 0.',
            
            'jam_kerja_per_minggu.numeric' => 'Jam kerja per minggu harus berupa angka.',
            'jam_kerja_per_minggu.min' => 'Jam kerja per minggu tidak boleh kurang dari 0.',
            'jam_kerja_per_minggu.max' => 'Jam kerja per minggu maksimal 168 jam.',
            
            'tunjangan.numeric' => 'Tunjangan harus berupa angka.',
            'tunjangan.min' => 'Tunjangan tidak boleh kurang dari 0.',
            
            'jenis_pegawai.required' => 'Jenis pegawai wajib dipilih.',
            'jenis_pegawai.in' => 'Jenis pegawai harus salah satu dari: tetap, kontrak, harian, btkl, btktl.',
            
            'kategori_tenaga_kerja.in' => 'Kategori tenaga kerja harus BTKL atau BTKTL.',
            
            'bank.string' => 'Nama bank harus berupa teks.',
            'bank.max' => 'Nama bank maksimal 100 karakter.',
            
            'nomor_rekening.string' => 'Nomor rekening harus berupa teks.',
            'nomor_rekening.max' => 'Nomor rekening maksimal 50 karakter.',
            
            'nama_rekening.string' => 'Nama rekening harus berupa teks.',
            'nama_rekening.max' => 'Nama rekening maksimal 255 karakter.',
            
            'asuransi.numeric' => 'Asuransi harus berupa angka.',
            'asuransi.min' => 'Asuransi tidak boleh kurang dari 0.',
        ];
    }
}