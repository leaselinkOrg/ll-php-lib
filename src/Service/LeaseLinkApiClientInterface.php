<?php

declare(strict_types=1);

namespace LeaseLink\Service;

use Psr\Log\LoggerInterface;

interface LeaseLinkApiClientInterface
{
    public function call(string $endpoint, array $data, ?string $token = null): array;
    public function getToken(): array;
    public function getLogger(): LoggerInterface;

}
