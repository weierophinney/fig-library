<?php

namespace Zend\Http;

use Fig\Http\HttpResponse,
    Fig\Http\HttpHeaders;

class Response implements HttpResponse
{
    protected $content;
    protected $headers;

    public function __construct($content = '', $status = 200, $headers = null)
    {
        $this->setContent($content);

        if ($headers instanceof HttpHeaders) {
            $this->setHeaders($headers);
        } elseif (is_array($headers)) {
            $httpHeaders = $this->getHeaders();
            foreach ($headers as $type => $value) {
                $httpHeaders->addHeader($type, $value);
            }
            $headers = $httpHeaders;
        } else {
            $headers = $this->getHeaders();
        }
        $headers->setStatusCode($status);
    }


    /* Create text representation of response, including protocol, status and headers */
    public function __toString()
    {
        $headers  = $this->getHeaders();
        $response = sprintf(
            'HTTP/%s %d %s',
            $headers->getProtocolVersion(),
            $headers->getStatusCode(),
            $headers->getStatusMessage()
        );
        $response .= "\r\n";
        foreach ($headers as $header) {
            $response .= (string) $header;
        }
        $response .= "\r\n" . $this->getContent();
        return $response;
    }

    public function sendHeaders()
    {
        $this->getHeaders()->send();
    }

    public function sendContent()
    {
        echo $this->getContent();
    }

    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();
    }

    /* mutators and accessors */
    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = (string) $content;
        return $this;
    }

    public function getHeaders()
    {
        if (null === $this->headers) {
            $this->setHeaders(new Headers());
        }
        return $this->headers;
    }

    public function setHeaders(HttpHeaders $headers)
    {
        $this->headers = $headers;
        return $this;
    }
}
