<?php

namespace Zend\Http;

use Fig\Http\HttpRequestHeaders;

class RequestHeaders extends Headers implements HttpRequestHeaders
{
    const PATTERN_REQUEST_LINE = "/^%token (?<uri>[^ ]+) HTTP\/(?<version>\d+(\.\d+)?)$/";

    protected $method = 'GET';
    protected $uri    = '';

    /**
     * Get request method
     * 
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get request URI
     * 
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set request method
     *
     * Normalizes to upper case.
     * 
     * @param  string $method 
     * @return RequestHeaders
     * @throws Exception\InvalidArgumentException on invalid method token
     */
    public function setMethod($method)
    {
        if (!preg_match('/^' . self::PATTERN_TOKEN . '$/', $method)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Request method must be a valid HTTP token; "%s" provided',
                $method
            ));
        }
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * Set request URI
     *
     * @todo Validate URI?
     * @param  string $uri 
     * @return RequestHeaders
     */
    public function setUri($uri)
    {
        $this->uri = (string) $uri;
        return $this;
    }

    /**
     * Populate from request body
     * 
     * @param  string $string 
     * @return RequestHeaders
     */
    public function fromString($string)
    {
        $pattern = str_replace('%token', self::PATTERN_TOKEN, self::PATTERN_REQUEST_LINE);
        $headers = preg_split("/\r\n/", $string, 2);
        if (!preg_match($pattern, $headers[0], $matches)) {
            return $this;
        }
        $method          = $matches['token'];
        $uri             = $matches['uri'];
        $protocolVersion = $matches['version'];
        $this->setMethod($method)
             ->setUri($uri)
             ->setProtocolVersion($protocolVersion);

        // If we have more headers, parse them
        if (count($headers) == 2) {
            parent::fromString($headers[1]);
        }

        return $this;
    }

    /**
     * String representation of request
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->renderRequestLine() . parent::__toString();
    }

    /**
     * Render the "Request-Line" header
     * 
     * @return string
     */
    public function renderRequestLine()
    {
        return sprintf(
            "%s %s HTTP/%s\r\n",
            $this->getMethod(),
            $this->getUri(),
            $this->getProtocolVersion()
        );
    }
}
