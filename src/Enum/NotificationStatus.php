<?php

declare(strict_types=1);

namespace LeaseLink\Enum;

enum NotificationStatus: string
{
    case NEW = 'NEW';
    case PROCESSING = 'PROCESSING';
    case ACCEPTED = 'ACCEPTED';
    case CANCELLED = 'CANCELLED';
    case SIGN_CONTRACT = 'SIGN_CONTRACT';
    case PAYMENT_FOR_ASSET = 'PAYMENT_FOR_ASSET';
    case SEND_ASSET = 'SEND_ASSET';
    case BNPL_STATUS_CHANGED = 'BNPL_STATUS_CHANGED';

    public static function fromString(string $status): ?self
    {
        return self::tryFrom(strtoupper($status));
    }
}
