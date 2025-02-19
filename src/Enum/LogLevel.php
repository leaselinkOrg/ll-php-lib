<?php

declare(strict_types=1);

namespace LeaseLink\Enum;

enum LogLevel: string
{
    case DEBUG = 'debug';
    case INFO = 'info';
    case NOTICE = 'notice';
    case WARNING = 'warning';
    case ERROR = 'error';
    case CRITICAL = 'critical';
    case ALERT = 'alert';
    case EMERGENCY = 'emergency';

    public static function fromString(string $level): self
    {
        return match (strtolower($level)) {
            'debug' => self::DEBUG,
            'info' => self::INFO,
            'notice' => self::NOTICE,
            'warning' => self::WARNING,
            'error' => self::ERROR,
            'critical' => self::CRITICAL,
            'alert' => self::ALERT,
            'emergency' => self::EMERGENCY,
            default => self::INFO
        };
    }

    public function shouldLog(LogLevel $minimumLevel): bool
    {
        $levels = [
            self::DEBUG->value => 0,
            self::INFO->value => 1,
            self::NOTICE->value => 2,
            self::WARNING->value => 3,
            self::ERROR->value => 4,
            self::CRITICAL->value => 5,
            self::ALERT->value => 6,
            self::EMERGENCY->value => 7
        ];

        return $levels[$this->value] >= $levels[$minimumLevel->value];
    }
}
