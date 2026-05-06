<?php

namespace Database\Factories;

use App\Models\JournalLine;
use App\Models\JournalEntry;
use App\Models\Coa;
use Illuminate\Database\Eloquent\Factories\Factory;

class JournalLineFactory extends Factory
{
    protected $model = JournalLine::class;

    public function definition(): array
    {
        return [
            'journal_entry_id' => JournalEntry::factory(),
            'coa_id' => Coa::factory(),
            'debit' => $this->faker->randomFloat(2, 0, 1000000),
            'credit' => 0,
            'memo' => $this->faker->optional()->sentence(),
        ];
    }

    public function debit($amount): static
    {
        return $this->state(fn (array $attributes) => [
            'debit' => $amount,
            'credit' => 0,
        ]);
    }

    public function credit($amount): static
    {
        return $this->state(fn (array $attributes) => [
            'debit' => 0,
            'credit' => $amount,
        ]);
    }
}