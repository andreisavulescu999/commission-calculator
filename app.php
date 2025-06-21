<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Bin\BinListProvider;
use App\Dto\TransactionDTO;
use App\Rates\ExchangeRatesApiProvider;
use App\Service\CommissionCalculator;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

$dotenv = new Dotenv();
$dotenv->load(dirname(__DIR__) . '/.env');

// Validate required env variables
if (empty($_ENV['BIN_LOOKUP_URL']) || empty($_ENV['EXCHANGE_RATE_API_URL']) || empty($_ENV['RATE_EU']) || empty($_ENV['RATE_NOT_EU'])) {
    echo "Environment variables are missing or not loaded properly.\n";
    exit(1);
}

// Handle file argument
$relativeInput = $argv[1] ?? null;

if (!$relativeInput) {
    echo "Missing input file.\nUsage: php app.php data/input.txt\n";
    exit(1);
}

// Normalize path and resolve absolute location
$inputPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . $relativeInput);
if (!$inputPath || !file_exists($inputPath)) {
    echo "File not found: {$relativeInput}\n";
    exit(1);
}

// Serializer setup
$serializer = new Serializer(
    [new ObjectNormalizer()],
    [new JsonEncoder()]
);

// Initialize the calculator with dependencies
$binProvider = new BinListProvider($_ENV['BIN_LOOKUP_URL']);
$exchangeProvider = new ExchangeRatesApiProvider($_ENV['EXCHANGE_RATE_API_URL']);

$calculator = new CommissionCalculator($binProvider, $exchangeProvider, (float)$_ENV['RATE_EU'], (float)$_ENV['RATE_NOT_EU']);

// Process the transactions
$lines = file($inputPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {
    try {
        /** @var TransactionDTO $transaction */
        $transaction = $serializer->deserialize($line, TransactionDTO::class, 'json');
        $commission = $calculator->calculate($transaction);
        echo $commission . PHP_EOL;
    } catch (ExceptionInterface $e) {
        echo "Error parsing transaction: {$e->getMessage()}\n";
    }
}
