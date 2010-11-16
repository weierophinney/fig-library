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
        if (!preg_match($this->allowedProtocolVersions, $version)) {
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
        if (!in_array($code, $this->allowedStatusCodes)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid status code provided: "%d"',
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

    public function addHeader(HttpHeader $header)
    {
    }

    public function get($type)
    {
    }

    public function send()
    {
    }

    public function sent()
    {
        return headers_sent();
    }

    public function isRedirect()
    {
    }

    /* Potential specialized mutators */
    public function expire()
    {
    }

    public function setRedirect($url, $code = 302)
    {
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
    public function hasVary()
    {
    }

    public function isCacheable()
    {
    }

    public function isClientError()
    {
    }

    public function isEmpty()
    {
    }

    public function isForbidden()
    {
    }

    public function isFresh()
    {
    }

    public function isInformational()
    {
    }

    public function isInvalid()
    {
    }

    public function isNotFound()
    {
    }

    public function isNotModified(HttpRequest $request)
    {
    }

    public function isOk()
    {
    }

    public function isServerError()
    {
    }

    public function isSuccessful()
    {
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

    public function getContent()
    {
    }

    public function getDate()
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
