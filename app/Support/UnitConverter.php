<?php

namespace App\Support;

class UnitConverter
{
    // Base groups: mass (g), volume (ml), count (pcs)
    private array $mass = [
        'kg' => 1000.0,
        'g'  => 1.0,
        'mg' => 0.001,
        'ons' => 100.0, // umum di Indonesia: 1 ons = 100 gram
    ];

    private array $volume = [
        'l'   => 1000.0,
        'liter' => 1000.0,
        'ml'  => 1.0,
        'sdt' => 5.0,      // sendok teh ≈ 5 ml
        'sendok_teh' => 5.0,
        'sdm' => 15.0,     // sendok makan ≈ 15 ml
        'sendok_makan' => 15.0,
        'cup' => 240.0,
    ];

    private array $count = [
        'pcs' => 1.0,
        'buah' => 1.0,
        'butir' => 1.0,
    ];

    // Assumed density for converting volume <-> mass when needed (g per ml)
    // This is an approximation; ideally this should be specified per-ingredient.
    private float $assumedDensity = 1.0; // 1 g/ml

    private function normalizeUnit(string $u): string
    {
        $u = strtolower(trim($u));
        $aliases = [
            // massa
            'gram' => 'g', 'gr' => 'g', 'g' => 'g',
            'kilogram' => 'kg', 'kg' => 'kg',
            'milligram' => 'mg', 'miligram' => 'mg', 'mg' => 'mg',
            'ons' => 'ons',
            // volume
            'liter' => 'l', 'ltr' => 'l', 'l' => 'l',
            'milliliter' => 'ml', 'mililiter' => 'ml', 'ml' => 'ml', 'cc' => 'ml',
            'sendok_teh' => 'sdt', 'sendok teh' => 'sdt', 'sdt' => 'sdt',
            'sendok_makan' => 'sdm', 'sendok makan' => 'sdm', 'sdm' => 'sdm',
            'cup' => 'cup',
            // hitungan
            'pcs' => 'pcs', 'buah' => 'pcs', 'butir' => 'pcs', 'biji' => 'pcs',
        ];
        return $aliases[$u] ?? $u;
    }

    public function convert(float $amount, string $from, string $to): float
    {
        $from = $this->normalizeUnit($from);
        $to   = $this->normalizeUnit($to);

        if ($from === $to) {
            return $amount;
        }

        // Same group conversion
        if ($this->inMass($from) && $this->inMass($to)) {
            return $amount * ($this->mass[$from] / $this->mass[$to]);
        }
        if ($this->inVolume($from) && $this->inVolume($to)) {
            return $amount * ($this->volume[$from] / $this->volume[$to]);
        }
        if ($this->inCount($from) && $this->inCount($to)) {
            return $amount * ($this->count[$from] / $this->count[$to]);
        }

        // Cross conversion using assumed density between volume and mass
        if ($this->inVolume($from) && $this->inMass($to)) {
            $ml = $amount * $this->volume[$from];
            $g  = $ml * $this->assumedDensity; // g = ml * density
            return $g / $this->mass[$to];
        }
        if ($this->inMass($from) && $this->inVolume($to)) {
            $g  = $amount * $this->mass[$from];
            $ml = $g / $this->assumedDensity; // ml = g / density
            return $ml / $this->volume[$to];
        }

        // Fallback: unsupported conversion, return as-is
        return $amount;
    }

    public function describe(string $from, string $to): string
    {
        $from = $this->normalizeUnit($from);
        $to   = $this->normalizeUnit($to);
        if ($from === $to) return "tanpa konversi";
        if ($this->inMass($from) && $this->inMass($to)) return "konversi massa";
        if ($this->inVolume($from) && $this->inVolume($to)) return "konversi volume";
        if (($this->inVolume($from) && $this->inMass($to)) || ($this->inMass($from) && $this->inVolume($to))) {
            return "konversi volume↔massa (asumsi 1 g/ml)";
        }
        if ($this->inCount($from) && $this->inCount($to)) return "konversi unit hitung";
        return "konversi tidak dikenal";
    }

    private function inMass(string $u): bool { return array_key_exists($u, $this->mass); }
    private function inVolume(string $u): bool { return array_key_exists($u, $this->volume); }
    private function inCount(string $u): bool { return array_key_exists($u, $this->count); }
}
