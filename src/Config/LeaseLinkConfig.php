<?php

declare(strict_types=1);

namespace LeaseLink\Config;

use LeaseLink\Enum\LogLevel;

/**
 * Configuration class for LeaseLink API client
 * 
 * This class manages all configuration settings required for interacting with the LeaseLink API,
 * including API URLs, authentication, debugging, and logging settings.
 */
final class LeaseLinkConfig
{
    private readonly LogLevel $logLevel;

    /**
     * @param string      $apiUrl     Production API URL
     * @param string      $testApiUrl  Test environment API URL
     * @param string|null $apiKey     API authentication key
     * @param bool        $isTest     Whether to use test environment
     * @param bool        $debug      Enable debug mode
     * @param string      $logFile    Path to log file
     * @param string      $logLevel   Logging level ('debug', 'info', 'warning', 'error')
     */
    public function __construct(
        private readonly string $apiUrl = 'https://online.leaselink.pl/api',
        private readonly string $testApiUrl = 'https://onlinetest.leaselink.pl/api',
        private readonly ?string $apiKey = null,
        private readonly bool $isTest = false,
        private readonly bool $debug = false,
        private readonly string $logFile = 'logs/leaselink.log',
        string $logLevel = 'info'
    ) {
        $this->logLevel = LogLevel::fromString($logLevel);
    }

    public function getApiUrl(): string
    {
        return $this->isTest ? $this->testApiUrl : $this->apiUrl;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function getLogFile(): string
    {
        return $this->logFile;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function getLogLevel(): LogLevel
    {
        return $this->logLevel;
    }
}
