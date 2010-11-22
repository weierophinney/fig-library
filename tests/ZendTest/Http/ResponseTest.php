<?php

namespace ZendTest\Http;

use Zend\Http\Response,
    Zend\Http\ResponseHeaders as Headers;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->response = new Response();
    }

    public function testContentIsEmptyByDefault()
    {
        $this->assertEquals('', $this->response->getContent());
    }

    public function testStatusIs200ByDefault()
    {
        $this->assertEquals(200, $this->response->getHeaders()->getStatusCode());
    }

    public function testNoHeadersByDefault()
    {
        $this->assertEquals(0, count($this->response->getHeaders()));
    }

    public function testContentIsMutable()
    {
        $this->response->setContent('foo');
        $this->assertEquals('foo', $this->response->getContent());
    }

    public function testHeadersAreMutable()
    {
        $headers = new Headers();
        $this->response->setHeaders($headers);
        $this->assertSame($headers, $this->response->getHeaders());
    }

    public function testCanPassContentStatusAndHeadersToConstructor()
    {
        $content  = 'foo';
        $headers  = new Headers();
        $status   = 401;
        $response = new Response($content, $status, $headers);

        $this->assertEquals($content, $response->getContent());
        $this->assertEquals($status, $response->getHeaders()->getStatusCode());
        $this->assertSame($headers, $response->getHeaders());
    }
       
    public function testSendHeadersEmitsHeaders()
    {
        $headers = new Headers();
        $headers->addHeader('X-Foo-Bar', 'baz');
        $this->response->setHeaders($headers);
        $this->response->sendHeaders();
        $test = xdebug_get_headers();
        $this->assertContains('X-Foo-Bar: baz', $test);
    }

    public function testSendContentEmitsContent()
    {
        $this->response->setContent('foo');
        ob_start();
        $this->response->sendContent();
        $test = ob_get_clean();
        $this->assertEquals('foo', $test);
    }

    public function testSendEmitsBothContentsAndHeaders()
    {
        $headers = new Headers();
        $headers->addHeader('X-Foo-Bar', 'baz');
        $this->response->setHeaders($headers);
        $this->response->setContent('foo');

        ob_start();
        $this->response->send();
        $test = ob_get_clean();

        $this->assertEquals('foo', $test);
        $test = xdebug_get_headers();
        $this->assertContains('X-Foo-Bar: baz', $test);
    }

    public function testCanSerializeEntireResponseAsString()
    {
        $headers = new Headers();
        $headers->addHeader('X-Foo-Bar', 'baz');
        $this->response->setHeaders($headers);
        $this->response->setContent('foo');
        $test = (string) $this->response;

        $this->assertContains("X-Foo-Bar: baz\r\n", $test);
        $this->assertContains("\r\n\r\nfoo", $test);
    }

    public function testCanParseResponseFromString()
    {
        $response = file_get_contents(__DIR__ . '/_files/response_headers.txt');
        $this->response->fromString($response);
        $this->assertEquals('This is the response body.', trim($this->response->getContent()));
        $headers = $this->response->getHeaders();
        $this->assertEquals(302, $headers->getStatusCode());
        $this->assertEquals('Temporarily Moved', $headers->getStatusMessage());
        $this->assertTrue($headers->has('Content-Type'));
        $this->assertTrue($headers->has('X-Baz-Bat'));
        $this->assertTrue($headers->has('X-Foo-Bar'));
        $this->assertTrue($headers->has('X-Baz-Bat'));
        $this->assertTrue($headers->has('Location'));
    }
}
