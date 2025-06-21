<?php

namespace App\Tests;

use App\Bin\BinProviderInterface;
use App\Dto\TransactionDTO;
use App\Rates\ExchangeRateProviderInterface;
use App\Service\CommissionCalculator;
use App\Utils\CountryCodes;
use App\Utils\CurrencyCodes;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;

class CommissionCalculatorTest extends TestCase
{
    private float $rateEu;
    private float $rateNotEu;

    /**
     * @throws Exception
     */
    public function testCommissionForEuCountry(): void
    {
        $binMock = $this->createMock(BinProviderInterface::class);
        $binMock->method('getCountryCode')->willReturn(CountryCodes::DE);

        $rateMock = $this->createMock(ExchangeRateProviderInterface::class);
        $rateMock->method('getRate')->willReturn(1.0);

        $calculator = new CommissionCalculator($binMock, $rateMock, 0.01, 0.02);

        $transaction = (new TransactionDTO())
            ->setBin('123456')
            ->setAmount(100.00)
            ->setCurrency(CurrencyCodes::DEFAULT_CURRENCY);
        $result = $calculator->calculate($transaction);

        $this->assertEquals(1.00, $result);
    }

    /**
     * @throws Exception
     */
    public function testNonEuCommissionIsCelled(): void
    {
        $binMock = $this->createMock(BinProviderInterface::class);
        $binMock->method('getCountryCode')->willReturn(CountryCodes::US);

        $rateMock = $this->createMock(ExchangeRateProviderInterface::class);
        $rateMock->method('getRate')->willReturn(0.98);

        $calculator = new CommissionCalculator($binMock, $rateMock, $this->getRateEu(), $this->getRateNotEu());

        $transaction = (new TransactionDTO())
            ->setBin('987654')
            ->setAmount(50.00)
            ->setCurrency(CurrencyCodes::USD);
        $result = $calculator->calculate($transaction);

        $this->assertEqualsWithDelta(0.51, $result, 0.05, '');
    }

    public function getRateEu(): float
    {
        return $this->rateEu;
    }

    public function getRateNotEu(): float
    {
        return $this->rateNotEu;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $dotenv = new Dotenv();
        $dotenv->load(dirname(__DIR__) . '/.env');

        $this->rateEu = $_ENV['RATE_EU'];
        $this->rateNotEu = $_ENV['RATE_NOT_EU'];
    }
}
