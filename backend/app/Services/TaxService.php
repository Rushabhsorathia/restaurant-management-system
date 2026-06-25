<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Restaurant;
use App\Models\TaxConfig;

class TaxService
{
    /**
     * Resolve the applicable tax config for a bill where the restaurant
     * and outlet may be in different states.
     *
     * Inter-state (different state codes) -> IGST.
     * Intra-state (same state) -> CGST + SGST.
     */
    public function resolve(Restaurant $restaurant, ?string $outletStateCode): ?TaxConfig
    {
        $configs = TaxConfig::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->get();

        if ($configs->isEmpty()) {
            return null;
        }

        $restaurantState = $this->stateCode($restaurant->state);
        $isInterState = $outletStateCode !== null
            && $outletStateCode !== ''
            && $restaurantState !== $outletStateCode;

        $type = $isInterState ? 'inter_state' : 'intra_state';

        return $configs->where('type', $type)->first()
            ?? $configs->first();
    }

    /**
     * Compute the tax split for an amount given a tax config.
     *
     * Returns cgst, sgst, igst, cess amounts.
     */
    public function split(TaxConfig $config, float $taxableAmount): array
    {
        $isInter = $config->type === 'inter_state';

        return [
            'cgst' => $isInter ? 0.0 : $this->round($taxableAmount * ((float) $config->cgst_rate / 100)),
            'sgst' => $isInter ? 0.0 : $this->round($taxableAmount * ((float) $config->sgst_rate / 100)),
            'igst' => $isInter ? $this->round($taxableAmount * ((float) $config->igst_rate / 100)) : 0.0,
            'cess' => $this->round($taxableAmount * ((float) $config->cess_rate / 100)),
        ];
    }

    protected function stateCode(?string $state): ?string
    {
        // Best-effort: GSTIN state code is a 2-digit prefix. For simplicity
        // here we treat the state name as the comparison key; the GSTIN
        // state-code mapping is handled by the caller if needed.
        return $state ? mb_strtolower(trim($state)) : null;
    }

    protected function round(float $value): float
    {
        return round($value, 2);
    }
}