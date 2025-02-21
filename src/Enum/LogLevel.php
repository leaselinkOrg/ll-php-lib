<?php

declare(strict_types=1);

namespace LeaseLink\Enum;

/**
 * Class representing log levels
 */
final class LogLevel
{
    const DEBUG = 'debug';
    const INFO = 'info';
    const NOTICE = 'notice';
    const WARNING = 'warning';
    const ERROR = 'error';
    const CRITICAL = 'critical';
    const ALERT = 'alert';
    const EMERGENCY = 'emergency';

    /** @var string */
    private $value;

    /**
     * @param string $value
     */
    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @param string $level
     * @return self
     */
    public static function fromString(string $level): self
    {
        $level = strtolower($level);
        switch ($level) {
            case self::DEBUG:
                return new self(self::DEBUG);
            case self::INFO:
                return new self(self::INFO);
            case self::NOTICE:
                return new self(self::NOTICE);
            case self::WARNING:
                return new self(self::WARNING);
            case self::ERROR:
                return new self(self::ERROR);
            case self::CRITICAL:
                return new self(self::CRITICAL);
            case self::ALERT:
                return new self(self::ALERT);
            case self::EMERGENCY:
                return new self(self::EMERGENCY);
            default:
                return new self(self::INFO);
        }
    }

    /**
     * @param LogLevel $minimumLevel
     * @return bool
     */
    public function shouldLog(self $minimumLevel): bool
    {
        $levels = [
            self::DEBUG => 0,
            self::INFO => 1,
            self::NOTICE => 2,
            self::WARNING => 3,
            self::ERROR => 4,
            self::CRITICAL => 5,
            self::ALERT => 6,
            self::EMERGENCY => 7
        ];

        return $levels[$this->value] >= $levels[$minimumLevel->value];
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}