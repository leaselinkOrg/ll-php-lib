<?php

declare(strict_types=1);

namespace LeaseLink\Config;

/**
 * Configuration options for LeaseLink calculations
 * 
 * This class handles various options that can be passed to the calculation endpoint,
 * including multi-offer settings, contact information, and cart properties.
 */
class CalculationOptions
{
    /**
     * @param bool $multiOffer Whether to enable multi-offer calculations
     * @param string|null $email Customer's email address
     * @param string|null $phone Customer's phone number
     * @param string|null $taxId Customer's tax identification number
     * @param string|null $externalOrderId External order identifier
     * @param bool $isCartReadOnly Whether the cart should be read-only
     */
    public function __construct(
        private readonly bool $multiOffer = false,
        private readonly ?string $email = null,
        private readonly ?string $phone = null,
        private readonly ?string $taxId = null,
        private readonly ?string $externalOrderId = null,
        private readonly bool $isCartReadOnly = true,
    ) {}

    /**
     * Convert configuration options to an array format
     * 
     * @return array<string, mixed> Array representation of the configuration
     */
    public function toArray(): array
    {
        $data = [
            'MultiOffer' => $this->multiOffer,
            'IsCartReadOnly' => $this->isCartReadOnly
        ];

        if ($this->email) {
            $data['Email'] = $this->email;
        }
        if ($this->phone) {
            $data['Phone'] = $this->phone;
        }
        if ($this->taxId) {
            $data['TaxId'] = $this->taxId;
        }
        if ($this->externalOrderId) {
            $data['ExternalOrderId'] = $this->externalOrderId;
        }

        return $data;
    }
}
