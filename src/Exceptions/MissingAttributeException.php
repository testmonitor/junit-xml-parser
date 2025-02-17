<?php

namespace TestMonitor\JUnitXmlParser\Exceptions;

use Exception;

class MissingAttributeException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param string $attribute
     * @param string $node
     */
    public function __construct(string $attribute, string $node)
    {
        parent::__construct('Missing attribute ' . $attribute . ' in ' . $node);
    }
}
