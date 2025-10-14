<?php
namespace App\Support;

class Pricing
{
    public static function applyMarkup(float $baseAmount): float
    {
        $percent = (float) Config::get('catalog.markup_percent', 0);
        $fixed = (float) Config::get('catalog.markup_fixed', 0);

        $price = $baseAmount;

        if ($percent !== 0.0) {
            $price += $baseAmount * ($percent / 100);
        }

        if ($fixed !== 0.0) {
            $price += $fixed;
        }

        return round($price, 2);
    }
}
