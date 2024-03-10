<?php

namespace App\OIDC\Http;

use CodeIgniter\HTTP\IncomingRequest;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class RequestWrapper implements ServerRequestInterface
{
    private IncomingRequest $incomingRequest;

    public function __construct(IncomingRequest $incomingRequest)
    {
        $this->incomingRequest = $incomingRequest;
    }

    public function getProtocolVersion(): string
    {
        return $this->incomingRequest->getProtocolVersion();
    }

    public function withProtocolVersion(string $version): MessageInterface
    {
        $incomingRequest = clone $this->incomingRequest;
        $incomingRequest->setProtocolVersion($version);
        return new RequestWrapper($incomingRequest);
    }

    public function getHeaders(): array
    {
        $headers = $this->incomingRequest->headers();
        $headerArray = [];
        foreach ($headers as $header) {
            $headerArray[] = [$header->getName(), $header->getValue()];
        }
        return $headerArray;
    }

    public function hasHeader(string $name): bool
    {
        return $this->incomingRequest->hasHeader($name);
    }

    public function getHeader(string $name): array
    {
        return $this->incomingRequest->header($name);
    }

    public function getHeaderLine(string $name): string
    {
        return $this->incomingRequest->getHeaderLine($name);
    }

    public function withHeader(string $name, $value): MessageInterface
    {
        $incomingRequest = clone $this->incomingRequest;
        $incomingRequest->setHeader($name, $value);
        return new RequestWrapper($incomingRequest);
    }

    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $incomingRequest = clone $this->incomingRequest;
        $incomingRequest->appendHeader($name, $value);
        return new RequestWrapper($incomingRequest);
    }

    public function withoutHeader(string $name): MessageInterface
    {
        $incomingRequest = clone $this->incomingRequest;
        $incomingRequest->removeHeader($name);
        return new RequestWrapper($incomingRequest);
    }

    public function getBody(): StreamInterface
    {
        // TODO: Implement getBody() method.
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        // TODO: Implement withBody() method.
    }

    public function getRequestTarget(): string
    {
        return "/";
    }

    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        return $this;
    }

    public function getMethod(): string
    {
        return $this->incomingRequest->getMethod();
    }

    public function withMethod(string $method): RequestInterface
    {
        return new RequestWrapper($this->incomingRequest->withMethod($method));
    }

    public function getUri(): UriInterface
    {
        return $this->incomingRequest->getUri();
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        // TODO: Implement withUri() method.
    }

    public function getServerParams(): array
    {
        return [];
    }

    public function getCookieParams(): array
    {
        return [];
    }

    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        return $this;
    }

    public function getQueryParams(): array
    {
        $this->incomingRequest->
        // TODO: Implement getQueryParams() method.
    }

    public function withQueryParams(array $query): ServerRequestInterface
    {

        // TODO: Implement withQueryParams() method.
    }

    public function getUploadedFiles(): array
    {
        return [];
    }

    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        return $this;
    }

    public function getParsedBody()
    {
        // TODO: Implement getParsedBody() method.
    }

    public function withParsedBody($data): ServerRequestInterface
    {
        // TODO: Implement withParsedBody() method.
    }

    public function getAttributes(): array
    {
        return [];
    }

    public function getAttribute(string $name, $default = null)
    {
        return null;
    }

    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        return $this;
    }

    public function withoutAttribute(string $name): ServerRequestInterface
    {
        return $this;
    }
}