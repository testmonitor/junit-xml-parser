<?php

namespace TestMonitor\JUnitXmlParser\Models;

class Result
{
    public function __construct(protected array $testSuites = [])
    {
    }

    /**
     * Return the parsed test suites.
     *
     * @return array
     */
    public function getTestSuites(): array
    {
        return $this->testSuites;
    }

    /**
     * Determine the total duration for all test suites. Returns 0 if unknown.
     *
     * @return float
     */
    public function getTotalDuration(): float
    {
        return array_sum(
            array_map(fn (TestSuite $testSuite) => $testSuite->getDuration() ?? 0, $this->testSuites)
        );
    }

    /**
     * Determine the total number of tests for all test suites. Returns 0 if unknown.
     *
     * @return int
     */
    public function getTotalNumberOfTests(): int
    {
        return array_sum(
            array_map(fn (TestSuite $testSuite) => $testSuite->getNumberOfTests() ?? 0, $this->testSuites)
        );
    }

    /**
     * Determine the total number of assertions for all test suites. Returns 0 if unknown.
     *
     * @return int
     */
    public function getTotalNumberOfAssertions(): int
    {
        return array_sum(
            array_map(fn (TestSuite $testSuite) => $testSuite->getNumberOfAssertions() ?? 0, $this->testSuites)
        );
    }

    /**
     * Determine the total number of errors for all test suites. Returns 0 if unknown.
     *
     * @return int
     */
    public function getTotalNumberOfErrors(): int
    {
        return array_sum(
            array_map(fn (TestSuite $testSuite) => $testSuite->getNumberOfErrors() ?? 0, $this->testSuites)
        );
    }

    /**
     * Determine the total number of failures for all test suites. Returns 0 if unknown.
     *
     * @return int
     */
    public function getTotalNumberOfFailures(): int
    {
        return array_sum(
            array_map(fn (TestSuite $testSuite) => $testSuite->getNumberOfFailures() ?? 0, $this->testSuites)
        );
    }

    /**
     * Determine the total number of skipped test cases for all test suites. Returns 0 if unknown.
     *
     * @return int
     */
    public function getTotalNumberOfSkipped(): int
    {
        return array_sum(
            array_map(fn (TestSuite $testSuite) => $testSuite->getNumberOfSkipped() ?? 0, $this->testSuites)
        );
    }
}
