<?php

declare(strict_types=1);

namespace Paysera\CommissionTask\Service;

use Paysera\CommissionTask\Model\Operation;

class CsvParser
{
    public function parseFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File {$filePath} does not exist");
        }

        $operations = [];
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            throw new \RuntimeException("Cannot open file {$filePath}");
        }

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) !== 6) {
                throw new \InvalidArgumentException('Invalid CSV format. Expected 6 columns per line.');
            }

            $operations[] = $this->createOperationFromCsvData($data);
        }

        fclose($handle);

        return $operations;
    }

    private function createOperationFromCsvData(array $data): Operation
    {
        [$dateString, $userIdString, $userType, $operationType, $amountString, $currency] = array_map('trim', $data);

        $date = \DateTime::createFromFormat('Y-m-d', $dateString);
        if ($date === false) {
            throw new \InvalidArgumentException("Invalid date format: {$dateString}");
        }

        $userId = (int) $userIdString;
        if ($userId <= 0) {
            throw new \InvalidArgumentException("Invalid user ID: {$userIdString}");
        }

        if (!in_array($userType, ['private', 'business'], true)) {
            throw new \InvalidArgumentException("Invalid user type: {$userType}");
        }

        if (!in_array($operationType, ['deposit', 'withdraw'], true)) {
            throw new \InvalidArgumentException("Invalid operation type: {$operationType}");
        }

        if (!is_numeric($amountString) || (float) $amountString <= 0) {
            throw new \InvalidArgumentException("Invalid amount: {$amountString}");
        }

        if (!in_array($currency, ['EUR', 'USD', 'JPY'], true)) {
            throw new \InvalidArgumentException("Invalid currency: {$currency}");
        }

        return new Operation(
            $date,
            $userId,
            $userType,
            $operationType,
            $amountString,
            $currency
        );
    }
} 