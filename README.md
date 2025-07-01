# Commission Task

A PHP application that calculates commission fees for financial operations (deposits and withdrawals) based on user type and predefined business rules.

## Requirements

- PHP 8.0 or higher
- BCMath extension
- Composer

## Installation

1. Clone the repository
2. Install dependencies:
```bash
composer install
```

## Usage

To process a CSV file with operations and calculate commission fees:

```bash
php script.php input.csv
```

### Input Format

The CSV file should contain one operation per line with the following format:
- operation date (Y-m-d format)
- user's identifier (number)
- user's type (`private` or `business`)
- operation type (`deposit` or `withdraw`)
- operation amount (numeric value)
- operation currency (`EUR`, `USD`, `JPY`)

Example:
```
2016-01-05,1,private,deposit,200.00,EUR
2016-01-06,2,business,withdraw,300.00,EUR
```

### Output

The application outputs one commission fee per line, calculated in the currency of the operation.

## Testing

To run all tests:

```bash
php vendor/phpunit/phpunit/phpunit
```

### Test Coverage

The application includes:
- Unit tests for mathematical operations
- Integration tests for commission calculation logic
- **One automation test** that processes the provided sample input and verifies the expected output

## Business Rules

### Commission Calculation

- Commission fees are calculated in the operation's currency
- Commission fees are rounded up to currency's decimal places
- EUR and USD: 2 decimal places
- JPY: 0 decimal places

### Deposit Rules

All deposits are charged **0.03%** of the deposit amount.

### Withdrawal Rules

#### Private Clients
- Commission fee: **0.3%** of withdrawn amount
- **Free withdrawal limits**:
  - €1000.00 per week (Monday to Sunday) is free of charge
  - Only for the first 3 withdrawal operations per week
  - 4th and subsequent operations use the standard 0.3% rate
  - If the free amount is exceeded, commission is calculated only for the exceeded amount

#### Business Clients
- Commission fee: **0.5%** of withdrawn amount

### Currency Conversion

For private client weekly limits, amounts are converted to EUR using the configured exchange rates:
- EUR:USD = 1:1.1497
- EUR:JPY = 1:129.53

## Architecture

The application follows SOLID principles and is designed for maintainability and extensibility:

- **Math Service**: Handles precise decimal calculations using BCMath
- **ExchangeRateService**: Manages currency conversions (easily configurable for new currencies)
- **CommissionCalculator**: Contains the core business logic for fee calculations
- **CsvParser**: Handles input file parsing and validation
- **Operation Model**: Represents individual transactions

### Adding New Features

- **New Currency**: Add exchange rate to `ExchangeRateService` configuration
- **New Commission Rules**: Extend `CommissionCalculator` class
- **New Input Formats**: Create new parser implementing similar interface

## File Structure

```
src/
├── Model/
│   └── Operation.php          # Operation data model
└── Service/
    ├── CommissionCalculator.php   # Core business logic
    ├── CsvParser.php              # CSV file parser
    ├── ExchangeRateService.php    # Currency conversion
    └── Math.php                   # Precise mathematical operations

tests/
└── Service/
    ├── CommissionCalculatorTest.php  # Main automation test
    └── MathTest.php                  # Math service tests

script.php                     # Main application entry point
input.csv                      # Sample input file
```

## Example

Given the sample input file:
```csv
2014-12-31,4,private,withdraw,1200.00,EUR
2015-01-01,4,private,withdraw,1000.00,EUR
2016-01-05,4,private,withdraw,1000.00,EUR
2016-01-05,1,private,deposit,200.00,EUR
2016-01-06,2,business,withdraw,300.00,EUR
2016-01-06,1,private,withdraw,30000,JPY
2016-01-07,1,private,withdraw,1000.00,EUR
2016-01-07,1,private,withdraw,100.00,USD
2016-01-10,1,private,withdraw,100.00,EUR
2016-01-10,2,business,deposit,10000.00,EUR
2016-01-10,3,private,withdraw,1000.00,EUR
2016-02-15,1,private,withdraw,300.00,EUR
2016-02-19,5,private,withdraw,3000000,JPY
```

Expected output:
```
0.60
3.00
0.00
0.06
1.50
0
0.70
0.30
0.30
3.00
0.00
0.00
8612
```

Thank you, Good luck! :) 
