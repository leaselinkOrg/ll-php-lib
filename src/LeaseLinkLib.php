<?php

declare(strict_types=1);

namespace LeaseLink;

use LeaseLink\Config\LeaseLinkConfig;
use LeaseLink\Service\LeaseLinkApiClientInterface;
use LeaseLink\Exception\LeaseLinkApiException;
use LeaseLink\Config\CalculationOptions;
use LeaseLink\Model\CalculationItem;
use LeaseLink\Model\CalculationResponse;
use LeaseLink\Model\ChosenOfferResponse;
use LeaseLink\Model\NotificationData;

/**
 * Main LeaseLink API library class
 * 
 * Provides methods for interacting with the LeaseLink API including:
 * - Creating calculations
 * - Saving chosen offers
 * - Handling notifications
 */
final class LeaseLinkLib
{
    private const VERSION = '1.0.2';

    /**
     * Create a new LeaseLink library instance
     *
     * @param LeaseLinkConfig $config Configuration object
     * @param LeaseLinkApiClientInterface $apiClient API client implementation
     * @throws LeaseLinkApiException When API key is missing
     */
    public function __construct(
        private readonly LeaseLinkConfig $config,
        private readonly LeaseLinkApiClientInterface $apiClient
    ) {
        if ($this->config->getApiKey() === null) {
            $this->apiClient->getLogger()->error('API key is missing');
            throw new LeaseLinkApiException('API key is required');
        }
    }

    /**
     * Create a new calculation with the given items and options
     *
     * @param CalculationItem[] $items Array of items to calculate
     * @param CalculationOptions|null $options Optional calculation settings
     * @return CalculationResponse Calculation result with offers
     * @throws LeaseLinkApiException When API call fails
     */
    public function createCalculation(array $items, ?CalculationOptions $options = null): CalculationResponse
    {
        try {
            $this->apiClient->getLogger()->info('Creating calculation', [
                'itemsCount' => count($items),
                'hasOptions' => $options !== null
            ]);

            $token = $this->apiClient->getToken();

            $data = [
                'Items' => array_map(fn(CalculationItem $item) => $item->toArray(), $items)
            ];

            if ($options) {
                $data = array_merge($data, $options->toArray());
            }

            $response = $this->apiClient->call('/CreateCalculation', $data, $token['Token']);

            $this->apiClient->getLogger()->info('Calculation created successfully', [
                'calculationId' => $response['CalculationId'] ?? 'unknown',
                'offersCount' => count($response['Offers'] ?? [])
            ]);

            return new CalculationResponse($response, $this->config);
        } catch (\Exception $e) {
            $this->apiClient->getLogger()->error('Calculation creation failed', [
                'error' => $e->getMessage(),
                'items' => $items,
                'options' => $options ? $options->toArray() : null
            ]);
            throw $e;
        }
    }

    /**
     * Save the chosen offer and get redirect URL
     *
     * @param string $offerGuid The offer GUID from calculation result
     * @param string $calculationPackageId The package ID from chosen offer
     * @return ChosenOfferResponse Response with redirect URL
     * @throws LeaseLinkApiException When saving offer fails
     */
    public function saveChosenOffer(string $offerGuid, string $calculationPackageId): ChosenOfferResponse
    {
        try {
            $this->apiClient->getLogger()->info('Saving chosen offer', [
                'OfferGuid' => $offerGuid,
                'CalculationPackageId' => $calculationPackageId
            ]);

            $token = $this->apiClient->getToken();

            $data = [
                'OfferGuid' => $offerGuid,
                'CalculationPackageId' => $calculationPackageId
            ];

            $response = $this->apiClient->call('/SaveChosenOffer', $data, $token['Token']);

            $this->apiClient->getLogger()->info('Chosen offer saved successfully', [
                'response' => $response
            ]);

            return new ChosenOfferResponse($response);
        } catch (\Exception $e) {
            $this->apiClient->getLogger()->error('Saving chosen offer failed', [
                'error' => $e->getMessage(),
                'OfferGuid' => $offerGuid,
                'CalculationPackageId' => $calculationPackageId
            ]);
            throw $e;
        }
    }

    /**
     * Handle webhook notification from LeaseLink
     *
     * @param array $rawData Raw notification data from webhook
     * @return NotificationData Parsed notification data
     * @throws LeaseLinkApiException When notification data is invalid
     */
    public function handleNotification(array $rawData): NotificationData
    {
        try {
            $this->apiClient->getLogger()->info('Processing notification', [
                'status' => $rawData['StatusName'] ?? 'unknown',
                'transactionId' => $rawData['TransactionId'] ?? 'unknown'
            ]);

            $notification = new NotificationData($rawData);

            $this->apiClient->getLogger()->info('Notification processed', [
                'status' => $notification->getStatus()->name,
                'transactionId' => $notification->getTransactionId(),
                'document' => $notification->getCustomerExternalDocument(),
                'invoiceData' => $notification->getInvoiceData()
            ]);

            return $notification;
        } catch (\Exception $e) {
            $this->apiClient->getLogger()->error('Notification processing failed', [
                'error' => $e->getMessage(),
                'data' => $rawData
            ]);
            throw $e;
        }
    }
}
