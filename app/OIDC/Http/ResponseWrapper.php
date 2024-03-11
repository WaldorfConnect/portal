<?php

namespace App\OIDC\Http;

use CodeIgniter\HTTP\Response;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ResponseWrapper implements ResponseInterface
{
    private Response $response;
    private ?StreamInterface $stream = null;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function getProtocolVersion(): string
    {
        return $this->response->getProtocolVersion();
    }

    public function withProtocolVersion(string $version): MessageInterface
    {
        $this->response->setProtocolVersion($version);
        return $this;
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
        $this->response->setHeader($name, $value);
        return $this;
    }

    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $this->response->appendHeader($name, $value);
        return $this;
    }

    public function withoutHeader(string $name): MessageInterface
    {
        $this->response->removeHeader($name);
        return $this;
    }

    public function getBody(): StreamInterface
    {
        if (!$this->stream) {
            $this->stream = Stream::create('');
        }

        return $this->stream;
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        $this->response->setBody($body);
        $this->stream = $body;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $this->response->setStatusCode($code, $reasonPhrase);
        return $this;
    }

    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }

    public function getHandle(): Response
    {
        $this->response->setBody($this->stream);
        return $this->response;
    }
}