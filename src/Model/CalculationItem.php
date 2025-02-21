<?php

declare(strict_types=1);

namespace LeaseLink\Model;

use LeaseLink\Exception\LeaseLinkApiException;
use Psr\Log\LoggerInterface;

/**
 * Represents a calculation item in the LeaseLink API.
 * 
 * This class handles validation and conversion of calculation items
 * used in lease calculations.
 */
class CalculationItem
{
    private const ALLOWED_TAX_VALUES = ['ZW', '0', '5', '8', '23'];

    /** @var string */
    private $name;

    /** @var int */
    private $quantity;

    /** @var string */
    private $categoryLevel1;

    /** @var float */
    private $unitNetPrice;

    /** @var float */
    private $unitGrossPrice;

    /** @var string */
    private $tax;

    /** @var float */
    private $unitTaxValue;

    /** @var string|null */
    private $categoryLevel2;

    /** @var string|null */
    private $categoryLevel3;

    /** @var string|null */
    private $itemId;

    /** @var LoggerInterface|null */
    private $logger;

    /**
     * Creates a new calculation item.
     *
     * @param string $name The name of the item
     * @param int $quantity The quantity of the item (must be greater than 0)
     * @param string $categoryLevel1 The primary category of the item
     * @param float $unitNetPrice The net price per unit (must be greater than 0)
     * @param float $unitGrossPrice The gross price per unit (must be greater than 0)
     * @param string $tax The tax rate (must be one of: ZW, 0, 5, 8, 23)
     * @param float $unitTaxValue The tax value per unit
     * @param string|null $categoryLevel2 Optional secondary category
     * @param string|null $categoryLevel3 Optional tertiary category
     * @param string|null $itemId Optional item identifier
     * @param LoggerInterface|null $logger Optional logger instance
     * 
     * @throws LeaseLinkApiException If validation fails
     */
    public function __construct(
        string $name,
        int $quantity,
        string $categoryLevel1,
        float $unitNetPrice,
        float $unitGrossPrice,
        string $tax,
        float $unitTaxValue,
        ?string $categoryLevel2 = null,
        ?string $categoryLevel3 = null,
        ?string $itemId = null,
        ?LoggerInterface $logger = null
    ) {
        $this->name = $name;
        $this->quantity = $quantity;
        $this->categoryLevel1 = $categoryLevel1;
        $this->unitNetPrice = $unitNetPrice;
        $this->unitGrossPrice = $unitGrossPrice;
        $this->tax = $tax;
        $this->unitTaxValue = $unitTaxValue;
        $this->categoryLevel2 = $categoryLevel2;
        $this->categoryLevel3 = $categoryLevel3;
        $this->itemId = $itemId;
        $this->logger = $logger;

        $this->validate();
    }

    /**
     * Validates the calculation item properties.
     *
     * @throws LeaseLinkApiException If any validation check fails
     */
    private function validate(): void
    {
        try {
            if (empty($this->name)) {
                throw new LeaseLinkApiException('Name is required');
            }
            if ($this->quantity < 1) {
                throw new LeaseLinkApiException('Quantity must be greater than 0');
            }
            if (empty($this->categoryLevel1)) {
                throw new LeaseLinkApiException('CategoryLevel1 is required');
            }
            if ($this->unitNetPrice <= 0) {
                throw new LeaseLinkApiException('UnitNetPrice must be greater than 0');
            }
            if ($this->unitGrossPrice <= 0) {
                throw new LeaseLinkApiException('UnitGrossPrice must be greater than 0');
            }
            if (!in_array($this->tax, self::ALLOWED_TAX_VALUES)) {
                throw new LeaseLinkApiException('Invalid tax value. Allowed values: ' . implode(', ', self::ALLOWED_TAX_VALUES));
            }
        } catch (LeaseLinkApiException $e) {
            if ($this->logger) {
                $this->logger->error('CalculationItem validation failed', [
                    'error' => $e->getMessage(),
                    'item' => $this->toArray()
                ]);
            }
            throw $e;
        }
    }

    /**
     * Converts the calculation item to an array format.
     *
     * @return array<string, mixed> The calculation item data as an array
     */
    public function toArray(): array
    {
        $data = [
            'Name' => $this->name,
            'Quantity' => $this->quantity,
            'CategoryLevel1' => $this->categoryLevel1,
            'UnitNetPrice' => $this->unitNetPrice,
            'UnitGrossPrice' => $this->unitGrossPrice,
            'Tax' => $this->tax,
            'UnitTaxValue' => $this->unitTaxValue
        ];

        if ($this->categoryLevel2) {
            $data['CategoryLevel2'] = $this->categoryLevel2;
        }
        if ($this->categoryLevel3) {
            $data['CategoryLevel3'] = $this->categoryLevel3;
        }
        if ($this->itemId) {
            $data['ItemId'] = $this->itemId;
        }

        return $data;
    }
}
