<?php

declare(strict_types=1);

namespace LeaseLink\Model;

use LeaseLink\Exception\LeaseLinkApiException;
use Psr\Log\LoggerInterface;

/**
 * Represents a response from choosing an offer in the LeaseLink API.
 * 
 * This class handles the parsing and validation of the response received
 * after selecting a specific leasing offer.
 */
class ChosenOfferResponse
{
    /** @var string URL for redirecting the user after offer selection */
    private $redirectUrl;

    /** @var array The original, unprocessed response data */
    private $rawResponse;

    /** @var LoggerInterface|null */
    private $logger;

    /**
     * Creates a new chosen offer response instance.
     *
     * @param array $response The raw response data from the API
     * @param LoggerInterface|null $logger Optional logger instance
     * 
     * @throws LeaseLinkApiException If the response validation fails
     */
    public function __construct(
        array $response,
        ?LoggerInterface $logger = null
    ) {
        $this->rawResponse = $response;
        $this->logger = $logger;
        $this->validate();
        $this->parseResponse();
    }

    /**
     * Gets the URL where the user should be redirected.
     *
     * @return string The redirect URL
     */
    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }

    /**
     * Gets the original, unprocessed response data.
     *
     * @return array The raw response data
     */
    public function getRawResponse(): array
    {
        return $this->rawResponse;
    }

    /**
     * Converts the response to an array format.
     *
     * @return array The response data as an array
     */
    public function toArray(): array
    {
        return [
            'redirectUrl' => $this->redirectUrl,
        ];
    }

    /**
     * Validates the response data.
     *
     * @throws LeaseLinkApiException If required fields are missing
     */
    private function validate(): void
    {
        try {
            if (!isset($this->rawResponse['RedirectUrl'])) {
                throw new LeaseLinkApiException('Missing RedirectUrl in response');
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

    /**
     * Parses the raw response data into object properties.
     */
    private function parseResponse(): void
    {
        if ($this->logger) {
            $this->logger->info('Parsing calculation response', [
                'RedirectUrl' => $this->rawResponse['RedirectUrl'] ?? 'unknown'
            ]);
        }

        $this->redirectUrl = $this->rawResponse['RedirectUrl'];

        if ($this->logger) {
            $this->logger->info('Response parsed successfully', [
                'redirectUrl' => $this->redirectUrl,
            ]);
        }
    }
}
