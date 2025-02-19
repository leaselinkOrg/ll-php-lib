# LeaseLink PHP Library

A PHP library for integrating with the LeaseLink API. This library provides a simple way to create lease calculations and handle responses from the LeaseLink service.

## Requirements

- PHP 8.1 or higher
- Composer
- cURL extension
- JSON extension

## Installation

For stable version:
```bash
composer require leaselinkorg/ll-php-lib
```

For development version:
```bash
composer require leaselinkorg/ll-php-lib:dev-develop
```

## Configuration

First, create a configuration object with your API credentials:

```php
use LeaseLink\Config\LeaseLinkConfig;

$config = new LeaseLinkConfig(
    apiKey: 'your-api-key',
    isTest: true, // Use true for test environment
    debug: true,  // Enable debug logging
    logFile: 'path/to/leaselink.log'
);
```

## Basic Usage

Here's a basic example of creating a calculation:

```php
use LeaseLink\LeaseLinkLib;
use LeaseLink\Service\LeaseLinkApiClient;
use LeaseLink\Model\CalculationItem;
use LeaseLink\Config\CalculationOptions;
use LeaseLink\Service\FileLogger;

// Initialize the client
$logger = new FileLogger('path/to/leaselink.log', true);
$client = new LeaseLinkApiClient($config, $logger);
$leaselink = new LeaseLinkLib($config, $client);

// Create calculation items
$items = [
    new CalculationItem(
        name: 'Laptop Dell XPS 13',
        quantity: 1,
        categoryLevel1: 'Electronics',
        unitNetPrice: 4065.04,
        unitGrossPrice: 5000.00,
        tax: '23',
        unitTaxValue: 934.96,
        categoryLevel2: 'Computers',
        categoryLevel3: 'Laptops',
        itemId: 'DELL-XPS-13'
    )
];

// Set calculation options
$options = new CalculationOptions(
    multiOffer: false,
    email: 'customer@example.com',
    taxId: '1234567890',
    externalOrderId: 'ORDER-123'
);

// Create calculation
try {
    $result = $leaselink->createCalculation($items, $options);
    
    // Access calculation results
    echo "Calculation ID: " . $result->getCalculationId() . "\n";
    echo "Calculation URL: " . $result->getCalculationUrl() . "\n";
    
    // Process offers
    foreach ($result->getOffers() as $offer) {
        echo "Offer: {$offer['partnerName']}\n";
        echo "Installments: {$offer['installmentAmount']} x {$offer['numberOfInstallments']}\n";
    }
} catch (LeaseLinkApiException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    print_r($e->getErrors());
}
```

## Save Chosen Offer

After creating a calculation, you can get redirect url the chosen offer:

```php
// First create calculation
$result = $leaselink->createCalculation($items, $options);

// Get first offer from results
$offer = $result->getOffers()[0];

// Save chosen offer
$savedOffer = $leaselink->saveChosenOffer(
    $result->getCalculationId(),
    $offer['calculationPackageId']
);

// Get redirect URL for customer
$redirectUrl = $savedOffer->getRedirectUrl();
```

See `examples/save-chosen-offer.php` for a complete example.

## Available Classes

### LeaseLinkConfig
Configuration class for API settings:
- `apiKey` - Your LeaseLink API key
- `isTest` - Boolean flag for test environment
- `debug` - Enable debug logging
- `logFile` - Path to log file

### CalculationItem
Represents a single item in the calculation:
- `name` - Product name
- `quantity` - Number of items
- `categoryLevel1` - Main category
- `unitNetPrice` - Net price per unit
- `unitGrossPrice` - Gross price per unit
- `tax` - Tax rate (allowed: 'ZW', '0', '5', '8', '23')
- `unitTaxValue` - Tax value per unit

### CalculationOptions
Additional options for calculation:
- `multiOffer` - Show all available financing options
- `email` - Customer email
- `phone` - Customer phone
- `taxId` - Customer tax ID
- `externalOrderId` - Your order reference
- `isCartReadOnly` - Lock cart modifications

## Error Handling

The library uses `LeaseLinkApiException` for error handling. You can access detailed error information:

```php
try {
    $result = $leaselink->createCalculation($items, $options);
} catch (LeaseLinkApiException $e) {
    echo $e->getMessage();    // Formatted error message
    print_r($e->getErrors()); // Detailed error array
}
```

## Logging

The library supports PSR-3 compatible loggers. You can use the built-in FileLogger or implement your own:

```php
$logger = new FileLogger('path/to/leaselink.log', true);
$client = new LeaseLinkApiClient($config, $logger);
```

## Webhook Notifications

The library supports handling webhook notifications from LeaseLink:

```php
try {
    $rawData = json_decode(file_get_contents('php://input'), true);
    $notification = $leaselink->handleNotification($rawData);
    
    switch ($notification->getStatus()) {
        case NotificationStatus::PROCESSING:
            // Handle processing status
            break;
        case NotificationStatus::ACCEPTED:
            // Handle accepted status
            break;
        // ... handle other statuses
    }
} catch (LeaseLinkApiException $e) {
    // Handle error
}
```

Available notification statuses:
- `PROCESSING` (0) - Order is being processed
- `CANCELLED` (-1) - Order was cancelled
- `ACCEPTED` (2) - Order has been accepted
- `SEND_ASSET` (3) - Asset can be sent
- `SIGN_CONTRACT` (4) - Contract ready for signing

See `examples/webhook-notification.php` for a complete example.

## Examples

Check the `examples` directory for working examples:

- `basic-usage.php`: Simple configuration and calculation creation
- `usage-with-logger.php`: Advanced usage with logging configuration
- `webhook-notification.php`: Handling webhook notifications from LeaseLink
- `save-chosen-offer.php`: Getting the redirect URL for the chosen offer

To run the examples:

```bash
php examples/basic-usage.php
php examples/usage-with-logger.php
php examples/webhook-notification.php
php examples/save-chosen-offer.php
```

Remember to update the API key in the examples before running them.

## License

GPL-3.0 license

## Support

For support, please contact e-mail: integracje@leaselink.pl
