<?php

namespace TestMonitor\JUnitXmlParser;

use Exception;
use XMLReader;
use TestMonitor\JUnitXmlParser\Models\Result;
use TestMonitor\JUnitXmlParser\Models\TestCase;
use TestMonitor\JUnitXmlParser\Models\TestSuite;
use TestMonitor\JUnitXmlParser\Exceptions\ValidationException;
use TestMonitor\JUnitXmlParser\Exceptions\FileNotFoundException;
use TestMonitor\JUnitXmlParser\Exceptions\MissingAttributeException;

class JUnitXmlParser
{
    /**
     * @var \XMLReader
     */
    protected XMLReader $reader;

    /**
     * @var array
     */
    protected array $testSuites = [];

    /**
     * Parse a JUnit XML report.
     *
     * @param string $filePath
     * @return \TestMonitor\JUnitXmlParser\Models\Result
     */
    public function parse(string $filePath): Result
    {
        libxml_use_internal_errors(true);

        $this->reader = new XMLReader();

        if (! @$this->reader->open($filePath)) {
            throw new FileNotFoundException($filePath);
        }

        $testSuites = [];

        while ($this->reader->read()) {
            if ($this->isElement('testsuite')) {
                $testSuites[] = $this->parseTestSuite();
            }
        }

        $this->reader->close();

        $this->throwExceptionWhenValidationFailed();

        return new Result($testSuites);
    }

    /**
     * Throws an exception when error(s) occured during parsing.
     *
     * @throws \RuntimeException
     */
    protected function throwExceptionWhenValidationFailed(): void
    {
        $errors = libxml_get_errors();

        if (! empty($errors)) {
            throw new ValidationException($errors);
        }

        // Clear errors to avoid accumulation
        libxml_clear_errors();
    }

    /**
     * Parses a single <testsuite> element.
     *
     * @return \TestMonitor\JUnitXmlParser\Models\TestSuite
     */
    protected function parseTestSuite(): TestSuite
    {
        $this->validateAttributes(['name']);

        $testSuite = new TestSuite(
            name: $this->reader->getAttribute('name'),
            duration: (float) $this->reader->getAttribute('time'),
            tests: (int) $this->reader->getAttribute('tests'),
            assertions: (int) $this->reader->getAttribute('assertions'),
            errors: (int) $this->reader->getAttribute('errors'),
            failures: (int) $this->reader->getAttribute('failures'),
            skipped: (int) $this->reader->getAttribute('skipped'),
        );

        while ($this->reader->read()) {
            if ($this->isEndElement('testsuite')) {
                return $testSuite;
            }

            if ($this->isElement('testcase')) {
                $testSuite->addTestCase($this->parseTestCase());
            } elseif ($this->isElement('testsuite')) {
                $testSuite->addNestedTestSuite($this->parseTestSuite());
            }
        }

        return $testSuite;
    }

    /**
     * Parses a single <testcase> element.
     *
     * @return \TestMonitor\JUnitXmlParser\Models\TestCase
     */
    protected function parseTestCase(): TestCase
    {
        $this->validateAttributes(['name', 'classname']);

        $testCase = new TestCase(
            name: $this->reader->getAttribute('name'),
            className: $this->reader->getAttribute('classname'),
            duration: (float) $this->reader->getAttribute('time'),
            assertions: (int) $this->reader->getAttribute('assertions')
        );

        if ($this->reader->isEmptyElement) {
            return $testCase;
        }

        while ($this->reader->read()) {
            if ($this->isEndElement('testcase')) {
                return $testCase;
            }

            if ($this->isElement('failure')) {
                $testCase->markFailed($this->readValue());
            } elseif ($this->isElement('skipped')) {
                $testCase->markSkipped();
            }
        }

        return $testCase;
    }

    /**
     * Reads the value of the current XML node and advances the reader.
     *
     * @return string
     */
    protected function readValue(): string
    {
        $this->reader->read();

        return trim($this->reader->value);
    }

    /**
     * Validates the presence of a list of attributes for the current element.
     *
     * @param array $attributes
     *
     * @throws \TestMonitor\JUnitXmlParser\Exceptions\MissingAttributeException
     */
    protected function validateAttributes(array $attributes): void
    {
        foreach ($attributes as $attribute) {
            if (empty($this->reader->getAttribute($attribute))) {
                throw new MissingAttributeException($attribute, $this->reader->readOuterXml());
            }
        }
    }

    /**
     * Determines if the current element matches the provided name.
     *
     * @param string $name
     * @return bool
     */
    protected function isElement(string $name): bool
    {
        return $this->reader->nodeType === XMLReader::ELEMENT && $this->reader->name === $name;
    }

    /**
     * Determines if the closing element matches the provided name.
     *
     * @param string $name
     * @return bool
     */
    protected function isEndElement(string $name): bool
    {
        return $this->reader->nodeType === XMLReader::END_ELEMENT && $this->reader->name === $name;
    }
}
