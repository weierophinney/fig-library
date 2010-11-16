<?php

namespace ZendTest\Http;

use Zend\Http\Headers;

class HeadersTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->headers = new Headers();
    }

    public function testProtocolVersionIs1Dot1ByDefault()
    {
        $this->assertEquals('1.1', $this->headers->getProtocolVersion());
    }

    public function testStatusCodeIs200ByDefault()
    {
        $this->assertEquals(200, $this->headers->getStatusCode());
    }

    public function testStatusMessageIsOKByDefault()
    {
        $this->assertEquals('OK', $this->headers->getStatusMessage());
    }
}
