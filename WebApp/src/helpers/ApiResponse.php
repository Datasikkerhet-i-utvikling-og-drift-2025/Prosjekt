<?php

namespace helpers;

class ApiResponse
{
    public bool $success;
    public string $message;
    public ?array $errors;
    public mixed $data;

    public function __construct(bool $success, string $message, mixed $data = null, ?array $errors = null)
    {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
        $this->errors = $errors;
    }


    /**
     * Converts the response object to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
            'errors' => $this->errors,
        ];
    }
}
