<?php

namespace TestMonitor\JUnitXmlParser\Models;

class TestCase
{
    /**
     * @var array
     */
    protected array $properties = [];

    public function __construct(
        protected string $name,
        protected string $className,
        protected TestStatus $status = TestStatus::PASSED,
        protected array $failureMessages = [],
        protected ?float $duration = null,
        protected ?int $assertions = null,
        protected ?string $systemOut = null,
        protected ?string $systemErr = null
    ) {
    }

    /**
     * Mark test case as passed.
     */
    public function markPassed(): void
    {
        $this->status = TestStatus::PASSED;
    }

    /**
     * Mark test case as failed.
     *
     * @param string $message Failure reason
     */
    public function markFailed(string $message): void
    {
        $this->status = TestStatus::FAILED;

        $this->failureMessages[] = $message;
    }

    /**
     * Mark test case as skipped.
     */
    public function markSkipped(): void
    {
        $this->status = TestStatus::SKIPPED;
    }

    /**
     * Get the name of the test case.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the class name of the test case.
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * Get the result status of the test case.
     *
     * @return \TestMonitor\JUnitXmlParser\Models\TestStatus
     */
    public function getStatus(): TestStatus
    {
        return $this->status;
    }

    /**
     * Get the reasons for failure, when available.
     *
     * @return array
     */
    public function getFailureMessages(): array
    {
        return $this->failureMessages;
    }

    /**
     * Get the duration of the test case execution in seconds, when available.
     *
     * @return null|float
     */
    public function getDuration(): ?float
    {
        return $this->duration;
    }

    /**
     * Get the number of assertions, when available.
     *
     * @return null|int
     */
    public function getNumberOfAssertions(): ?int
    {
        return $this->assertions;
    }

    /**
     * Set the test case properties.
     *
     * @param string $value
     */
    public function setProperties(array $properties): void
    {
        $this->properties = $properties;
    }

    /**
     * Get the test case properties.
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * Set the test case system out data.
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
     * Set the test case system error data.
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
