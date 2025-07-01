<?php

declare(strict_types=1);

namespace Paysera\CommissionTask\Tests\Service;

use PHPUnit\Framework\TestCase;
use Paysera\CommissionTask\Service\Math;
use Paysera\CommissionTask\Service\ExchangeRateService;
use Paysera\CommissionTask\Service\CommissionCalculator;
use Paysera\CommissionTask\Service\CsvParser;

class CommissionCalculatorTest extends TestCase
{
    private Math $math;
    private ExchangeRateService $exchangeRateService;
    private CommissionCalculator $commissionCalculator;
    private CsvParser $csvParser;

    public function setUp(): void
    {
        $this->math = new Math(10);
        $exchangeRates = [
            'USD' => '1.1497',
            'JPY' => '129.53'
        ];
        $this->exchangeRateService = new ExchangeRateService($exchangeRates, $this->math);
        $this->commissionCalculator = new CommissionCalculator($this->math, $this->exchangeRateService);
        $this->csvParser = new CsvParser();
    }

    /**
     * Test the complete system with the provided input data and verify the expected output
     */
    public function testCompleteSystemWithProvidedInput(): void
    {
        $inputCsv = __DIR__ . '/../../input.csv';
        
        // Expected output from the task specification
        $expectedOutput = [
            '0.60',
            '3.00',
            '0.00',
            '0.06',
            '1.50',
            '0',
            '0.70',
            '0.30',
            '0.30',
            '3.00',
            '0.00',
            '0.00',
            '8612'
        ];

        $operations = $this->csvParser->parseFile($inputCsv);
        $actualOutput = [];

        foreach ($operations as $operation) {
            $commission = $this->commissionCalculator->calculateCommission($operation);
            
            // Format output according to currency precision
            $precision = $this->exchangeRateService->getCurrencyPrecision($operation->getCurrency());
            if ($precision === 0) {
                $actualOutput[] = (string)(int)$commission;
            } else {
                $actualOutput[] = number_format((float)$commission, $precision, '.', '');
            }
        }

        $this->assertEquals($expectedOutput, $actualOutput);
    }

    /**
     * Test deposit commission calculation
     */
    public function testDepositCommission(): void
    {
        $operation = new \Paysera\CommissionTask\Model\Operation(
            new \DateTime('2016-01-05'),
            1,
            'private',
            'deposit',
            '200.00',
            'EUR'
        );

        $commission = $this->commissionCalculator->calculateCommission($operation);
        $this->assertEquals('0.06', $commission);
    }

    /**
     * Test business withdraw commission
     */
    public function testBusinessWithdrawCommission(): void
    {
        $operation = new \Paysera\CommissionTask\Model\Operation(
            new \DateTime('2016-01-06'),
            2,
            'business',
            'withdraw',
            '300.00',
            'EUR'
        );

        $commission = $this->commissionCalculator->calculateCommission($operation);
        $this->assertEquals('1.50', $commission);
    }

    /**
     * Test private withdraw with free amount
     */
    public function testPrivateWithdrawWithFreeAmount(): void
    {
        $operation = new \Paysera\CommissionTask\Model\Operation(
            new \DateTime('2016-01-05'),
            4,
            'private',
            'withdraw',
            '1000.00',
            'EUR'
        );

        $commission = $this->commissionCalculator->calculateCommission($operation);
        $this->assertEquals('0', $commission);
    }
} 