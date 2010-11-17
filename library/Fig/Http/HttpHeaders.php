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
     *
     * Typically, $header will be of type HttpHeader, but this allows addHeader() 
     * to operate as a factory. Suggestion is to allow HttpHeader objects, arrays,
     * or all 3 arguments.
     */
    public function addHeader($header, $content = null, $replace = false);
    public function setRedirect($url, $code = 302);

    /*
     * Retrieve named header; returns either false or a queue of headers.
     * has() tests for headers
     */
    public function get($type);
    public function has($type);

    /* Sending headers, and testing sent status */
    public function send(); // actually send headers
    public function sent(); // return boolean headers sent status

    /* Testing header status */
    public function hasVary();          // Vary header present?
    public function isRedirect();       // 3XX status and/or Location header?
    public function isClientError();    // 4XX status?
    public function isEmpty();          // 201, 204, or 304 status?
    public function isForbidden();      // 403 status?
    public function isInformational();  // 1XX status?
    public function isInvalid();        // <100 or >= 600 status?
    public function isNotFound();       // 404 status?
    public function isOk();             // 200 status?
    public function isServerError();    // 5XX status?
    public function isSuccessful();     // 2XX status?

    /* Those headers occurring below here need to be discussed */

    /* Potential specialized mutators * /
    public function expire();
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

    /* Potential specialized conditionals * /
    public function isCacheable();
    public function isFresh();
    public function isNotModified(HttpRequest $request);
    public function isValidateable();
    public function mustRevalidate();

    /* Potential specialized accessors * /
    public function getAge() ;
    public function getEtag();
    public function getExpires();
    public function getLastModified();
    public function getMaxAge();
    public function getTtl();
    public function getVary();
     */
}
