<?php

declare(strict_types=1);

namespace LeaseLink\Exception;

/**
 * Exception class for handling LeaseLink API errors
 * 
 * This exception can handle both string and array error messages,
 * converting array messages into a formatted string representation.
 */
class LeaseLinkApiException extends \Exception
{
    /** @var array Array of error messages */
    private $errors = [];

    /**
     * @param string|array $message Error message or array of error messages
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct($message = "", int $code = 0, ?\Throwable $previous = null)
    {
        if (is_array($message)) {
            $this->errors = $message;
            $message = $this->formatArrayMessage($message);
        } else {
            $message = (string) $message;
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the array of error messages
     * 
     * @return array The error messages
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Format array message into a single string
     * 
     * @param array $message Array of error messages
     * @return string Formatted error message
     */
    private function formatArrayMessage(array $message): string
    {
        if (empty($message)) {
            return "Unknown error occurred";
        }

        return implode("; ", array_map(function ($key, $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            return is_string($key) ? "$key: $value" : $value;
        }, array_keys($message), $message));
    }
}