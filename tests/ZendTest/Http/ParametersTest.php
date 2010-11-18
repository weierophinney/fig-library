<?php

namespace ZendTest\Http;

use Zend\Http\Parameters;

class ParametersTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->params = new Parameters();
    }

    public function testCanUseArrayAccess()
    {
        $this->params['foo'] = 'bar';
        $this->assertEquals('bar', $this->params['foo']);
    }

    public function testCanUseObjectAccess()
    {
        $this->params->foo = 'bar';
        $this->assertEquals('bar', $this->params->foo);
    }

    public function testCanMixAndMatchArrayAndObjectAccess()
    {
        $this->params['foo'] = 'bar';
        $this->params->bar   = 'baz';
        $this->assertEquals('bar', $this->params->foo);
        $this->assertEquals('baz', $this->params['bar']);
    }

    public function standardArray()
    {
        return array(
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'bat',
        );
    }

    public function testCanPopulateViaConstructor()
    {
        $array = $this->standardArray();
        $params = new Parameters($array);
        foreach ($array as $key => $value) {
            $this->assertEquals($value, $params[$key]);
        }
    }

    public function testCanPopulateViaFromArray()
    {
        $array = $this->standardArray();
        $this->params->fromArray($array);
        foreach ($array as $key => $value) {
            $this->assertEquals($value, $this->params[$key]);
        }
    }

    public function testCanPopulateFromQueryString()
    {
        $array  = $this->standardArray();
        $string = http_build_query($array);
        $this->params->fromString($string);
        foreach ($array as $key => $value) {
            $this->assertEquals($value, $this->params[$key]);
        }
    }

    public function testCanSerializeToArray()
    {
        $array = $this->standardArray();
        $params = new Parameters($array);
        $this->assertEquals($array, $params->toArray());
    }

    public function testCanSerializeToString()
    {
        $array  = $this->standardArray();
        $params = new Parameters($array);
        $string = http_build_query($array);
        $this->assertEquals($string, $params->toString());
    }
}
