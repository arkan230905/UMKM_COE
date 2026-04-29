<?php

namespace Database\Factories;

use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

class JournalEntryFactory extends Factory
{
    protected $model = JournalEntry::class;

    public function definition(): array
    {
        return [
            'tanggal' => $this->faker->date(),
            'ref_type' => $this->faker->randomElement(['manual', 'sale', 'purchase', 'payment']),
            'ref_id' => $this->faker->optional()->randomNumber(),
            'memo' => $this->faker->sentence(),
        ];
    }
}