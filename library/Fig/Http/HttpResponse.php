<?php

namespace Fig\Http;

interface HttpResponse
{
    public function __construct($content = '', $status = 200, $headers = null);

    /* Create text representation of response, including protocol, status and headers */
    public function __toString();

    public function sendHeaders();
    public function sendContent();
    public function send(); // send both headers and content

    /* mutators and accessors */
    public function getContent();
    public function setContent($content);
    public function getHeaders();
    public function setHeaders(HttpHeaders $headers);
}
