<?php

namespace TestMonitor\JUnitXmlParser\Exceptions;

use Exception;

class FileNotFoundException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param string $filePath
     */
    public function __construct(string $filePath)
    {
        parent::__construct('Unable to open ' . $filePath);
    }
}
