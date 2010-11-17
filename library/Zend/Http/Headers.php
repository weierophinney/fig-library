<?php

namespace Zend\Http;

use Fig\Http\HttpHeader,
    Fig\Http\HttpHeaders,
    Fig\Http\HttpRequest,
    SplQueue;

class Headers extends SplQueue implements HttpHeaders
{
    protected $allowedProtocolVersions = '/^\d+\.\d+$/';
    protected $allowedStatusCodes = array(
        100,
        101,
        200,
        201,
        202,
        203,
        204,
        205,
        206,
        300,
        301,
        302,
        303,
        304,
        305,
        306,
        307,
        400,
        401,
        402,
        403,
        404,
        405,
        406,
        407,
        408,
        409,
        410,
        411,
        412,
        413,
        414,
        415,
        416,
        417,
        500,
        501,
        502,
        503,
        504,
        505,
    );

    /**
     * @var array Status codes indicating empty response
     */
    protected $emptyCodes = array(201, 204, 304);

    /**
     * Set of headers sorted by type
     *
     * Used to allow fetching headers by type.
     *
     * @var array Array of SplQueue instances
     */
    protected $headers = array();

    protected $isSent = false;

    protected $protocolVersion = '1.1';
    protected $statusCode      = 200;
    protected $statusMessage   = 'OK';

