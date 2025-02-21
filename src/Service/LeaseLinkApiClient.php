<?php

declare(strict_types=1);

namespace LeaseLink\Service;

use LeaseLink\Config\LeaseLinkConfig;
use LeaseLink\Exception\LeaseLinkApiException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Client for interacting with the LeaseLink API
 * 
 * Handles API communication, authentication, and error handling while providing
 * logging capabilities for debugging and monitoring.
 */
final class LeaseLinkApiClient implements LeaseLinkApiClientInterface
{
    /** @var LeaseLinkConfig */
    private $config;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param LeaseLinkConfig  $config Configuration for the API client
     * @param LoggerInterface|null $logger Optional PSR-3 logger instance
     */
    public function __construct(
        LeaseLinkConfig $config,
        ?LoggerInterface $logger = null
    ) {
        $this->config = $config;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Makes an HTTP POST request to the specified API endpoint
     *
     * @param string $endpoint API endpoint path
     * @param array $data Request payload
     * @param string|null $token Optional authentication token
     * @return array Decoded JSON response
     * @throws LeaseLinkApiException When API request fails or returns invalid response
     */
    public function call(string $endpoint, array $data, ?string $token = null): array
    {
        $url = $this->config->getApiUrl() . $endpoint;
        $this->logger->info('Making API call', ['endpoint' => $endpoint, 'url' => $url]);

        $ch = curl_init($url);

        $headers = ['Content-Type: application/json'];
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_COOKIEFILE => '',
            CURLOPT_COOKIEJAR => ''
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $this->logger->info('API call successful', ['statusCode' => $httpCode]);
        }

        $this->logger->debug('HTTP response', ['code' => $httpCode, 'body' => $response, 'headers' => $headers, 'data' => $data, 'url' => $url]);

        if ($httpCode < 200 || $httpCode >= 300) {
            $body = $response !== false ? json_decode($response, true) : null;

            $this->logger->error('API request failed', [
                'url' => $url,
                'statusCode' => $httpCode,
                'response' => $body,
                'request' => $data
            ]);

            throw new LeaseLinkApiException($body['errors'] ?? ['HTTP request failed with status ' . $httpCode]);
        }

        $responseData = json_decode($response, true);
        $this->logger->debug('JSON response', $responseData);

        if ($responseData === null) {
            $this->logger->error('Invalid JSON response', [
                'url' => $url,
                'response' => $response
            ]);
            throw new LeaseLinkApiException('Invalid JSON response');
        }

        return $responseData;
    }

    /**
     * Gets the current logger instance
     *
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Sets a new logger instance
     *
     * @param LoggerInterface $logger PSR-3 compliant logger
     * @return LeaseLinkApiClientInterface
     */
    public function setLogger(LoggerInterface $logger): LeaseLinkApiClientInterface
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Requests a new authentication token from the API
     *
     * @return array{Token: string, ValidTo: string} Token data containing the token string and expiration date
     * @throws LeaseLinkApiException When token request fails or returns invalid response
     */
    public function getToken(): array
    {
        $this->logger->info('Requesting new token');

        try {
            $result = $this->call('/GetToken', [
                'ApiKey' => $this->config->getApiKey()
            ]);

            if (isset($result['Token'])) {
                $this->logger->info('Token acquired successfully', [
                    'validTo' => $result['ValidTo'] ?? 'unknown'
                ]);
            }

            if (!isset($result['Token']) || !isset($result['ValidTo'])) {
                $this->logger->error('Invalid token response', ['response' => $result]);
                throw new LeaseLinkApiException('Invalid token response');
            }

            return [
                'Token' => $result['Token'],
                'ValidTo' => $result['ValidTo']
            ];
        } catch (\Exception $e) {
            $this->logger->error('Token acquisition failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}