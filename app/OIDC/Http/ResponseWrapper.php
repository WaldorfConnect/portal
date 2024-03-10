<?php

namespace App\OIDC\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ResponseWrapper implements ResponseInterface
{
    private \CodeIgniter\HTTP\ResponseInterface $response;

    public function __construct(\CodeIgniter\HTTP\ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function getProtocolVersion(): string
    {
        return $this->response->getProtocolVersion();
    }

    public function withProtocolVersion(string $version): MessageInterface
    {
        $clonedResponse = clone $this->response;
        $clonedResponse->setProtocolVersion($version);
        return new ResponseWrapper($clonedResponse);
    }

    public function getHeaders(): array
    {
        $headers = $this->response->headers();
        $headerArray = [];
        foreach ($headers as $header) {
            $headerArray[] = [$header->getName(), $header->getValue()];
        }
        return $headerArray;
    }

    public function hasHeader(string $name): bool
    {
        return $this->response->hasHeader($name);
    }

    public function getHeader(string $name): array
    {
        return $this->response->header($name);
    }

    public function getHeaderLine(string $name): string
    {
        return $this->response->getHeaderLine($name);
    }

    public function withHeader(string $name, $value): MessageInterface
    {
        $clonedResponse = clone $this->response;
        $clonedResponse->setHeader($name, $value);
        return new ResponseWrapper($clonedResponse);
    }

    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $clonedResponse = clone $this->response;
        $clonedResponse->appendHeader($name, $value);
        return new ResponseWrapper($clonedResponse);
    }

    public function withoutHeader(string $name): MessageInterface
    {
        $clonedResponse = clone $this->response;
        $clonedResponse->removeHeader($name);
        return new ResponseWrapper($clonedResponse);
    }

    public function getBody(): StreamInterface
    {
        // TODO: Implement getBody() method.
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        // TODO: Implement withBody() method.
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $clonedResponse = clone $this->response;
        $clonedResponse->setStatusCode($code, $reasonPhrase);
        return new ResponseWrapper($clonedResponse);
    }

    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }

    public function getHandle(): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response;
    }
}