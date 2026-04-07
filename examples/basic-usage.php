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
// Note: Tax, UnitNetPrice and UnitTaxValue are optional.
// If omitted, LeaseLink defaults Tax to "23" and calculates the rest.
$items = [
    new CalculationItem(
        name: 'Test Product',
        quantity: 1,
        categoryLevel1: 'Electronics',
        unitGrossPrice: 1230.00
    )
];

// Create options
// New optional params: disableProcess, continueUrl, returnUrl
$options = new CalculationOptions(
    email: 'test@example.com',
    // disableProcess: true,   // Preview/simulation mode — application cannot be submitted
    // continueUrl: 'https://example.com/thank-you', // Redirect after signing contract
    // returnUrl: 'https://example.com/rejected',    // Redirect after rejection
);

try {
    $result = $leaselink->createCalculation($items, $options);

    echo "Calculation created successfully!\n";
    echo "ID: {$result->getCalculationId()}\n";
    echo "URL: {$result->getCalculationAbsoluteUrl()}\n";
    // Use getCalculationAbsoluteUrlWithOffer() to pre-select a specific offer:
    // echo "URL with offer: {$result->getCalculationAbsoluteUrlWithOffer('1420343451')}\n";
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
