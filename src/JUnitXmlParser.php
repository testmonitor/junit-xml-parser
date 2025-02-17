<?php

namespace TestMonitor\JUnitXmlParser;

use XMLReader;
use TestMonitor\JUnitXmlParser\Models\TestCase;
use TestMonitor\JUnitXmlParser\Models\TestSuite;

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
     * @return array
     */
    public function parse(string $filePath): array
    {
        $this->reader = new XMLReader();
        $this->reader->open($filePath);

        while ($this->reader->read()) {
            if ($this->isElement('testsuite')) {
                $this->testSuites[] = $this->parseTestSuite();
            }
        }

        $this->reader->close();

        return $this->testSuites;
    }

    /**
     * Parses a single <testsuite> element.
     *
     * @return \TestMonitor\JUnitXmlParser\Models\TestSuite
     */
    protected function parseTestSuite(): TestSuite
    {
        $testSuite = new TestSuite(
            $this->reader->getAttribute('name'),
            (float) $this->reader->getAttribute('time'),
            $this->reader->getAttribute('timestamp')
        );

        while ($this->reader->read()) {
            if ($this->isEndElement('testsuite')) {
                return $testSuite;
            }

            if ($this->isElement('testcase')) {
                $testSuite->addTestCase($this->parseTestCase());
            } elseif ($this->isElement('testsuite')) {
                $testSuite->addNestedSuite($this->parseTestSuite());
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
        $testCase = new TestCase(
            name: $this->reader->getAttribute('name'),
            className: $this->reader->getAttribute('classname'),
            duration: (float) $this->reader->getAttribute('time'),
            timestamp: $this->reader->getAttribute('timestamp')
        );

        if ($this->reader->isEmptyElement) {
            return $testCase;
        }

        while ($this->reader->read()) {
            if ($this->isEndElement('testcase')) {
                return $testCase;
            } elseif ($this->isElement('failure')) {
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
