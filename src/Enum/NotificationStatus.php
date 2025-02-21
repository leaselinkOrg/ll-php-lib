<?php

declare(strict_types=1);

namespace LeaseLink\Enum;

/**
 * Class representing notification statuses
 */
final class NotificationStatus
{
    const PROCESSING = 0;
    const CANCELLED = -1;
    const ACCEPTED = 2;
    const SEND_ASSET = 3;
    const SIGN_CONTRACT = 4;

    /** @var int */
    private $value;

    /**
     * @param int $value
     */
    private function __construct(int $value)
    {
        $this->value = $value;
    }

    /**
     * @param string $status
     * @return self|null
     */
    public static function fromString(string $status): ?self
    {
        switch (strtoupper($status)) {
            case 'PROCESSING':
                return new self(self::PROCESSING);
            case 'CANCELLED':
                return new self(self::CANCELLED);
            case 'ACCEPTED':
                return new self(self::ACCEPTED);
            case 'SEND_ASSET':
                return new self(self::SEND_ASSET);
            case 'SIGN_CONTRACT':
                return new self(self::SIGN_CONTRACT);
            default:
                return null;
        }
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }
}