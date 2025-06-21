<?php

namespace App\Bin;

class BinListProvider implements BinProviderInterface
{
    public function __construct(
        private readonly string $binUrl = '',
    )
    {
    }

    public function getCountryCode(string $bin): string
    {
        $url = $this->generateUrl($bin);
        $response = @file_get_contents($url);

        if ($response === false) {
            return 'XX';
        }
        $data = json_decode($response);

        return $data->country->alpha2 ?? 'XX';
    }

    private function generateUrl(string $bin): string
    {
        return "{$this->getBinUrl()}/{$bin}";
    }

    public function getBinUrl(): string
    {
        return $this->binUrl;
    }
}