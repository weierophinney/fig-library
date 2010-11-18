<?php

namespace Zend\Http;

use ArrayObject,
    Fig\Http\Parameters as HttpParameters;

class Parameters extends ArrayObject implements HttpParameters
{
    /**
     * Constructor
     *
     * Enforces that we have an array, and enforces parameter access to array
     * elements.
     * 
     * @param  array $values 
     * @return void
     */
    public function __construct(array $values = null)
    {
        if (null === $values) {
            $values = array();
        }
        parent::__construct($values, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Populate from native PHP array
     * 
     * @param  array $values 
     * @return void
     */
    public function fromArray(array $values)
    {
        $this->exchangeArray($values);
    }

    /**
     * Populate from query string
     * 
     * @param  string $string 
     * @return void
     */
    public function fromString($string)
    {
        $array = array();
        parse_str($string, $array);
        $this->fromArray($array);
    }

    /**
     * Serialize to native PHP array
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->getArrayCopy();
    }

    /**
     * Serialize to query string
     * 
     * @return string
     */
    public function toString()
    {
        return http_build_query($this);
    }
}