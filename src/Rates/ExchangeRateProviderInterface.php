<?php

namespace App\Rates;

interface ExchangeRateProviderInterface
{
    public function getRate(string $currency): float;
}