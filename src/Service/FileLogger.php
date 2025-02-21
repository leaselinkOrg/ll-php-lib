<?php

declare(strict_types=1);

namespace LeaseLink\Service;

use Psr\Log\AbstractLogger;
use LeaseLink\Enum\LogLevel;

class FileLogger extends AbstractLogger
{
    /** @var LogLevel */
    private $minimumLevel;

    /** @var string */
    private $logFile;

    /** @var bool */
    private $debug;

    /**
     * @param string $logFile Path to log file
     * @param bool $debug Enable debug logging
     * @param string $minimumLevel Minimum log level to record
     */
    public function __construct(
        string $logFile,
        bool $debug = false,
        string $minimumLevel = 'info'
    ) {
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        
        $this->logFile = $logFile;
        $this->debug = $debug;
        $this->minimumLevel = LogLevel::fromString($minimumLevel);
    }

    /**
     * @param mixed $level
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        $logLevel = LogLevel::fromString($level);
        
        if (!$this->debug && $logLevel === LogLevel::DEBUG) {
            return;
        }

        if (!$logLevel->shouldLog($this->minimumLevel)) {
            return;
        }

        $date = date('Y-m-d H:i:s');
        $content = "[$date] [$level] $message";
        
        if (!empty($context)) {
            $content .= "\nContext: " . json_encode($context, JSON_PRETTY_PRINT);
        }
        
        $content .= "\n" . str_repeat('-', 80) . "\n";
        
        file_put_contents($this->logFile, $content, FILE_APPEND);
    }
}