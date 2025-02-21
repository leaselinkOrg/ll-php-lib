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
    /** @var NotificationStatus */
    private $status;
    
    /** @var string */
    private $statusName;
    
    /** @var string */
    private $transactionId;
    
    /** @var string|null */
    private $companyName;
    
    /** @var string|null */
    private $taxId;
    
    /** @var string|null */
    private $city;
    
    /** @var string|null */
    private $zipCode;
    
    /** @var string|null */
    private $streetName;
    
    /** @var string|null */
    private $streetNumber;
    
    /** @var string|null */
    private $locationNumber;
    
    /** @var string */
    private $partnerId;
    
    /** @var string */
    private $customerExternalDocument;
    
    /** @var string|null */
    private $financialProductType;
    
    /** @var float|null */
    private $contractGrossValue;
    
    /** @var int|null */
    private $numberOfInstallments;
    
    /** @var \DateTimeImmutable */
    private $operationDateTime;
    
    /** @var string */
    private $guid;

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
            'status' => $this->status->getValue(),
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
