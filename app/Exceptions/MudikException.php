<?php

namespace App\Exceptions;

use App\Enums\ErrorCode;
use Exception;

class MudikException extends Exception
{
    protected ErrorCode $errorCode;
    protected array $additionalData = [];

    public function __construct(
        ErrorCode $errorCode,
        ?string $customMessage = null,
        array $additionalData = [],
        int $code = 0,
        ?Exception $previous = null
    ) {
        $this->errorCode = $errorCode;
        $this->additionalData = $additionalData;
        
        $message = $customMessage ?? $errorCode->getMessage();
        
        parent::__construct($message, $code, $previous);
    }

    public function getErrorCode(): ErrorCode
    {
        return $this->errorCode;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    public function toArray(): array
    {
        return [
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => $this->errorCode->value,
            'data' => $this->additionalData,
        ];
    }
}