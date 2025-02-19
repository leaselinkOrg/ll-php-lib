<?php

declare(strict_types=1);

namespace LeaseLink\Model;

use LeaseLink\Enum\NotificationStatus;
use LeaseLink\Exception\LeaseLinkApiException;

/**
 * Represents a webhook notification from LeaseLink
 * 
 * Handles notifications for:
 * - Order processing status
 * - Contract signing
 * - Asset delivery
 * - Order cancellation
 */
class NotificationData
{
    private NotificationStatus $status;
    private string $statusName;
    private string $transactionId;
    private ?string $companyName;
    private ?string $taxId;
    private ?string $city;
    private ?string $zipCode;
    private ?string $streetName;
    private ?string $streetNumber;
    private ?string $locationNumber;
    private string $partnerId;
    private string $customerExternalDocument;
    private ?string $financialProductType;
    private ?float $contractGrossValue;
    private ?int $numberOfInstallments;
    private \DateTimeImmutable $operationDateTime;
    private string $guid;

    /**
     * Create new notification from webhook data
     *
     * @param array $data Raw notification data
     * @throws LeaseLinkApiException When notification data is invalid
     */
    public function __construct(array $data)
    {
        $status = NotificationStatus::fromString($data['StatusName'] ?? '');
        if ($status === null) {
            throw new LeaseLinkApiException('Invalid status name');
        }

        try {
            $this->status = $status;
            $this->statusName = $data['StatusName'];
            $this->transactionId = $data['TransactionId'];
            $this->companyName = $data['InvoiceVatCompanyName'];
            $this->taxId = $data['InvoiceVatIdentificationNumber'];
            $this->city = $data['InvoiceVatAddressCity'];
            $this->zipCode = $data['InvoiceVatAddressZipCode'];
            $this->streetName = $data['InvoiceVatAddressStreetName'];
            $this->streetNumber = $data['InvoiceVatAddressStreetNumber'];
            $this->locationNumber = $data['InvoiceVatAddressLocationNumber'];
            $this->partnerId = $data['PartnerId'];
            $this->customerExternalDocument = $data['CustomerExternalDocument'];
            $this->financialProductType = $data['FinancialProductType'];
            $this->contractGrossValue = $data['ContractGrossValue'] ? (float)$data['ContractGrossValue'] : null;
            $this->numberOfInstallments = $data['NumberOfInstallments'] ? (int)$data['NumberOfInstallments'] : null;
            $this->operationDateTime = new \DateTimeImmutable($data['OperationDateTime']);
            $this->guid = $data['Guid'];
        } catch (\Exception $e) {
            throw new LeaseLinkApiException('Invalid notification data');
        }
    }

    /**
     * Get the notification status enum
     *
     * @return NotificationStatus Current notification status
     */
    public function getStatus(): NotificationStatus
    {
        return $this->status;
    }

    /**
     * Get the transaction ID
     * 
     * @return string Transaction ID
     */
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * Get the external document ID
     * 
     * @return string External document ID /order ID from customer
     */
    public function getCustomerExternalDocument(): string
    {
        return $this->customerExternalDocument;
    }

    /**
     * Get invoice data if available
     *
     * @return array{
     *     companyName: string|null,
     *     taxId: string|null,
     *     city: string|null,
     *     zipCode: string|null,
     *     streetName: string|null,
     *     streetNumber: string|null,
     *     locationNumber: string|null
     * } Invoice data array
     */
    public function getInvoiceData(): array
    {
        return [
            'companyName' => $this->companyName,
            'taxId' => $this->taxId,
            'city' => $this->city,
            'zipCode' => $this->zipCode,
            'streetName' => $this->streetName,
            'streetNumber' => $this->streetNumber,
            'locationNumber' => $this->locationNumber
        ];
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'statusName' => $this->statusName,
            'transactionId' => $this->transactionId,
            'companyName' => $this->companyName,
            'taxId' => $this->taxId,
            'city' => $this->city,
            'zipCode' => $this->zipCode,
            'streetName' => $this->streetName,
            'streetNumber' => $this->streetNumber,
            'locationNumber' => $this->locationNumber,
            'partnerId' => $this->partnerId,
            'customerExternalDocument' => $this->customerExternalDocument,
            'financialProductType' => $this->financialProductType,
            'contractGrossValue' => $this->contractGrossValue,
            'numberOfInstallments' => $this->numberOfInstallments,
            'operationDateTime' => $this->operationDateTime->format('c'),
            'guid' => $this->guid
        ];
    }
}
