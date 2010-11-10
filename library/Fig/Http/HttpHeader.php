<?php

namespace Fig\Http;

interface HttpHeader
{
    public function __construct($type, $value = null, $replace = false);

    /* mutators */
    public function setType($type);
    public function setValue($type);
    public function replace($flag = null); // also acts as mutator

    /* accessors */
    public function getType();
    public function getValue();
}
