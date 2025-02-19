<?php
require __DIR__ . '/../vendor/autoload.php';

use LeaseLink\Config\LeaseLinkConfig;
use LeaseLink\Service\LeaseLinkApiClient;
use LeaseLink\LeaseLinkLib;
use LeaseLink\Model\CalculationItem;
use LeaseLink\Config\CalculationOptions;
use LeaseLink\Service\FileLogger;

// Configuration
$config = new LeaseLinkConfig(
    apiKey: 'your-api-key-here',
    isTest: true,
    debug: true,
    logFile: __DIR__ . '/../logs/leaselink.log'
);

// Setup logger
$logger = new FileLogger($config->getLogFile(), $config->isDebug());
$client = new LeaseLinkApiClient($config, $logger);
$leaselink = new LeaseLinkLib($config, $client);

try {
    // First, create a calculation
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

    $options = new CalculationOptions(
        multiOffer: true,
        email: 'test@example.com'
    );

    // Get calculation result
    $calculationResult = $leaselink->createCalculation($items, $options);
    
    echo "Calculation created:\n";
    echo "ID: {$calculationResult->getCalculationId()}\n";
    echo "Available offers:\n";
    
    // Display all available offers
    foreach ($calculationResult->getOffers() as $index => $offer) {
        echo sprintf(
            "%d. %s: %s PLN x %d months (Package ID: %s)\n",
            $index + 1,
            $offer['financialProductType'],
            $offer['grossMonthlyInstallment'],
            $offer['numberOfInstallments'],
            $offer['calculationPackageId']
        );
    }

    // Choose first offer
    $chosenOffer = $calculationResult->getOffers()[0];
    
    // Save chosen offer
    $savedOffer = $leaselink->saveChosenOffer(
        $calculationResult->getCalculationId(),
        $chosenOffer['calculationPackageId']
    );

    echo "\nOffer saved successfully!\n";
    echo "Redirect URL: {$savedOffer->getRedirectUrl()}\n";
    
} catch (\LeaseLink\Exception\LeaseLinkApiException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    print_r($e->getErrors());
}
