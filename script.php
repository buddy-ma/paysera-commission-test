<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use Paysera\CommissionTask\Service\Math;
use Paysera\CommissionTask\Service\ExchangeRateService;
use Paysera\CommissionTask\Service\CommissionCalculator;
use Paysera\CommissionTask\Service\CsvParser;

try {
    if ($argc !== 2) {
        throw new \InvalidArgumentException('Usage: php script.php <input_file.csv>');
    }

    $inputFile = $argv[1];

    // Initialize services with exchange rates from the example
    $math = new Math(10); // Use high precision for calculations
    $exchangeRates = [
        'USD' => '1.1497',
        'JPY' => '129.53'
    ];
    $exchangeRateService = new ExchangeRateService($exchangeRates, $math);
    $commissionCalculator = new CommissionCalculator($math, $exchangeRateService);
    $csvParser = new CsvParser();

    // Parse operations from CSV
    $operations = $csvParser->parseFile($inputFile);

    // Calculate and output commissions
    foreach ($operations as $operation) {
        $commission = $commissionCalculator->calculateCommission($operation);
        
        // Format output according to currency precision
        $precision = $exchangeRateService->getCurrencyPrecision($operation->getCurrency());
        if ($precision === 0) {
            echo (int)$commission . PHP_EOL;
        } else {
            // Format with proper decimal places
            echo number_format((float)$commission, $precision, '.', '') . PHP_EOL;
        }
    }

} catch (\Exception $e) {
    fprintf(STDERR, "Error: %s\n", $e->getMessage());
    exit(1);
} 