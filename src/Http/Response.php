<?php

namespace PhpDevCommunity\HttpClient\Http;

final class Response
{
    private string $body;
    private int $statusCode;
    private array $headers;

    public function __construct(string $body, int $statusCode, array $headers)
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function bodyToArray(): array
    {
        try {
            $decodedBody = json_decode($this->body, true, 512, JSON_THROW_ON_ERROR);
        }catch (\JsonException $e) {
            throw new \Exception('Invalid JSON format in response body : ' . $e->getMessage());
        }
        return $decodedBody;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
