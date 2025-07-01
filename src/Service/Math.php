<?php

declare(strict_types=1);

namespace Paysera\CommissionTask\Service;

class Math
{
    private int $scale;

    public function __construct(int $scale)
    {
        $this->scale = $scale;
    }

    public function add(string $leftOperand, string $rightOperand): string
    {
        return bcadd($leftOperand, $rightOperand, $this->scale);
    }

    public function subtract(string $leftOperand, string $rightOperand): string
    {
        return bcsub($leftOperand, $rightOperand, $this->scale);
    }

    public function multiply(string $leftOperand, string $rightOperand): string
    {
        return bcmul($leftOperand, $rightOperand, $this->scale);
    }

    public function divide(string $leftOperand, string $rightOperand): string
    {
        return bcdiv($leftOperand, $rightOperand, $this->scale);
    }

    public function compare(string $leftOperand, string $rightOperand): int
    {
        return bccomp($leftOperand, $rightOperand, $this->scale);
    }

    public function isGreaterThan(string $leftOperand, string $rightOperand): bool
    {
        return $this->compare($leftOperand, $rightOperand) === 1;
    }

    public function isGreaterOrEqualThan(string $leftOperand, string $rightOperand): bool
    {
        return $this->compare($leftOperand, $rightOperand) >= 0;
    }

    public function isLessOrEqualThan(string $leftOperand, string $rightOperand): bool
    {
        return $this->compare($leftOperand, $rightOperand) <= 0;
    }

    public function roundUp(string $value, int $precision): string
    {
        $multiplier = bcpow('10', (string)$precision, 0);
        $multipliedValue = bcmul($value, $multiplier, $this->scale);
        
        // Get the integer part
        $integerPart = $this->getIntegerPart($multipliedValue);
        
        // If there's any decimal part, round up
        if (bccomp($multipliedValue, $integerPart, $this->scale) > 0) {
            $integerPart = bcadd($integerPart, '1', 0);
        }
        
        return bcdiv($integerPart, $multiplier, $precision);
    }

    private function getIntegerPart(string $value): string
    {
        $pos = strpos($value, '.');
        if ($pos === false) {
            return $value;
        }
        return substr($value, 0, $pos);
    }
}
