<?php

declare(strict_types=1);

namespace LeaseLink\Model;

use LeaseLink\Exception\LeaseLinkApiException;
use LeaseLink\Config\LeaseLinkConfig;
use Psr\Log\LoggerInterface;

/**
 * Represents a calculation response from the LeaseLink API
 * 
 * Contains calculation details including:
 * - Calculation ID and URL
 * - Total values (net, gross, tax)
 * - Available financing offers
 */
class CalculationResponse
{
    /** @var string */
    private $calculationId;
    
    /** @var string */
    private $calculationUrl;
    
    /** @var float */
    private $totalNetValue;
    
    /** @var float */
    private $totalGrossValue;
    
    /** @var float */
    private $totalTaxValue;
    
    /** @var array */
    private $offers = [];
    
    /** @var array */
    private $rawResponse;
    
    /** @var LeaseLinkConfig */
    private $config;
    
    /** @var LoggerInterface|null */
    private $logger;

    /**
     * Create new calculation response
     *
     * @param array $response Raw API response data
     * @param LeaseLinkConfig $config Configuration for URL building
     * @param LoggerInterface|null $logger Optional logger for response processing
     * @throws LeaseLinkApiException When response data is invalid
     */
    public function __construct(
        array $response,
        LeaseLinkConfig $config,
        ?LoggerInterface $logger = null
    ) {
        $this->rawResponse = $response;
        $this->config = $config;
        $this->logger = $logger;
        $this->validate();
        $this->parseResponse();
    }

    private function validate(): void
    {
        try {
            $requiredFields = [
                'CalculationId',
                'CalculationUrl',
                'TotalNetValue',
                'TotalGrossValue',
                'TotalTaxValue',
                'Offers'
            ];

            foreach ($requiredFields as $field) {
                if (!isset($this->rawResponse[$field])) {
                    throw new LeaseLinkApiException("Missing {$field} in response");
                }
            }

            if (!is_array($this->rawResponse['Offers'])) {
                throw new LeaseLinkApiException('Invalid Offers format in response');
            }
        } catch (LeaseLinkApiException $e) {
            if ($this->logger) {
                $this->logger->error('CalculationResponse validation failed', [
                    'error' => $e->getMessage(),
                    'response' => $this->rawResponse
                ]);
            }
            throw $e;
        }
    }

    private function parseResponse(): void
    {
        if ($this->logger) {
            $this->logger->info('Parsing calculation response', [
                'calculationId' => $this->rawResponse['CalculationId'] ?? 'unknown',
                'offersCount' => count($this->rawResponse['Offers'] ?? [])
            ]);
        }

        $this->calculationId = $this->rawResponse['CalculationId'];
        $this->calculationUrl = $this->rawResponse['CalculationUrl'];
        $this->totalNetValue = (float)$this->rawResponse['TotalNetValue'];
        $this->totalGrossValue = (float)$this->rawResponse['TotalGrossValue'];
        $this->totalTaxValue = (float)$this->rawResponse['TotalTaxValue'];
        $this->offers = array_map([$this, 'parseOffer'], $this->rawResponse['Offers']);

        if ($this->logger) {
            $this->logger->info('Response parsed successfully', [
                'totalNetValue' => $this->totalNetValue,
                'totalGrossValue' => $this->totalGrossValue
            ]);
        }
    }

    private function parseOffer(array $offer): array
    {
        return [
            'numberOfInstallments' => (int)($offer['NumberOfInstallments'] ?? 0),
            'netMonthlyInstallment' => (float)($offer['NetMonthlyInstallment'] ?? 0),
            'grossMonthlyInstallment' => (float)($offer['GrossMonthlyInstallment'] ?? 0),
            'netInitialFee' => (float)($offer['NetInitialFee'] ?? 0),
            'grossInitialFee' => (float)($offer['GrossInitialFee'] ?? 0),
            'percentInitialFee' => (float)($offer['PercentInitialFee'] ?? 0),
            'netResidualFee' => (float)($offer['NetResidualFee'] ?? 0),
            'grossResidualFee' => (float)($offer['GrossResidualFee'] ?? 0),
            'percentResidualFee' => (float)($offer['PercentResidualFee'] ?? 0),
            'interestRate' => (float)($offer['InterestRate'] ?? 0),
            'calculationPackageId' => (string)($offer['CalculationPackageId'] ?? ''),
            'financialProductType' => $offer['FinancialProductType'] ?? ''
        ];
    }

    public function getCalculationId(): string
    {
        return $this->calculationId;
    }

    /**
     * Get the full calculation URL including API base URL
     *
     * @return string Complete URL to calculation
     */
    public function getCalculationUrl(): string
    {
        return rtrim($this->config->getBaseUrl(), '/') . $this->calculationUrl;
    }

    public function getTotalNetValue(): float
    {
        return $this->totalNetValue;
    }

    public function getTotalGrossValue(): float
    {
        return $this->totalGrossValue;
    }

    public function getTotalTaxValue(): float
    {
        return $this->totalTaxValue;
    }

    /**
     * Get array of available financing offers
     *
     * @return array List of offers with details like installments and fees
     */
    public function getOffers(): array
    {
        return $this->offers;
    }

    public function getRawResponse(): array
    {
        return $this->rawResponse;
    }

    public function toArray(): array
    {
        return [
            'calculationId' => $this->calculationId,
            'calculationUrl' => $this->getCalculationUrl(),
            'totalNetValue' => $this->totalNetValue,
            'totalGrossValue' => $this->totalGrossValue,
            'totalTaxValue' => $this->totalTaxValue,
            'offers' => $this->offers
        ];
    }
}
