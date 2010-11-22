<?php

namespace ZendTest\Http;

use Zend\Http\Request,
    Zend\Http\RequestHeaders as Headers;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->request = new Request();
    }

    public function testSuperGlobalAccessorsAllReturnParametersObjectsByDefault()
    {
        foreach (array('query', 'post', 'cookie', 'file', 'server', 'env') as $type) {
            $collection = $this->request->{$type}();
            $this->assertType('Fig\Http\Parameters', $collection);
            $this->assertType('Zend\Http\Parameters', $collection);
        }
    }

    public function testHeadersInitiallyPopulatedFromServer()
    {
        $_SERVER['HTTP_HOST']           = 'foo.bar';
        $_SERVER['HTTP_X_REQUESTED_BY'] = __CLASS__;
        $_SERVER['REQUEST_METHOD']      = 'POST';
        $_SERVER['SERVER_PROTOCOL']     = 'HTTP/1.1';
        $_SERVER['CONTENT_TYPE']        = 'application/x-www-urlencoded';
        $_SERVER['HTTP_ACCEPT']         = 'text/xhtml+xml';
        $headers = $this->request->headers();
        $this->assertEquals('POST', $headers->getMethod());
        $this->assertEquals('foo.bar', $headers->get('host')->top()->getValue());
        $this->assertEquals(__CLASS__, $headers->get('X-Requested-By')->top()->getValue());
        $this->assertEquals('application/x-www-urlencoded', $headers->get('Content-Type')->top()->getValue());
        $this->assertEquals('text/xhtml+xml', $headers->get('Accept')->top()->getValue());
    }

    public function testRequestUriDeterminedFromServerByDefault()
    {
        $_SERVER['REQUEST_URI'] = '/foo.bar/baz';
        $this->assertEquals($_SERVER['REQUEST_URI'], $this->request->server('REQUEST_URI'));
        $this->assertEquals($_SERVER['REQUEST_URI'], $this->request->getRequestUri());
    }

    public function testSchemeIsHttpByDefault()
    {
        if (!empty($_SERVER['HTTPS'])) {
            $this->markTestSkipped();
        }

        $this->assertEquals('http', $this->request->getScheme());
    }

    public function testHttpsSchemeIndicatedWhenHttpsServerVariableIsNonEmpty()
    {
        $_SERVER['HTTPS'] = true;
        $this->assertEquals('https', $this->request->getScheme());
    }

    public function testHttpHostDeterminedFromServerByDefault()
    {
        $_SERVER['HTTP_HOST'] = 'foo.bar';
        $this->assertEquals('foo.bar', $this->request->server('HTTP_HOST'));
        $this->assertEquals('foo.bar', $this->request->getHttpHost());
    }

    public function testPortDeterminedFromServerByDefault()
    {
        $_SERVER['SERVER_PORT'] = 2152;
        $this->assertEquals(2152, $this->request->server('SERVER_PORT'));
        $this->assertEquals(2152, $this->request->getPort());
    }

    public function testPathInfoDeterminedFromServerByDefault()
    {
        $_SERVER['PATH_INFO'] = '/foo/bar.html';
        $this->assertEquals('/foo/bar.html', $this->request->server('PATH_INFO'));
        $this->assertEquals('/foo/bar.html', $this->request->getPathInfo());
    }

    public function testRequestMethodPulledFromHeaders()
    {
        $this->request->headers()->setMethod('PUT');
        $this->assertEquals('PUT', $this->request->getMethod());
    }

    public function testSetMethodProxiesToHeadersObject()
    {
        $this->request->setMethod('DELETE');
        $this->assertEquals('DELETE', $this->request->getMethod());
        $this->assertEquals('DELETE', $this->request->headers()->getMethod());
    }

    public function testSecureSettingBasedOnHttpsServerVar()
    {
        $this->request->server()->offsetSet('HTTPS', false);
        $this->assertFalse($this->request->isSecure());
        $this->request->server()->offsetSet('HTTPS', true);
        $this->assertTrue($this->request->isSecure());
    }

    public function testIsNotXmlHttpRequestByDefault()
    {
        $this->assertFalse($this->request->isXmlHttpRequest());
    }

    public function testIsXmlHttpRequestIfAppropriateHeaderFound()
    {
        $this->request->headers()->addHeader('X-Requested-With', 'XMLHttpRequest');
        $this->assertTrue($this->request->isXmlHttpRequest());
    }

    public function testIsNotFlashRequestByDefault()
    {
        $this->assertFalse($this->request->isFlashRequest());
    }

    public function testIsFlashRequestIfAppropriateHeaderFound()
    {
        $this->request->headers()->addHeader('User-Agent', 'Requested/with ; flash');
        $this->assertTrue($this->request->isFlashRequest());
    }

    public function testIsNotDeleteRequestByDefault()
    {
        $this->assertFalse($this->request->isDelete());
    }

    public function testIsGetRequestByDefault()
    {
        $this->assertTrue($this->request->isGet());
    }

    public function testIsNotHeadRequestByDefault()
    {
        $this->assertFalse($this->request->isHead());
    }

    public function testIsNotOptionsRequestByDefault()
    {
        $this->assertFalse($this->request->isOptions());
    }

    public function testIsNotPostRequestByDefault()
    {
        $this->assertFalse($this->request->isPost());
    }

    public function testIsNotPutRequestByDefault()
    {
        $this->assertFalse($this->request->isPut());
    }

    public function testIsDeleteRequestWhenMethodMatches()
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $this->assertTrue($this->request->isDelete());
    }

    public function testIsHeadRequestWhenMethodMatches()
    {
        $_SERVER['REQUEST_METHOD'] = 'HEAD';
        $this->assertTrue($this->request->isHead());
    }

    public function testIsOptionsRequestWhenMethodMatches()
    {
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $this->assertTrue($this->request->isOptions());
    }

    public function testIsPostRequestWhenMethodMatches()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue($this->request->isPost());
    }

    public function testIsPutRequestWhenMethodMatches()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->assertTrue($this->request->isPut());
    }

    public function testBodyIsNullByDefault()
    {
        $this->assertNull($this->request->body());
    }

    public function testBodyIsMutable()
    {
        $this->request->setRawBody('foo bar baz');
        $this->assertEquals('foo bar baz', $this->request->body());
    }

    public function testCanRepresentRequestAsString()
    {
        $headers = $this->request->headers();
        $headers->addHeaders(array(
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ));
        $headers->setMethod('POST')
                ->setUri('/json-endpoint');
        $body    = json_encode(array(
            'foo' => 'bar',
        ));
        $this->request->setRawBody($body);

        $test = (string) $this->request;
        $expected = "POST /json-endpoint HTTP/1.1\r\nContent-Type: application/json\r\nAccept: application/json\r\n\r\n{\"foo\":\"bar\"}";
        $this->assertEquals($expected, $test);
    }

    public function testCanPopulateFromString()
    {
        $request = file_get_contents(__DIR__ . '/_files/request_headers.txt');
        $this->request->fromString($request);

        $this->assertTrue($this->request->isPut());
        $this->assertEquals('/baz', $this->request->getRequestUri());
        $body = rtrim($this->request->body());
        $this->assertEquals('This is the request body.', $body);

        $headers = $this->request->headers();
        $this->assertTrue($headers->has('Host'));
        $this->assertEquals('foo.bar', $headers->get('Host')->top()->getValue());
        $this->assertTrue($headers->has('Content-Type'));
        $this->assertEquals('text/html', $headers->get('Content-Type')->top()->getValue());
        $this->assertTrue($headers->has('X-Foo-Bar'));
        $this->assertRegexp('/^bar;\s*baz; bat;\s*$/', $headers->get('X-Foo-Bar')->top()->getValue());
        $this->assertTrue($headers->has('X-Baz-Bat'));

        $headers = $headers->get('X-Baz-Bat');
        $this->assertEquals(2, count($headers));
        $expected = array('foobar', 'bazbat');
        $test     = array();
        foreach ($headers as $header) {
            $test[] = $header->getValue();
        }
        $this->assertEquals($expected, $test);
    }
}
