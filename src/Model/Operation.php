<?php

declare(strict_types=1);

namespace Paysera\CommissionTask\Model;

class Operation
{
    private \DateTime $date;
    private int $userId;
    private string $userType;
    private string $operationType;
    private string $amount;
    private string $currency;

    public function __construct(
        \DateTime $date,
        int $userId,
        string $userType,
        string $operationType,
        string $amount,
        string $currency
    ) {
        $this->date = $date;
        $this->userId = $userId;
        $this->userType = $userType;
        $this->operationType = $operationType;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function getOperationType(): string
    {
        return $this->operationType;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function isPrivate(): bool
    {
        return $this->userType === 'private';
    }

    public function isBusiness(): bool
    {
        return $this->userType === 'business';
    }

    public function isDeposit(): bool
    {
        return $this->operationType === 'deposit';
    }

    public function isWithdraw(): bool
    {
        return $this->operationType === 'withdraw';
    }

    public function getWeekNumber(): int
    {
        return (int)$this->date->format('W');
    }

    public function getYear(): int
    {
        return (int)$this->date->format('o'); // ISO year
    }
} 