<?php

namespace Zend\Http;

use Fig\Http\HttpHeader;

class Header implements HttpHeader
{
    /** @var string */
    protected $type;

    /** @var string */
    protected $value;

    /** @var bool */
    protected $replaceFlag;

    /**
     * Constructor
     * 
     * @param  string $type 
     * @param  string|array $value 
     * @param  bool $replace 
     * @return void
     */
    public function __construct($type, $value, $replace = false)
    {
        $this->setType($type);
        $this->setValue($value);
        $this->replace($replace);
    }

    /* mutators */

    /**
     * Set header type
     * 
     * @param  string $type 
     * @return Header
     */
    public function setType($type)
    {
        if (!is_scalar($type)) {
            throw new Exception\InvalidArgumentException('Header type must be scalar');
        }
        if (!preg_match('/^[a-z][a-z0-9-]*$/i', (string) $type)) {
            throw new Exception\InvalidArgumentException('Header type must start with a letter, and consist of only letters, numbers, and dashes');
        }
        $this->type = $type;
        return $this;
    }

    /**
     * Set header value
     * 
     * @param  string|array $value 
     * @return Header
     */
    public function setValue($value, $separator = '; ')
    {
        if (is_array($value)) {
            $value = implode($separator, $value);
        }
        $value = (string) $value;
        if (empty($value) || preg_match('/^\s+$/', $value)) {
            $value = '';
        }
        $this->value = $value;
        return $this;
    }

    /**
     * Retrieve or set "replace" flag
     *
     * Used by the Headers class when sending headers.
     *
     * If a null flag is passed (or no argument passed), returns the value of 
     * the flag; otherwise, sets it.
     * 
     * @param  null|bool $flag 
     * @return Header|bool
     */
    public function replace($flag = null)
    {
        if (null === $flag) {
            return $this->replaceFlag;
        }
        $this->replaceFlag = (bool) $flag;
        return $this;
    }

    /* accessors */

    /**
     * Retrieve header type
     * 
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Retrieve header value
     * 
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /* behavior */

    /**
     * Send header
     *
     * Proxies to __toString() to format header appropriately (and trims it), 
     * and uses value of replace flag as second argument for header().
     * 
     * @return void
     */
    public function send()
    {
        header(trim($this->__toString()), $this->replace());
    }

    /**
     * Cast to string
     *
     * Returns in form of "TYPE: VALUE\r\n"
     * 
     * @return string
     */
    public function __toString()
    {
        $type  = $this->getType();
        $value = $this->getValue();
        return $type . ': ' . $value . "\r\n";
    }
}
