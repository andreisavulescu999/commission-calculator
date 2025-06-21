<?php

namespace App\Bin;

interface BinProviderInterface
{
    public function getCountryCode(string $bin): string;
}