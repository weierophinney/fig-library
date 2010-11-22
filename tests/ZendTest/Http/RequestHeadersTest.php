<?php

namespace ZendTest\Http;

use Zend\Http\RequestHeaders,
    Zend\Http\Header;

/**
 * Tests RequestHeaders functionality
 */
class RequestHeadersTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->headers = new RequestHeaders();
    }

    public function testMethodIsGetByDefault()
    {
        $this->assertEquals('GET', $this->headers->getMethod());
    }

    public function testUriIsEmptyByDefault()
    {
        $this->assertSame('', $this->headers->getUri());
    }

    public function testPassingInvalidHttpTokenToMethodRaisesException()
    {
        $this->setExpectedException('Zend\Http\Exception\InvalidArgumentException');
        $this->headers->setMethod('Foo bar');
    }

    public function testMethodIsMutable()
    {
        $this->headers->setMethod('POST');
        $this->assertEquals('POST', $this->headers->getMethod());
    }

    public function testSettingMethodNormalizesToUpperCase()
    {
        $this->headers->setMethod('delete');
        $this->assertEquals('DELETE', $this->headers->getMethod());
    }

    public function testUriIsMutable()
    {
        $this->headers->setUri('http://foo.bar/baz');
        $this->assertEquals('http://foo.bar/baz', $this->headers->getUri());
    }

    public function testCanParseHeadersFromString()
    {
        $string = file_get_contents(__DIR__ . '/_files/request_headers.txt');
        $this->headers->fromString($string);
        $this->assertEquals('PUT', $this->headers->getMethod());
        $this->assertEquals('/baz', $this->headers->getUri());
        $this->assertEquals('1.0', $this->headers->getProtocolVersion());

        $this->assertTrue($this->headers->has('Host'));
        $this->assertEquals('foo.bar', $this->headers->get('Host')->top()->getValue());
        $this->assertTrue($this->headers->has('Content-Type'));
        $this->assertEquals('text/html', $this->headers->get('Content-Type')->top()->getValue());
        $this->assertTrue($this->headers->has('X-Foo-Bar'));
        $this->assertRegexp('/^bar;\s*baz; bat;\s*$/', $this->headers->get('X-Foo-Bar')->top()->getValue());
        $this->assertTrue($this->headers->has('X-Baz-Bat'));
        $headers = $this->headers->get('X-Baz-Bat');
        $this->assertEquals(2, count($headers));
        $expected = array('foobar', 'bazbat');
        $test     = array();
        foreach ($headers as $header) {
            $test[] = $header->getValue();
        }
        $this->assertEquals($expected, $test);
        $this->assertFalse($this->headers->has('This'));
    }

    public function testParsingHeadersFromStringResetsStateOfCollection()
    {
        $this->headers->setMethod('delete');
        $this->headers->addHeaders(array(
            'X-Foo'            => 'bar',
            'X-Bar'            => 'baz',
            'Content-Encoding' => 'utf-8',
        ));
        $this->testCanParseHeadersFromString();
        $this->assertFalse($this->headers->has('X-Foo'));
        $this->assertFalse($this->headers->has('X-Bar'));
        $this->assertFalse($this->headers->has('Content-Encoding'));
    }

    public function testCanCastToString()
    {
        $this->headers->setMethod('post')
                      ->setUri('http://foo.bar/baz');
        $this->headers->addHeaders(array(
            'Content-Type' => 'text/html',
            'X-Emitted-By' => __CLASS__,
        ));
        $test = (string) $this->headers;
        $expected = "POST http://foo.bar/baz HTTP/1.1\r\nContent-Type: text/html\r\nX-Emitted-By: " . __CLASS__ . "\r\n";
        $this->assertEquals($expected, $test);
    }
}
