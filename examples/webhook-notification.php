<?php
require __DIR__ . '/../vendor/autoload.php';

use LeaseLink\Config\LeaseLinkConfig;
use LeaseLink\Service\LeaseLinkApiClient;
use LeaseLink\Service\FileLogger;
use LeaseLink\LeaseLinkLib;
use LeaseLink\Enum\NotificationStatus;
use LeaseLink\Exception\LeaseLinkApiException;

// Configuration
$config = new LeaseLinkConfig(
    apiKey: 'your-api-key-here',
    isTest: true,
    debug: true,
    logFile: __DIR__ . '/../logs/webhook.log'
);

// Setup logging
$logger = new FileLogger($config->getLogFile(), $config->isDebug());
$client = new LeaseLinkApiClient($config, $logger);
$leaselink = new LeaseLinkLib($config, $client);

// Simulate incoming webhook data (in production, use php://input)
$rawData = [
    "Status" => 4,
    "StatusName" => "SIGN_CONTRACT",
    "TransactionId" => "30d9baf8b4564e6db4d16b615102ce87",
    "InvoiceVatCompanyName" => "LeaseLink Sp. z o.o.",
    "InvoiceVatIdentificationNumber" => "5272698282",
    "InvoiceVatAddressCity" => "Warszawa",
    "InvoiceVatAddressZipCode" => "03-840",
    "InvoiceVatAddressStreetName" => "ul. Grochowska",
    "InvoiceVatAddressStreetNumber" => "306/308",
    "InvoiceVatAddressLocationNumber" => "",
    "PartnerId" => "integracje",
    "CustomerExternalDocument" => "ORDER-123",
    "FinancialProductType" => "OperationalLeasing",
    "ContractGrossValue" => 7419.13,
    "NumberOfInstallments" => 36,
    "OperationDateTime" => "2024-10-22T10:38:42.5776485Z",
    "Guid" => "287673c74a914c429948c2cc7a823581"
];

try {
    // In production, use:
    // $rawData = json_decode(file_get_contents('php://input'), true);

    // Remember to secure your webhook endpoint with a secret key
    // $secret added to the webhook URL: https://example.com/webhook.php?secret=your-secret-key
    // $secret = $_GET['secret'] ??
    // if ($secret !== 'your-secret') {
    //     throw new Exception('Invalid secret key');
    // }

    
    $notification = $leaselink->handleNotification($rawData);
    
    // Handle different notification statuses
    switch ($notification->getStatus()) {
        case NotificationStatus::PROCESSING:
            echo "Order is being processed\n";
            // Update order status to processing
            break;
            
        case NotificationStatus::ACCEPTED:
            echo "Order has been accepted\n";
            // Update order status to accepted
            break;
            
        case NotificationStatus::SIGN_CONTRACT:
            echo "Contract ready for signing\n";
            echo "Company: " . $notification->toArray()['companyName'] . "\n";
            echo "Contract Value: " . $notification->toArray()['contractGrossValue'] . "\n";
            echo "Invoice Data" . print_r($notification->getInvoiceData(), true) . "\n";
            // Update order status and store contract details
            break;
            
        case NotificationStatus::SEND_ASSET:
            echo "Asset can be sent\n";
            // Update order status to ready for shipping
            break;
            
        case NotificationStatus::CANCELLED:
            echo "Order was cancelled\n";
            // Update order status to cancelled
            break;
    }
    
    // Log full notification data
    echo "\nFull notification data:\n";
    print_r($notification->toArray());
    
} catch (LeaseLinkApiException $e) {
    echo "Error processing webhook: " . $e->getMessage() . "\n";
    print_r($e->getErrors());
}

// Example of production webhook endpoint:
/*
// webhook.php
header('Content-Type: application/json');

try {
    $rawData = json_decode(file_get_contents('php://input'), true);
    if (!$rawData) {
        throw new Exception('Invalid JSON payload');
    }
    
    $notification = $leaselink->handleNotification($rawData);
    
    // Process notification...
    
    http_response_code(200);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
*/
