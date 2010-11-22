<?php

namespace ZendTest\Http;

use Zend\Http\ResponseHeaders,
    Zend\Http\Header;

/**
 * Tests both ResponseHeaders as well as base Headers functionality
 */
class ResponseHeadersTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->headers = new ResponseHeaders();
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

    public function invalidVersions()
    {
        return array(
            array(true),
            array(1),
            array('1'),
            array(array('1')),
            array(new \stdClass()),
        );
    }

    /**
     * @dataProvider invalidVersions
     */
    public function testRaisesExceptionOnInvalidProtocolVersion($version)
    {
        $this->setExpectedException('Zend\Http\Exception\InvalidArgumentException');
        $this->headers->setProtocolVersion($version);
    }

    public function testProtocolVersionIsMutableWhenProvidedValidArgument()
    {
        $this->headers->setProtocolVersion('12.3');
        $this->assertEquals('12.3', $this->headers->getProtocolVersion());
    }

    public function invalidCodes()
    {
        return array(
            array(true),
            array(1),
            array('1001'),
            array(array('1')),
            array(new \stdClass()),
        );
    }

    /**
     * @dataProvider invalidCodes
     */
    public function testRaisesExceptionOnInvalidStatusCodes($code)
    {
        $this->setExpectedException('Zend\Http\Exception\InvalidArgumentException');
        $this->headers->setStatusCode($code);
    }

    public function testStatusCodeIsMutableWhenProvidedValidArgument()
    {
        $this->headers->setStatusCode(201);
        $this->assertEquals(201, $this->headers->getStatusCode());
    }

    public function invalidMessages()
    {
        return array(
            array(true),
            array(1),
            array(1.0),
            array(array(1)),
            array(new \stdClass()),
        );
    }

    /**
     * @dataProvider invalidMessages
     */
    public function testPassingNonStringMessageWhenSettingStatusCodeSetsBlankMessage($message)
    {
        $this->headers->setStatusCode(200, $message);
        $this->assertEquals('', $this->headers->getStatusMessage());
    }

    public function testStatusMessageIsMutableWithValidStrings()
    {
        $this->headers->setStatusCode(200, 'this is a message');
        $this->assertEquals('this is a message', $this->headers->getStatusMessage());
    }

    public function testStripsNewlinesFromProvidedStatusMessages()
    {
        $this->headers->setStatusCode(200, "This\ris\r\na\nmessage");
        $this->assertEquals('Thisisamessage', $this->headers->getStatusMessage());
    }

    public function testHasNoHeadersByDefault()
    {
        $this->assertEquals(0, count($this->headers));
    }

    public function testCanAddHeaders()
    {
        $header = new Header('X-Foo-Bar', 'baz');
        $this->headers->addHeader($header);
        $this->assertEquals(1, count($this->headers));
        foreach ($this->headers as $test) {
        }
        $this->assertSame($header, $test);
    }

    public function testCanAddHeadersViaScalarArguments()
    {
        $this->headers->addHeader('X-Foo-Bar', 'baz', true);
        $this->assertTrue($this->headers->has('X-Foo-Bar'));
        $headers = $this->headers->get('X-Foo-Bar');
        foreach ($headers as $header) {}
        $this->assertEquals('baz', $header->getValue());
        $this->assertTrue($header->replace());
    }

    public function testCanAddHeadersUsingArrays()
    {
        $this->headers->addHeader(array(
            'type'    => 'X-Foo-Bar',
            'value'   => 'baz',
            'replace' => true,
        ));
        $this->assertTrue($this->headers->has('X-Foo-Bar'));
        $headers = $this->headers->get('X-Foo-Bar');
        foreach ($headers as $header) {}
        $this->assertEquals('baz', $header->getValue());
        $this->assertTrue($header->replace());
    }

    public function testPushingNonHeadersOntoObjectRaisesException()
    {
        $this->setExpectedException('Zend\Http\Exception\InvalidArgumentException');
        $this->headers->push(1);
    }

    public function testUnshiftingNonHeadersOntoObjectRaisesException()
    {
        $this->setExpectedException('Zend\Http\Exception\InvalidArgumentException');
        $this->headers->unshift(1);
    }

    public function testCanRetrieveHeadersByName()
    {
        $header = new Header('X-Foo-Bar', 'baz');
        $this->headers->addHeader($header);
        $headers = $this->headers->get('X-Foo-Bar');
        $this->assertType('SplQueue', $headers);
        $this->assertEquals(1, count($headers));
        foreach ($headers as $test) {
        }
        $this->assertSame($header, $test);
    }

    public function testGetReturnsFalseIfNoHeaderOfTypeIsRegistered()
    {
        $this->assertFalse($this->headers->get('foo'));
    }

    public function testUnshiftPrependsHeaderOfSameTypeToNamedHeaders()
    {
        $header1 = new Header('X-Foo-Bar', 'baz');
        $header2 = new Header('X-Foo-Bar', 'bat');

        $this->headers->addHeader($header1);
        $this->headers->unshift($header2);

        $headers = $this->headers->get('X-Foo-Bar');
        $list    = array();
        foreach ($headers as $h) {
            $list[] = $h;
        }
        $expected = array($header2, $header1);
        $this->assertSame($expected, $list);
    }

    public function testSendSendsProtocolVersion()
    {
        // Cannot test this against XDebug's xdebug_get_headers() at this time,
        // as implicit headers are not aggregated.
        $this->markTestSkipped('Cannot test at this time');
        $this->headers->send();
        $this->assertTrue($this->headers->sent());
        $headerString = $this->headers->__toString();
        $this->assertContains('HTTP/1.1 200 OK', $headerString);
    }

    public function testSendSendsHeadersInOrder()
    {
        $header1 = new Header('X-Foo-Bar', 'baz', true);
        $header2 = new Header('X-Foo-Bar', 'bat');
        $header3 = new Header('X-Baz-Bat', 'bar');

        $this->headers->addHeader($header1);
        $this->headers->unshift($header2);
        $this->headers->push($header3);

        $this->headers->send();
        $expected = array(
            trim($header2->__toString()),
            trim($header1->__toString()),
            trim($header3->__toString()),
        );

        $headers = xdebug_get_headers();
        if (preg_match('/^Set-Cookie:\s+XDEBUG_SESSION/', $headers[0])) {
            // Strip XDebug cookie header off
            array_shift($headers);
        }
        $this->assertEquals($expected, $headers);
    }

    public function testSentFlagFalseByDefault()
    {
        $this->assertFalse($this->headers->sent());
    }

    public function testSentFlagTrueAfterSendingHeaders()
    {
        $this->headers->addHeader(new Header('X-Foo-Bar', 'baz'));
        $this->headers->send();
        $this->assertTrue($this->headers->sent());
    }

    public function testIsRedirectReturnsFalseByDefault()
    {
        $this->assertFalse($this->headers->isRedirect());
    }

    public function testIsRedirectIfStatusCodeIn300Series()
    {
        $this->headers->setStatusCode(301);
        $this->assertTrue($this->headers->isRedirect());
    }

    public function testIsRedirectIfLocationHeaderPresent()
    {
        $this->headers->setStatusCode(201)
                      ->addHeader(new Header('Location', '/foo'));
        $this->assertTrue($this->headers->isRedirect());
    }

    /**
     * Thinking this may need some thinking. SF2 allows passing a date 
     * object (or null to clear), and will fall back on determining the current
     * request age based on a number of factors
     *
     * @group disable
     */
    public function testCallingExpireSetsExpireHeader()
    {
        $this->headers->expire();
        $headers = $this->headers->get('Expire');
        $this->assertType('SplQueue', $headers);
        $this->assertEquals(1, count($headers));
    }

    public function testSetRedirectActsAsHeaderFactory()
    {
        $this->headers->setRedirect('/foo');
        $code    = $this->headers->getStatusCode();
        $this->assertEquals(302, $code);
        $headers = $this->headers->get('Location');
        $this->assertType('SplQueue', $headers);
        $this->assertEquals(1, count($headers));
    }

    public function testSetRedirectDoesNotResetStatusCodeIfEmptyValueProvided()
    {
        $original = $this->headers->getStatusCode();
        $this->headers->setRedirect('/foo', false);
        $code = $this->headers->getStatusCode();
        $this->assertEquals($original, $code);
    }

    public function testSetRedirectSetsStatusCodeWhenProvided()
    {
        $this->headers->setRedirect('/foo', 201);
        $code = $this->headers->getStatusCode();
        $this->assertEquals(201, $code);
    }

    public function testHasVaryReturnsFalseByDefault()
    {
        $this->assertFalse($this->headers->hasVary());
    }

    public function testHasVaryReturnsTrueWhenVaryHeaderPresent()
    {
        $this->headers->addHeader(new Header('Vary', 'text/plain'));
        $this->assertTrue($this->headers->hasVary());
    }

    public function testIsNotClientErrorByDefault()
    {
        $this->assertFalse($this->headers->isClientError());
    }

    public function test400StatusCodesReturnClientError()
    {
        $this->headers->setStatusCode(401);
        $this->assertTrue($this->headers->isClientError());
    }

    public function nonClientErrorCodes()
    {
        return array(
            array(100),
            array(200),
            array(301),
            array(500),
        );
    }

    /**
     * @dataProvider nonClientErrorCodes
     */
    public function testNon400StatusCodesDoNotReturnClientError($code)
    {
        $this->headers->setStatusCode($code);
        $this->assertFalse($this->headers->isClientError());
    }

    public function testIsNotEmptyByDefault()
    {
        $this->assertFalse($this->headers->isEmpty());
    }

    public function emptyStatusCodes()
    {
        return array(
            array(201),
            array(204),
            array(304),
        );
    }

    /**
     * @dataProvider emptyStatusCodes
     */
    public function testReturnsEmptyForAppropriateStatusCodes($code)
    {
        $this->headers->setStatusCode($code);
        $this->assertTrue($this->headers->isEmpty());
    }

    public function nonEmptyStatusCodes()
    {
        return array(
            array(100),
            array(200),
            array(300),
            array(400),
            array(500),
        );
    }

    /**
     * @dataProvider nonEmptyStatusCodes
     */
    public function testReturnsFalseForNonEmptyStatusCodes($code)
    {
        $this->headers->setStatusCode($code);
        $this->assertFalse($this->headers->isEmpty());
    }

    public function testNotForbiddenByDefault()
    {
        $this->assertFalse($this->headers->isForbidden());
    }

    public function testReturnsFalseForForbiddenStatusCodes()
    {
        $this->headers->setStatusCode(403);
        $this->assertTrue($this->headers->isForbidden());
    }

    /**
     * @dataProvider nonEmptyStatusCodes
     */
    public function testReturnsTrueForNonForbiddenStatusCodes($code)
    {
        $this->headers->setStatusCode($code);
        $this->assertFalse($this->headers->isForbidden());
    }

    public function testStatusIsNotInformationalByDefault()
    {
        $this->assertFalse($this->headers->isInformational());
    }

    public function informationalCodes()
    {
        return array(
            array(100),
            array(101),
        );
    }

    /**
     * @dataProvider informationalCodes
     */
    public function test100StatusesAreInformational($code)
    {
        $this->headers->setStatusCode($code);
        $this->assertTrue($this->headers->isInformational());
    }

    public function nonInformationalCodes()
    {
        return array(
            array(200),
            array(301),
            array(401),
            array(500),
        );
    }

    /**
     * @dataProvider nonInformationalCodes
     */
    public function testNon100StatusesAreNotInformational($code)
    {
        $this->headers->setStatusCode($code);
        $this->assertFalse($this->headers->isInformational());
    }

    public function testNotInvalidByDefault()
    {
        $this->assertFalse($this->headers->isInvalid());
    }

    public function testCannotSetInvalidStatusLessThan100()
    {
        $this->setExpectedException('Zend\Http\Exception\InvalidArgumentException');
        $this->headers->setStatusCode(1);
    }

    public function testCannotSetInvalidStatusGreaterThan5xx()
    {
        $this->setExpectedException('Zend\Http\Exception\InvalidArgumentException');
        $this->headers->setStatusCode(600);
    }

    public function testIsFoundByDefault()
    {
        $this->assertFalse($this->headers->isNotFound());
    }

    public function test404SetsNotFoundStatus()
    {
        $this->headers->setStatusCode(404);
        $this->assertTrue($this->headers->isNotFound());
    }

    /**
     * @dataProvider nonEmptyStatusCodes
     */
    public function testNon404StatusMarksAsFound($code)
    {
        $this->headers->setStatusCode($code);
        $this->assertFalse($this->headers->isNotFound());
    }

    public function testIsOkByDefault()
    {
        $this->assertTrue($this->headers->isOk());
    }

    /**
     * @dataProvider nonInformationalCodes
     */
    public function testNon200StatusIsNotOk($code)
    {
        $code++;
        $this->headers->setStatusCode($code);
        $this->assertFalse($this->headers->isOk());
    }

    public function testIsNotServerErrorByDefault()
    {
        $this->assertFalse($this->headers->isServerError());
    }

    public function serverErrorCodes()
    {
        return array(
            array(500),
            array(501),
            array(502),
            array(503),
            array(504),
            array(505),
        );
    }

    /**
     * @dataProvider serverErrorCodes
     */
    public function test5xxStatusCodesReflectServerError($code)
    {
        $this->headers->setStatusCode($code);
        $this->assertTrue($this->headers->isServerError());
    }

    public function nonServerErrorCodes()
    {
        return array(
            array(100),
            array(200),
            array(300),
            array(400),
        );
    }

    /**
     * @dataProvider nonServerErrorCodes
     */
    public function testNon5xxStatusCodesAreNotServerErrors($code)
    {
        $this->headers->setStatusCode($code);
        $this->assertFalse($this->headers->isServerError());
    }

    public function testIsSuccessfulByDefault()
    {
        $this->assertTrue($this->headers->isSuccessful());
    }

    public function successfulCodes()
    {
        return array(
            array(200),
            array(201),
            array(202),
            array(203),
            array(204),
        );
    }

    /**
     * @dataProvider successfulCodes
     */
    public function test2xxStatusCodesAreSuccessful($code)
    {
        $this->headers->setStatusCode($code);
        $this->assertTrue($this->headers->isSuccessful());
    }

    public function unsuccessfulCodes()
    {
        return array(
            array(100),
            array(301),
            array(401),
            array(500),
        );
    }

    /**
     * @dataProvider unsuccessfulCodes
     */
    public function testNon2xxStatusCodesAreNotSuccessful($code)
    {
        $this->headers->setStatusCode($code);
        $this->assertFalse($this->headers->isSuccessful());
    }

    public function testCanRenderObjectAsString()
    {
        $this->headers->setStatusCode(302, 'Moved Temporarily');
        $this->headers->addHeaders(array(
            'Location'     => 'http://foo.local/bar',
            'X-Emitted-By' => __CLASS__,
        ));
        $test = (string) $this->headers;
        $expected = "HTTP/1.1 302 Moved Temporarily\r\nLocation: http://foo.local/bar\r\nX-Emitted-By: " . __CLASS__ . "\r\n";
        $this->assertEquals($expected, $test);
    }

    public function testCanParseHeadersFromString()
    {
        $response = file_get_contents(__DIR__ . '/_files/response_headers.txt');
        $this->headers->fromString($response);
        $this->assertEquals(302, $this->headers->getStatusCode());
        $this->assertEquals('Temporarily Moved', $this->headers->getStatusMessage());
        $this->assertTrue($this->headers->has('Content-Type'));
        $this->assertEquals('text/html', $this->headers->get('Content-Type')->top()->getValue());
        $this->assertTrue($this->headers->has('Location'));
        $this->assertEquals('http://foo.local/bar', $this->headers->get('Location')->top()->getValue());
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
        $this->headers->setStatusCode(301, "Permanently Moved");
        $this->headers->addHeaders(array(
            'X-Foo'    => 'bar',
            'X-Bar'    => 'baz',
            'Location' => 'http://foo.bar/baz',
        ));
        $this->testCanParseHeadersFromString();
        $this->assertFalse($this->headers->has('X-Foo'));
        $this->assertFalse($this->headers->has('X-Bar'));
        $headers = $this->headers->get('Location');
        $this->assertEquals(1, count($headers));
        $this->assertNotEquals('http://foo.bar/baz', $headers->top()->getValue());
    }
}
