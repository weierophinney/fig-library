<?php

namespace ZendTest\Http;

use Zend\Http\Header;

class HeaderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->header = new Header('X-Foo-Bar', 'baz');
    }

    public function testTypeReflectsConstructorArgument()
    {
        $this->assertEquals('X-Foo-Bar', $this->header->getType());
    }

    public function testValueReflectsConstructorArgument()
    {
        $this->assertEquals('baz', $this->header->getValue());
    }

    public function testReplaceFlagIsFalseByDefault()
    {
        $this->assertFalse($this->header->replace());
    }

    public function testReplaceFlagReflectsConstructorArgument()
    {
        $header = new Header('X-Foo-Bar', 'baz', true);
        $this->assertTrue($header->replace());
    }

    public function testTypeIsMutable()
    {
        $this->header->setType('X-Bar-Baz');
        $this->assertEquals('X-Bar-Baz', $this->header->getType());
    }

    public function testValueIsMutable()
    {
        $this->header->setValue('bazbat');
        $this->assertEquals('bazbat', $this->header->getValue());
    }

    public function testReplaceFlagIsMutable()
    {
        $this->header->replace(true);
        $this->assertTrue($this->header->replace());
    }

    public function emptyTypes()
    {
        return array(
            array(null),
            array(''),
            array(0),
            array('0'),
            array(' '),
            array(false),
            array(array()),
        );
    }

    /**
     * @dataProvider emptyTypes
     */
    public function testTypeMayNotBeEmpty($type)
    {
        $this->setExpectedException('Zend\Http\Exception\InvalidArgumentException');
        $this->header->setType($type);
    }

    public function invalidTypes()
    {
        return array(
            array('1'),
            array(1),
            array(true),
            array(array('foo')),
        );
    }

    /**
     * @dataProvider invalidTypes
     */
    public function testTypesNotMatchingStringOfAtLeastOneCharacterRaiseException($type)
    {
        $this->setExpectedException('Zend\Http\Exception\InvalidArgumentException');
        $this->header->setType($type);
    }

    public function testPassingArrayValueImplodesStringWithSemicolonAndSpaceByDefault()
    {
        $this->header->setValue(array('foo', 'enctype="utf-8"', 'lang="en"'));
        $this->assertEquals('foo; enctype="utf-8"; lang="en"', $this->header->getValue());
    }

    public function testPassingArrayValueImplodesStringWithPassedSeparator()
    {
        $this->header->setValue(array('foo', 'enctype="utf-8"', 'lang="en"'), '/');
        $this->assertEquals('foo/enctype="utf-8"/lang="en"', $this->header->getValue());
    }

    /**
     * @dataProvider emptyTypes
     */
    public function testPassingEmptyValueSetsValueToEmptyString($value)
    {
        $header = new Header('X-Foo-Bar', 'baz');
        $header->setValue($value);
        $this->assertEquals('', $header->getValue());
    }

    public function testHeaderCanRenderItselfAsAString()
    {
        $header = new Header('X-Foo-Bar', 'baz');
        $string = (string) $header;
        $this->assertEquals("X-Foo-Bar: baz\r\n", $string);
    }

    /**
     * @runInSeparateProcess
     */
    public function testHeaderCanSendItself()
    {
        if (!function_exists('xdebug_get_headers')) {
            $this->markTestSkipped('Cannot reliably get headers sent in order to test');
        }
        $header = new Header('X-Foo-Bar', 'baz');
        $header->send();

        $headers  = xdebug_get_headers();
        $expected = "X-Foo-Bar: baz";
        $this->assertContains($expected, $headers, var_export($headers, 1));
    }
}
