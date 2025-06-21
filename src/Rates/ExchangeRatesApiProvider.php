<?php

namespace App\Rates;

class ExchangeRatesApiProvider implements ExchangeRateProviderInterface
{
    public function __construct(
        private readonly string $apiUrl,
    )
    {
    }

    public function getRate(string $currency): float
    {
        $response = file_get_contents($this->getApiUrl());
        $data = json_decode($response, true);

        return $data['rates'][$currency] ?? 0.0;
    }

    private function getApiUrl(): string
    {
        return $this->apiUrl;
    }
}
