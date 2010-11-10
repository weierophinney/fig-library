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
    public function getStatusCode(); // 200, 301, etc.
    public function getStatusMessage(); 
    public function setStatusCode($code, $text = null);

    /* 
     * Adding headers 
     *
     * Also: requires overriding push, unshift to ensure values are of correct 
     * type.
     */
    public function addHeader(Header $header);

    /*
     * Retrieve named header; returns either a single header or an array of headers
     */
    public function get($type);

    /* Sending headers, and testing sent status */
    public function send();
    public function sent(); // return boolean

    /* Testing header status */
    public function isRedirect();

    /* Potential specialized mutators */
    public function expire();
    public function setRedirect($url, $code = 302);
    public function setClientTtl($seconds);
    public function setEtag($etag = null, $weak = false);
    public function setExpires($date = null);
    public function setLastModified($date = null);
    public function setMaxAge($value);
    public function setNotModified();
    public function setPrivate($value);
    public function setSharedMaxAge($value);
    public function setTtl($seconds);
    public function setVary($headers, $replace = true);

    /* Potential specialized conditionals */
    public function hasVary();
    public function isCacheable();
    public function isClientError();
    public function isEmpty();
    public function isForbidden();
    public function isFresh();
    public function isInformational();
    public function isInvalid();
    public function isNotFound();
    public function isNotModified(Request $request);
    public function isOk();
    public function isServerError();
    public function isSuccessful();
    public function isValidateable();
    public function mustRevalidate();

    /* Potential specialized accessors */
    public function getAge() ;
    public function getContent();
    public function getDate();
    public function getEtag();
    public function getExpires();
    public function getLastModified();
    public function getMaxAge();
    public function getTtl();
    public function getVary();
}
