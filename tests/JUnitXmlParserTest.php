<?php

namespace TestMonitor\JUnitXmlParser\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use TestMonitor\JUnitXmlParser\JUnitXmlParser;
use TestMonitor\JUnitXmlParser\Models\TestStatus;
use TestMonitor\JUnitXmlParser\Exceptions\ValidationException;
use TestMonitor\JUnitXmlParser\Exceptions\FileNotFoundException;

class JUnitXmlParserTest extends TestCase
{
    protected JUnitXmlParser $parser;

    protected function setUp(): void
    {
        $this->parser = new JUnitXmlParser();
    }

    #[Test]
    public function it_parses_a_junit_xml_report(): void
    {
        $testSuites = $this->parser->parse(__DIR__ . '/fixtures/sample.xml');

        // Assertions for main suite
        $this->assertCount(1, $testSuites);
        $mainSuite = $testSuites[0];
        $this->assertEquals('Main Suite', $mainSuite->getName());
        $this->assertEquals(1.23, $mainSuite->getDuration());
        $this->assertEquals('2024-02-17T10:00:00Z', $mainSuite->getTimestamp());

        // Assertions for nested sub-suite
        $this->assertCount(1, $mainSuite->getNestedTestSuites());
        $subSuite = $mainSuite->getNestedTestSuites()[0];
        $this->assertEquals('Sub Suite', $subSuite->getName());
        $this->assertCount(3, $subSuite->getTestCases());

        // Assertions for test cases
        $testCases = $subSuite->getTestCases();

        $this->assertEquals('Test 1', $testCases[0]->getName());
        $this->assertEquals(TestStatus::PASSED, $testCases[0]->getStatus());

        $this->assertEquals('Test 2', $testCases[1]->getName());
        $this->assertEquals(TestStatus::FAILED, $testCases[1]->getStatus());
        $this->assertEquals('Expected true but got false', $testCases[1]->getFailureMessages()[0]);

        $this->assertEquals('Test 3', $testCases[2]->getName());
        $this->assertEquals(TestStatus::SKIPPED, $testCases[2]->getStatus());
    }

    #[Test]
    public function it_parses_an_empty_file_correctly(): void
    {
        $this->expectException(ValidationException::class);

        $testSuites = $this->parser->parse(__DIR__ . '/fixtures/empty.xml');
    }

    #[Test]
    public function it_handles_a_non_existing_file_gracefully(): void
    {
        $this->expectException(FileNotFoundException::class);

        $this->parser->parse(__DIR__ . '/fixtures/notfound.xml');
    }

    #[Test]
    public function it_handles_malformed_xml_gracefully(): void
    {
        $this->expectException(\Exception::class);

        $this->parser->parse(__DIR__ . '/fixtures/malformed.xml');
    }

    #[Test]
    public function it_parses_deeply_nested_suites(): void
    {
        $testSuites = $this->parser->parse(__DIR__ . '/fixtures/nested_test_suites.xml');

        $this->assertCount(1, $testSuites);
        $level1 = $testSuites[0]->getNestedTestSuites()[0];
        $level2 = $level1->getNestedTestSuites()[0];
        $level3 = $level2->getNestedTestSuites()[0];

        $this->assertEquals('Level 3', $level3->getName());
        $this->assertCount(1, $level3->getTestCases());
    }

    #[Test]
    public function it_parses_multiple_failures_correctly(): void
    {
        $testSuites = $this->parser->parse(__DIR__ . '/fixtures/multiple_failures.xml');

        $testCase = $testSuites[0]->getTestCases()[0];

        $this->assertEquals('Test With Multiple Failures', $testCase->getName());
        $this->assertEquals(TestStatus::FAILED, $testCase->getStatus());
        $this->assertStringContainsString('First failure message', $testCase->getFailureMessages()[0]);
        $this->assertStringContainsString('Second failure message', $testCase->getFailureMessages()[1]);
    }

    #[Test]
    public function it_ignores_system_output(): void
    {
        $testSuites = $this->parser->parse(__DIR__ . '/fixtures/system_output.xml');

        $testCase = $testSuites[0]->getTestCases()[0];

        $this->assertEquals('Test With Output', $testCase->getName());
        $this->assertEquals(TestStatus::PASSED, $testCase->getStatus());
    }

    #[Test]
    public function it_handles_missing_attributes_gracefully(): void
    {
        $this->expectException(\Exception::class);

        $this->parser->parse(__DIR__ . '/fixtures/missing_attributes.xml');
    }

    #[Test]
    public function it_handles_a_large_xml_file(): void
    {
        $testSuites = $this->parser->parse(__DIR__ . '/fixtures/large.xml');

        $this->assertEquals(10, count($testSuites));
        $this->assertEquals(1000, count($testSuites[0]->getTestCases()));
        $this->assertEquals(1000, count($testSuites[9]->getTestCases()));
        $this->assertEquals(TestStatus::PASSED, $testSuites[9]->getTestCases()[1]->getStatus());
        $this->assertEquals(TestStatus::FAILED, $testSuites[9]->getTestCases()[8]->getStatus());

        // Parsing a file with 10.000 test cases should not use 15MB RAM or more.
        $this->assertLessThanOrEqual(1024 * 1024 * 15, memory_get_usage());
    }
}
