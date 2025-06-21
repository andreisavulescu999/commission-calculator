<?php

namespace App\Utils;

class CountryChecker
{
    public static function isEu(string $countryCode): bool
    {
        return in_array($countryCode, CountryCodes::EU_COUNTRIES, true);
    }
}