<?php

namespace Database\Factories;

use App\Models\Coa;
use Illuminate\Database\Eloquent\Factories\Factory;

class CoaFactory extends Factory
{
    protected $model = Coa::class;

    public function definition(): array
    {
        return [
            'kode_akun' => $this->faker->unique()->numerify('####'),
            'nama_akun' => $this->faker->words(3, true),
            'kategori_akun' => $this->faker->randomElement(['Kas dan Bank', 'Piutang', 'Persediaan', 'Aset Tetap']),
            'tipe_akun' => $this->faker->randomElement(['ASET', 'KEWAJIBAN', 'MODAL', 'PENDAPATAN', 'BEBAN']),
            'saldo_normal' => 'debit',
            'keterangan' => $this->faker->sentence(),
            'saldo_awal' => $this->faker->numberBetween(0, 10000000),
            'tanggal_saldo_awal' => $this->faker->date(),
            'posted_saldo_awal' => $this->faker->boolean(),
            'user_id' => 1, // Default user for testing
        ];
    }

    public function aset(): static
    {
        return $this->state(fn (array $attributes) => [
            'kode_akun' => $this->faker->numerify('11##'),
            'tipe_akun' => 'ASET',
            'saldo_normal' => 'debit',
        ]);
    }

    public function kewajiban(): static
    {
        return $this->state(fn (array $attributes) => [
            'kode_akun' => $this->faker->numerify('21##'),
            'tipe_akun' => 'KEWAJIBAN',
            'saldo_normal' => 'kredit',
        ]);
    }

    public function modal(): static
    {
        return $this->state(fn (array $attributes) => [
            'kode_akun' => $this->faker->numerify('31##'),
            'tipe_akun' => 'MODAL',
            'saldo_normal' => 'kredit',
        ]);
    }

    public function pendapatan(): static
    {
        return $this->state(fn (array $attributes) => [
            'kode_akun' => $this->faker->numerify('41##'),
            'tipe_akun' => 'PENDAPATAN',
            'saldo_normal' => 'kredit',
        ]);
    }

    public function beban(): static
    {
        return $this->state(fn (array $attributes) => [
            'kode_akun' => $this->faker->numerify('51##'),
            'tipe_akun' => 'BEBAN',
            'saldo_normal' => 'debit',
        ]);
    }
}