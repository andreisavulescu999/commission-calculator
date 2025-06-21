<?php

namespace App\Service;

use App\Bin\BinProviderInterface;
use App\Dto\TransactionDTO;
use App\Rates\ExchangeRateProviderInterface;
use App\Utils\CountryChecker;
use App\Utils\CurrencyCodes;

class CommissionCalculator
{
    public function __construct(
        private readonly BinProviderInterface          $binProvider,
        private readonly ExchangeRateProviderInterface $exchangeRateProvider,
        private readonly float                         $rateEu,
        private readonly float                         $rateNotEu,
    )
    {
    }

    public function calculate(TransactionDTO $transaction): float
    {
        $countryCode = $this->binProvider->getCountryCode($transaction->getBin());
        $rate = $this->exchangeRateProvider->getRate($transaction->getCurrency());

        $amountInEur = ($transaction->getCurrency() === CurrencyCodes::DEFAULT_CURRENCY || $rate == 0.0) ? $transaction->getAmount() : $transaction->getAmount() / $rate;
        $commissionRate = CountryChecker::isEu($countryCode) ? $this->getRateEu() : $this->getRateNotEu();

        $commission = $amountInEur * $commissionRate;

        if (CountryChecker::isEu($countryCode)) {
            return round($commission, 2);
        }

        return ceil($commission * 100) / 100;
    }

    public function getRateEu(): float
    {
        return $this->rateEu;
    }

    public function getRateNotEu(): float
    {
        return $this->rateNotEu;
    }
}
