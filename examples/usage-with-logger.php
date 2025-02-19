<?php
require __DIR__ . '/../vendor/autoload.php';

use LeaseLink\Config\LeaseLinkConfig;
use LeaseLink\Service\LeaseLinkApiClient;
use LeaseLink\Service\FileLogger;
use LeaseLink\LeaseLinkLib;
use LeaseLink\Model\CalculationItem;

// Debug console logger
class ConsoleLogger extends \Psr\Log\AbstractLogger
{
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        echo "[" . strtoupper($level) . "] " . $message . "\n";
        if (!empty($context)) {
            print_r($context);
        }
    }
}

// Configuration with logging enabled
$config = new LeaseLinkConfig(
    apiKey: 'your-api-key-here',
    isTest: true,
    debug: true,
    logFile: __DIR__ . '/../logs/leaselink.log',
    logLevel: 'debug' // Set minimum log level
);

// Create multiple loggers
$consoleLogger = new ConsoleLogger();
$fileLogger = new FileLogger(
    $config->getLogFile(),
    $config->isDebug(),
    $config->getLogLevel()->value
);

// Combine loggers
class CombinedLogger extends \Psr\Log\AbstractLogger
{
    public function __construct(private readonly array $loggers) {}

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        foreach ($this->loggers as $logger) {
            $logger->log($level, $message, $context);
        }
    }
}

// Initialize with combined logger
$logger = new CombinedLogger([$consoleLogger, $fileLogger]);
$client = new LeaseLinkApiClient($config, $logger);
$leaselink = new LeaseLinkLib($config, $client);

// Test different log levels
$logger->debug('Debug message');
$logger->info('Info message');
$logger->warning('Warning message');
$logger->error('Error message');

// Create sample calculation
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

try {
    $result = $leaselink->createCalculation($items);
    echo "\nCalculation URL: " . $result->getCalculationUrl() . "\n";
} catch (\LeaseLink\Exception\LeaseLinkApiException $e) {
    echo "\nError occurred:\n";
    print_r($e->getErrors());
}

echo "\nCheck 'leaselink.log' for detailed logs.\n";
