<?php

namespace TestMonitor\JUnitXmlParser\Models;

class TestSuite
{
    /**
     * @var array
     */
    protected array $testCases = [];

    /**
     * @var array
     */
    protected array $nestedTestSuites = [];

    /**
     * @var array
     */
    protected array $properties = [];

    public function __construct(
        protected string $name,
        protected ?float $duration = null,
        protected ?int $tests = null,
        protected ?int $assertions = null,
        protected ?int $errors = null,
        protected ?int $failures = null,
        protected ?int $skipped = null,
        protected ?string $systemOut = null,
        protected ?string $systemErr = null
    ) {
    }

    /**
     * Add a test case to this test suite.
     *
     * @param \TestMonitor\JUnitXmlParser\Models\TestCase $testCase
     */
    public function addTestCase(TestCase $testCase): void
    {
        $this->testCases[] = $testCase;
    }

    /**
     * Add a nested test suite to this test suite.
     *
     * @param \TestMonitor\JUnitXmlParser\Models\TestSuite $testSuite
     */
    public function addNestedTestSuite(TestSuite $testSuite): void
    {
        $this->nestedTestSuites[] = $testSuite;
    }

    /**
     * Get the name of this test suite.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the test cases for this test suite.
     *
     * @return array<\TestMonitor\JUnitXmlParser\Models\TestCase>
     */
    public function getTestCases(): array
    {
        return $this->testCases;
    }

    /**
     * Get the nested test suites for this test suite.
     *
     * @return array<\TestMonitor\JUnitXmlParser\Models\TestSuite>
     */
    public function getNestedTestSuites(): array
    {
        return $this->nestedTestSuites;
    }

    /**
     * Get the duration of the test suite execution in seconds, when available.
     *
     * @return null|float
     */
    public function getDuration(): ?float
    {
        return $this->duration;
    }

    /**
     * Get the number of the test cases, when available.
     *
     * @return null|int
     */
    public function getNumberOfTests(): ?int
    {
        return $this->tests;
    }

    /**
     * Get the total number of assertions, when available.
     *
     * @return null|int
     */
    public function getNumberOfAssertions(): ?int
    {
        return $this->assertions;
    }

    /**
     * Get the total number of errors, when available.
     *
     * @return null|int
     */
    public function getNumberOfErrors(): ?int
    {
        return $this->errors;
    }

    /**
     * Get the total number of failures, when available.
     *
     * @return null|int
     */
    public function getNumberOfFailures(): ?int
    {
        return $this->failures;
    }

    /**
     * Get the total number of skipped test cases, when available.
     *
     * @return null|int
     */
    public function getNumberOfSkipped(): ?int
    {
        return $this->skipped;
    }

    /**
     * Set the test suite properties.
     *
     * @param string $value
     */
    public function setProperties(array $properties): void
    {
        $this->properties = $properties;
    }

    /**
     * Get the test suite properties.
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * Set the test suite system out data.
     *
     * @param string $value
     */
    public function setSystemOut(string $value): void
    {
        $this->systemOut = html_entity_decode(trim($value));
    }

    /**
     * Get the system out data, when available.
     *
     * @return null|string
     */
    public function getSystemOut(): ?string
    {
        return $this->systemOut;
    }

    /**
     * Set the test suite system error data.
     *
     * @param string $value
     */
    public function setSystemErr(string $value): void
    {
        $this->systemErr = html_entity_decode(trim($value));
    }

    /**
     * Get the system error data, when available.
     *
     * @return null|string
     */
    public function getSystemErr(): ?string
    {
        return $this->systemErr;
    }
}
