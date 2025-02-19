<?php

declare(strict_types=1);

namespace LeaseLink\Service;

use Psr\Log\AbstractLogger;
use LeaseLink\Enum\LogLevel;

class FileLogger extends AbstractLogger
{
    private readonly LogLevel $minimumLevel;

    public function __construct(
        private readonly string $logFile,
        private readonly bool $debug = false,
        string $minimumLevel = 'info'
    ) {
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->minimumLevel = LogLevel::fromString($minimumLevel);
    }

    public function log($level, string|\Stringable $message, array $context = []): void
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
