<?php
require __DIR__ . '/../vendor/autoload.php';

use LeaseLink\Config\LeaseLinkConfig;
use LeaseLink\Service\LeaseLinkApiClient;
use LeaseLink\LeaseLinkLib;
use LeaseLink\Model\CalculationItem;
use LeaseLink\Config\CalculationOptions;

// Basic configuration
$config = new LeaseLinkConfig(
    apiKey: 'your-api-key-here',
    isTest: true
);

// Initialize client and library
$client = new LeaseLinkApiClient($config);
$leaselink = new LeaseLinkLib($config, $client);

// Create items
$items = [
    new CalculationItem(
        name: 'Test Product',
        quantity: 1,
        categoryLevel1: 'Electronics',
        unitNetPrice: 1000.00,
        unitGrossPrice: 1230.00,
        tax: '23',
        unitTaxValue: 230.00
    )
];

// Create options
$options = new CalculationOptions(
    email: 'test@example.com'
);

try {
    $result = $leaselink->createCalculation($items, $options);
    
    echo "Calculation created successfully!\n";
    echo "ID: {$result->getCalculationId()}\n";
    echo "URL: {$result->getCalculationUrl()}\n";
    echo "\nAvailable offers:\n";
    
    foreach ($result->getOffers() as $offer) {
        echo sprintf(
            "- %s: %s PLN x %d months\n",
            $offer['financialProductType'],
            $offer['grossMonthlyInstallment'],
            $offer['numberOfInstallments']
        );
    }
} catch (\LeaseLink\Exception\LeaseLinkApiException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