    /**
     * Retrieve HTTP protocol version
     * 
     * @return string|float
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * Set HTTP protocol version
     * 
     * @param  string|float $version 
     * @return Headers
     */
    public function setProtocolVersion($version)
    {
        if (!is_scalar($version) || !preg_match($this->allowedProtocolVersions, $version)) {
            $version = is_scalar($version) ? (string) $version : gettype($version);
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid protocol version: "%s"',
                (string) $version
            ));
        }
        $this->protocolVersion = (string) $version;
        return $this;
    }

    /**
     * Retrieve HTTP status code
     * 
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Get HTTP status message
     * 
     * @return string
     */
    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

    /**
     * Set HTTP status code and (optionally) message
     * 
     * @param  string|float $code 
     * @param  null|string $text 
     * @return Headers
     */
    public function setStatusCode($code, $text = null)
    {
        if (!is_numeric($code) || !in_array($code, $this->allowedStatusCodes)) {
            $code = is_scalar($code) ? $code : gettype($code);
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid status code provided: "%s"',
                $code
            ));
        }
        $this->statusCode = $code;
        if (!is_string($text)) {
            // Not a string? Set it to an empty string
            $this->statusMessage = '';
        } else {
            // Strip any lineending characters before storing
            $text = preg_replace("/(\r|\n)/", '', $text);
            $this->statusMessage = $text;
        }
        return $this;
    }

    /**
     * Add a header onto the queue
     * 
     * @param  HttpHeader $header 
     * @return Headers
     */
    public function addHeader(HttpHeader $header)
    {
        $this->push($header);
        return $this;
    }

    /**
     * Push a header onto the queue
     * 
     * @param  Header $value 
     * @return void
     * @throws Exception\InvalidArgumentException when non-Header object provided
     */
    public function push($value)
    {
        if (!$value instanceof HttpHeader) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Headers may only aggregate Fig\Http\HttpHeader objects; received %s',
                (is_object($value) ? get_class($value) : gettype($value))
            ));
        }

        $type = strtolower($value->getType());
        if (!array_key_exists($type, $this->headers)) {
            $this->headers[$type] = new SplQueue();
        }
        $this->headers[$type]->push($value);

        return parent::push($value);
    }

    /**
     * Unshift a header onto the queue
     * 
     * @param  Header $value 
     * @return void
     * @throws Exception\InvalidArgumentException when non-Header object provided
     */
    public function unshift($value)
    {
        if (!$value instanceof HttpHeader) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Headers may only aggregate Fig\Http\HttpHeader objects; received %s',
                (is_object($value) ? get_class($value) : gettype($value))
            ));
        }

        $type = strtolower($value->getType());
        if (!array_key_exists($type, $this->headers)) {
            $this->headers[$type] = new SplQueue();
        }
        $this->headers[$type]->unshift($value);

        return parent::unshift($value);
    }

    /**
     * Get all headers of a certain name/type
     * 
     * @param  string $type 
     * @return false|SplQueue
     */
    public function get($type)
    {
        $type = strtolower($type);
        if (array_key_exists($type, $this->headers)) {
            return $this->headers[$type];
        }
        return false;
    }

    /**
     * Send headers
     *
     * Builds and sends a status header, based on protocol version, status code
     * and status message, and loops through and sends each header aggregated.
     * 
     * @return void
     */
    public function send()
    {
        // Build and send status header
        $status = sprintf(
            'HTTP/%s %d %s',
            $this->getProtocolVersion(),
            $this->getStatusCode(),
            $this->getStatusMessage()
        );
        header($status);

        // Now loop through all headers and send
        foreach ($this as $header) {
            $header->send();
        }
        $this->isSent = true;
    }

    /**
     * Are headers sent?
     *
     * Returns true for either of the following situations:
     *
     * - send() has been called, and the isSent flag set to true
     * - headers_sent() returns true
     * 
     * @return bool
     */
    public function sent()
    {
        return ($this->isSent || headers_sent());
    }

    /**
     * Is this a redirect header?
     *
     * Returns true if we have a 3xx status code, or if a Location header is
     * present.
     * 
     * @return bool
     */
    public function isRedirect()
    {
        $code    = $this->getStatusCode();
        $headers = $this->get('Location');
        return (((300 <= $code) && (400 > $code)) 
                || ($headers && count($headers)));
    }

    /* Potential specialized mutators */
    public function expire()
    {
    }

    /**
     * Add a redirect header
     *
     * Creates and appends a redirect header. If a non-empty status code is 
     * given, it is passed to {@link setStatusCode()}.
     * 
     * @param  string $url 
     * @param  null|int $code 
     * @return Headers
     */
    public function setRedirect($url, $code = 302)
    {
        $this->addHeader(new Header('Location', $url, true));
        if (!empty($code)) {
            $this->setStatusCode($code);
        }
        return $this;
    }

    public function setClientTtl($seconds)
    {
    }

    public function setEtag($etag = null, $weak = false)
    {
    }

    public function setExpires($date = null)
    {
    }

    public function setLastModified($date = null)
    {
    }

    public function setMaxAge($value)
    {
    }

    public function setNotModified()
    {
    }

    public function setPrivate($value)
    {
    }

    public function setSharedMaxAge($value)
    {
    }

    public function setTtl($seconds)
    {
    }

    public function setVary($headers, $replace = true)
    {
    }


    /* Potential specialized conditionals */

    /**
     * Do we have a Vary header?
     * 
     * @return bool
     */
    public function hasVary()
    {
        $headers = $this->get('Vary');
        return ($headers instanceof SplQueue);
    }

    public function isCacheable()
    {
    }

    /**
     * Does the status code indicate a client error?
     * 
     * @return bool
     */
    public function isClientError()
    {
        $code = $this->getStatusCode();
        return ($code < 500 && $code >= 400);
    }

    /**
     * Does the status code indicate an empty response?
     * 
     * @return bool
     */
    public function isEmpty()
    {
        return in_array($this->getStatusCode(), $this->emptyCodes);
    }

    /**
     * Is the request forbidden due to ACLs?
     * 
     * @return bool
     */
    public function isForbidden()
    {
        return (403 == $this->getStatusCode());
    }

    public function isFresh()
    {
    }

    /**
     * Is the current status "informational"?
     * 
     * @return bool
     */
    public function isInformational()
    {
        $code = $this->getStatusCode();
        return ($code >= 100 && $code < 200);
    }

    /**
     * Is the status code invalid?
     *
     * Because we validate status codes, this can never return true.
     * 
     * @return false
     */
    public function isInvalid()
    {
        return false;
    }

    /**
     * Does the status code indicate the resource is not found?
     * 
     * @return bool
     */
    public function isNotFound()
    {
        return (404 === $this->getStatusCode());
    }

    public function isNotModified(HttpRequest $request)
    {
    }

    /**
     * Do we have a normal, OK response?
     * 
     * @return bool
     */
    public function isOk()
    {
        return (200 === $this->getStatusCode());
    }

    /**
     * Does the status code reflect a server error?
     * 
     * @return bool
     */
    public function isServerError()
    {
        $code = $this->getStatusCode();
        return (500 <= $code && 600 > $code);
    }

    /**
     * Was the response successful?
     * 
     * @return bool
     */
    public function isSuccessful()
    {
        $code = $this->getStatusCode();
        return (200 <= $code && 300 > $code);
    }

    public function isValidateable()
    {
    }

    public function mustRevalidate()
    {
    }


    /* Potential specialized accessors */
    public function getAge() 
    {
    }

    public function getEtag()
    {
    }

    public function getExpires()
    {
    }

    public function getLastModified()
    {
    }

    public function getMaxAge()
    {
    }

    public function getTtl()
    {
    }

    public function getVary()
    {
    }
}
