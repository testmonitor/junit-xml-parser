<?php

namespace TestMonitor\JUnitXmlParser\Models;

class TestCase
{
    public function __construct(
        protected string $name,
        protected string $className,
        protected TestStatus $status = TestStatus::PASSED,
        protected ?string $failureMessage = null,
        protected ?float $duration = null,
        protected ?string $timestamp = null
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

        $this->failureMessage = $message;
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
     * Get the reason for failure, when available.
     *
     * @return \TestMonitor\JUnitXmlParser\Models\TestStatus
     */
    public function getFailureMessage(): ?string
    {
        return $this->failureMessage;
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
     * Get the test case execution timestamp.
     *
     * @return null|string
     */
    public function getTimestamp(): ?string
    {
        return $this->timestamp;
    }
}
