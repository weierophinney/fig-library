<?php

namespace Fig\Http;

use Iterator,
    ArrayAccess,
    Countable;

/*
 * In most cases, extend SplQueue, and then override where necessary
 */
interface HttpHeaders extends Iterator, ArrayAccess, Countable
{
    /* 
     * General mutators and accessors 
     *
     * These are items that are technically part of the response headers, but
     * not individual headers themselves.
     */
    public function getProtocolVersion(); // HTTP 1.0, 1.1
    public function setProtocolVersion($version);

    /* 
     * Adding headers 
     *
     * Also: requires overriding push, unshift to ensure values are of correct 
     * type.
     *
     * Typically, $header will be of type HttpHeader, but this allows addHeader() 
     * to operate as a factory. Suggestion is to allow HttpHeader objects, arrays,
     * or all 3 arguments.
     */
    public function addHeader($header, $content = null, $replace = false);

    /**
     * Allow adding multiple headers at once
     *
     * Implementation can vary -- could be key/value pairs, array of HttpHeader 
     * objects, array of arrays, etc -- or combination thereof.
     *
     * @param mixed $headers
     * @return void
     */
    public function addHeaders($headers);

    /*
     * Retrieve named header; returns either false or a queue of headers.
     * has() tests for headers
     */
    public function get($type);
    public function has($type);

    /**
     * Representation of headers as string
     */
    public function __toString();

    /**
     * Create headers from string (request/response document)
     *
     * @param mixed $string 
     * @return void
     */
    public function fromString($string);

    /* Methods occurring below here need to be discussed */

    /* Potential specialized mutators * /
    public function setAccept($string);
    public function setAcceptCharset($string);
    public function setAcceptEncoding($string);
    public function setAcceptLanguage($string);
    public function setAuthorization($credentials);
    public function setExpect($string);
    public function setFrom($string);
    public function setHost($string);
    public function setIfMatch($string);
    public function setIfModifiedSince($string);
    public function setIfNoneMatch($string);
    public function setIfRange($string);
    public function setIfUnmodifiedSince($string);
    public function setMaxForwards($string);
    public function setProxyAuthorization($string);
    public function setRange($string);
    public function setReferer($string);
    public function setTE($string);
    public function setUserAgent($string);

    /* Potential specialized accessors * /
    public function getAccept();
    public function getAcceptCharset();
    public function getAcceptEncoding();
    public function getAcceptLanguage();
    public function getAuthorization();
    public function getExpect();
    public function getFrom();
    public function getHost();
    public function getIfMatch();
    public function getIfModifiedSince();
    public function getIfNoneMatch();
    public function getIfRange();
    public function getIfUnmodifiedSince();
    public function getMaxForwards();
    public function getProxyAuthorization();
    public function getRange();
    public function getReferer();
    public function getTE();
    public function getUserAgent();
     */
}
