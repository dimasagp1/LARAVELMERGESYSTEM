<?php

namespace App\Modules\Rnd\Services;

use Illuminate\Validation\ValidationException;

class FormulaCalculationService
{
    /**
     * Validate 100.00% precision rule and calculate recipe metrics.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function calculate(array $items, float $servingSizeG, int $servingsPerPack): array
    {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'Formula wajib memiliki minimal 1 bahan baku.',
            ]);
        }

        $totalPercentage = 0.0;
        $totalCostPerKg = 0.0;
        $totalCostPerServing = 0.0;
        $processedItems = [];

        foreach ($items as $idx => $item) {
            $percentage = (float) ($item['percentage'] ?? 0);
            $pricePerKg = (float) ($item['price_per_kg'] ?? 0);

            $totalPercentage += $percentage;

            // Dose calculation (in grams)
            $weightPerServingG = ($percentage / 100.0) * $servingSizeG;

            // Cost per serving: (pricePerKg / 1000 grams) * weightPerServingG
            $pricePerServing = ($pricePerKg / 1000.0) * $weightPerServingG;

            // Cost component per kg
            $costPerKgComponent = ($percentage / 100.0) * $pricePerKg;

            $totalCostPerKg += $costPerKgComponent;
            $totalCostPerServing += $pricePerServing;

            $processedItems[] = [
                'material_product_id' => $item['material_product_id'],
                'vendor_id' => $item['vendor_id'] ?? null,
                'percentage' => round($percentage, 4),
                'weight_per_serving_g' => round($weightPerServingG, 4),
                'price_per_kg' => round($pricePerKg, 2),
                'price_per_serving' => round($pricePerServing, 4),
                'notes' => $item['notes'] ?? null,
            ];
        }

        // Strict 100.00% validation (tolerance 0.001%)
        if (abs($totalPercentage - 100.0) > 0.001) {
            $diff = round($totalPercentage - 100.0, 4);
            $msg = $diff > 0
                ? "Total persentase formula melebihi 100% (+{$diff}%). Total saat ini: {$totalPercentage}%."
                : "Total persentase formula kurang dari 100% ({$diff}%). Total saat ini: {$totalPercentage}%.";

            throw ValidationException::withMessages([
                'total_percentage' => $msg,
            ]);
        }

        $totalCostPerPack = $totalCostPerServing * $servingsPerPack;

        return [
            'total_percentage' => round($totalPercentage, 4),
            'total_cost_per_kg' => round($totalCostPerKg, 2),
            'total_cost_per_serving' => round($totalCostPerServing, 4),
            'total_cost_per_pack' => round($totalCostPerPack, 2),
            'items' => $processedItems,
        ];
    }
}
