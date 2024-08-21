<?php

namespace App\OIDC\Http;

use CodeIgniter\HTTP\IncomingRequest;
use Nyholm\Psr7\Stream;
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
        $this->incomingRequest->setProtocolVersion($version);
        return $this;
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
        $values = [];
        $result = $this->incomingRequest->header($name);
        if (is_array($result)) {
            foreach ($result as $item) {
                $values[] = $item->getValue();
            }
        } else {
            $values[] = $result->getValue();
        }

        return $values;
    }

    public function getHeaderLine(string $name): string
    {
        return $this->incomingRequest->getHeaderLine($name);
    }

    public function withHeader(string $name, $value): MessageInterface
    {
        $this->incomingRequest->setHeader($name, $value);
        return $this;
    }

    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $this->incomingRequest->appendHeader($name, $value);
        return $this;
    }

    public function withoutHeader(string $name): MessageInterface
    {
        $this->incomingRequest->removeHeader($name);
        return $this;
    }

    public function getBody(): StreamInterface
    {
        return Stream::create($this->incomingRequest->getBody() ?? '');
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        $this->incomingRequest->setBody($body);
        return $this;
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
        $this->incomingRequest->setMethod($method);
        return $this;
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
        return $_SERVER;
    }

    public function getCookieParams(): array
    {
        return $_COOKIE;
    }

    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $_COOKIE[] = $cookies;
        return $this;
    }

    public function getQueryParams(): array
    {
        $array = [];
        parse_str($this->incomingRequest->getUri()->getQuery(), $array);
        return $array;
    }

    public function withQueryParams(array $query): ServerRequestInterface
    {
        $this->incomingRequest->getUri()->addQuery($query[0], $query[1]);
        return $this;
    }

    public function getUploadedFiles(): array
    {
        // Not necessary for implementation
        return [];
    }

    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        // Not necessary for implementation
        return $this;
    }

    public function getParsedBody(): null|array|object
    {
        $array = [];
        parse_str($this->incomingRequest->getBody(), $array);
        return $array;
    }

    public function withParsedBody($data): ServerRequestInterface
    {
        $this->incomingRequest->setBody(http_build_query($data));
        return $this;
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