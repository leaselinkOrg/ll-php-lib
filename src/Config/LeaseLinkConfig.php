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
    /** @var LogLevel */
    private $logLevel;
    
    /** @var string */
    private $apiUrl;
    
    /** @var string */
    private $testApiUrl;
    
    /** @var string|null */
    private $apiKey;
    
    /** @var bool */
    private $isTest;
    
    /** @var bool */
    private $debug;
    
    /** @var string */
    private $logFile;

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
        string $apiUrl = 'https://online.leaselink.pl',
        string $testApiUrl = 'https://onlinetest.leaselink.pl',
        ?string $apiKey = null,
        bool $isTest = false,
        bool $debug = false,
        string $logFile = 'logs/leaselink.log',
        string $logLevel = 'info'
    ) {
        $this->apiUrl = $apiUrl;
        $this->testApiUrl = $testApiUrl;
        $this->apiKey = $apiKey;
        $this->isTest = $isTest;
        $this->debug = $debug;
        $this->logFile = $logFile;
        $this->logLevel = LogLevel::fromString($logLevel);
    }

    public function getBaseUrl(): string
    {
        return $this->isTest ? $this->testApiUrl : $this->apiUrl;
    }

    public function getApiUrl(): string
    {
        return $this->getBaseUrl() .'/api';
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