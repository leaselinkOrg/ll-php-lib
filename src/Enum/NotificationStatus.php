<?php

declare(strict_types=1);

namespace LeaseLink\Enum;

enum NotificationStatus: int
{
    case PROCESSING = 0;
    case CANCELLED = -1;
    case ACCEPTED = 2;
    case SEND_ASSET = 3;
    case SIGN_CONTRACT = 4;

    public static function fromString(string $status): ?self
    {
        return match (strtoupper($status)) {
            'PROCESSING' => self::PROCESSING,
            'CANCELLED' => self::CANCELLED,
            'ACCEPTED' => self::ACCEPTED,
            'SEND_ASSET' => self::SEND_ASSET,
            'SIGN_CONTRACT' => self::SIGN_CONTRACT,
            default => null
        };
    }
}
