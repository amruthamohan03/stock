<?php

class EmcfException extends Exception
{
    protected ?int $emcfErrorCode = null;
    protected array $responseData = [];

    public function __construct(
        string $message,
        int $code = 0,
        array $responseData = [],
        ?int $emcfErrorCode = null
    ) {
        parent::__construct($message, $code);
        $this->responseData = $responseData;
        $this->emcfErrorCode = $emcfErrorCode;
    }

    public function getEmcfErrorCode(): ?int
    {
        return $this->emcfErrorCode;
    }

    public function isRetryable(): bool
    {
        if ($this->emcfErrorCode === null) {
            return false;
        }

        return EmcfErrorCodes::isRetryable($this->emcfErrorCode);
    }

    public function getResponseData(): array
    {
        return $this->responseData;
    }
}
