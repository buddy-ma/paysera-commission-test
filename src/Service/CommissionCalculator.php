<?php

declare(strict_types=1);

namespace Paysera\CommissionTask\Service;

use Paysera\CommissionTask\Model\Operation;

class CommissionCalculator
{
    private const DEPOSIT_COMMISSION_RATE = '0.0003'; // 0.03%
    private const PRIVATE_WITHDRAW_COMMISSION_RATE = '0.003'; // 0.3%
    private const BUSINESS_WITHDRAW_COMMISSION_RATE = '0.005'; // 0.5%
    private const PRIVATE_FREE_AMOUNT_PER_WEEK = '1000.00'; // EUR
    private const PRIVATE_FREE_OPERATIONS_PER_WEEK = 3;

    private Math $math;
    private ExchangeRateService $exchangeRateService;
    private array $userWeeklyHistory = [];

    public function __construct(Math $math, ExchangeRateService $exchangeRateService)
    {
        $this->math = $math;
        $this->exchangeRateService = $exchangeRateService;
    }

    public function calculateCommission(Operation $operation): string
    {
        if ($operation->isDeposit()) {
            return $this->calculateDepositCommission($operation);
        }

        if ($operation->isWithdraw()) {
            return $this->calculateWithdrawCommission($operation);
        }

        throw new \InvalidArgumentException('Unknown operation type');
    }

    private function calculateDepositCommission(Operation $operation): string
    {
        $commission = $this->math->multiply($operation->getAmount(), self::DEPOSIT_COMMISSION_RATE);
        $precision = $this->exchangeRateService->getCurrencyPrecision($operation->getCurrency());
        
        return $this->math->roundUp($commission, $precision);
    }

    private function calculateWithdrawCommission(Operation $operation): string
    {
        if ($operation->isBusiness()) {
            return $this->calculateBusinessWithdrawCommission($operation);
        }

        if ($operation->isPrivate()) {
            return $this->calculatePrivateWithdrawCommission($operation);
        }

        throw new \InvalidArgumentException('Unknown user type');
    }

    private function calculateBusinessWithdrawCommission(Operation $operation): string
    {
        $commission = $this->math->multiply($operation->getAmount(), self::BUSINESS_WITHDRAW_COMMISSION_RATE);
        $precision = $this->exchangeRateService->getCurrencyPrecision($operation->getCurrency());
        
        return $this->math->roundUp($commission, $precision);
    }

    private function calculatePrivateWithdrawCommission(Operation $operation): string
    {
        $weekKey = $this->getWeekKey($operation);
        
        if (!isset($this->userWeeklyHistory[$weekKey])) {
            $this->userWeeklyHistory[$weekKey] = [
                'operationCount' => 0,
                'totalAmountEur' => '0.00'
            ];
        }

        // Convert operation amount to EUR for weekly limit calculation
        $amountInEur = $this->exchangeRateService->convertToEur(
            $operation->getAmount(),
            $operation->getCurrency()
        );

        $currentWeekData = &$this->userWeeklyHistory[$weekKey];
        $currentWeekData['operationCount']++;

        // Check if this operation is within free limits
        if ($currentWeekData['operationCount'] <= self::PRIVATE_FREE_OPERATIONS_PER_WEEK) {
            $remainingFreeAmount = $this->math->subtract(
                self::PRIVATE_FREE_AMOUNT_PER_WEEK,
                $currentWeekData['totalAmountEur']
            );

            if ($this->math->isGreaterThan($remainingFreeAmount, '0')) {
                if ($this->math->isLessOrEqualThan($amountInEur, $remainingFreeAmount)) {
                    // Entire amount is free
                    $currentWeekData['totalAmountEur'] = $this->math->add(
                        $currentWeekData['totalAmountEur'],
                        $amountInEur
                    );
                    return '0';
                } else {
                    // Partial amount is free, calculate commission for the excess
                    $excessAmountEur = $this->math->subtract($amountInEur, $remainingFreeAmount);
                    $excessAmountInOriginalCurrency = $this->exchangeRateService->convertFromEur(
                        $excessAmountEur,
                        $operation->getCurrency()
                    );
                    
                    $commission = $this->math->multiply(
                        $excessAmountInOriginalCurrency,
                        self::PRIVATE_WITHDRAW_COMMISSION_RATE
                    );
                    
                    $currentWeekData['totalAmountEur'] = self::PRIVATE_FREE_AMOUNT_PER_WEEK;
                    
                    $precision = $this->exchangeRateService->getCurrencyPrecision($operation->getCurrency());
                    return $this->math->roundUp($commission, $precision);
                }
            }
        }

        // No free amount left or exceeded free operations limit
        $currentWeekData['totalAmountEur'] = $this->math->add(
            $currentWeekData['totalAmountEur'],
            $amountInEur
        );

        $commission = $this->math->multiply($operation->getAmount(), self::PRIVATE_WITHDRAW_COMMISSION_RATE);
        $precision = $this->exchangeRateService->getCurrencyPrecision($operation->getCurrency());
        
        return $this->math->roundUp($commission, $precision);
    }

    private function getWeekKey(Operation $operation): string
    {
        return sprintf(
            '%d_%d_%d',
            $operation->getUserId(),
            $operation->getYear(),
            $operation->getWeekNumber()
        );
    }
} 