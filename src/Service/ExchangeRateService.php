<?php

declare(strict_types=1);

namespace Paysera\CommissionTask\Service;

class ExchangeRateService
{
    private array $rates;
    private Math $math;

    public function __construct(array $rates, Math $math)
    {
        $this->rates = $rates;
        $this->math = $math;
    }

    public function convertToEur(string $amount, string $fromCurrency): string
    {
        if ($fromCurrency === 'EUR') {
            return $amount;
        }

        if (!isset($this->rates[$fromCurrency])) {
            throw new \InvalidArgumentException("Exchange rate for currency {$fromCurrency} not found");
        }

        return $this->math->divide($amount, $this->rates[$fromCurrency]);
    }

    public function convertFromEur(string $amount, string $toCurrency): string
    {
        if ($toCurrency === 'EUR') {
            return $amount;
        }

        if (!isset($this->rates[$toCurrency])) {
            throw new \InvalidArgumentException("Exchange rate for currency {$toCurrency} not found");
        }

        return $this->math->multiply($amount, $this->rates[$toCurrency]);
    }

    public function getCurrencyPrecision(string $currency): int
    {
        $precisions = [
            'EUR' => 2,
            'USD' => 2,
            'JPY' => 0,
        ];

        return $precisions[$currency] ?? 2;
    }
} 