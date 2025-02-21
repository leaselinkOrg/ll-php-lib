<?php

declare(strict_types=1);

namespace LeaseLink\Service;

use Psr\Log\LoggerInterface;

/**
 * Interface for LeaseLink API client implementations
 */
interface LeaseLinkApiClientInterface
{
    /**
     * Makes an HTTP POST request to the specified API endpoint
     *
     * @param string $endpoint API endpoint path
     * @param array $data Request payload
     * @param string|null $token Optional authentication token
     * @return array Decoded JSON response
     */
    public function call(string $endpoint, array $data, ?string $token = null): array;

    /**
     * Requests a new authentication token from the API
     *
     * @return array{Token: string, ValidTo: string} Token data
     */
    public function getToken(): array;

    /**
     * Gets the current logger instance
     *
     * @return LoggerInterface The current logger
     */
    public function getLogger(): LoggerInterface;

    /**
     * Sets a new logger instance
     *
     * @param LoggerInterface $logger PSR-3 compliant logger
     * @return self For method chaining
     */
    public function setLogger(LoggerInterface $logger): self;
}