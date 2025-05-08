<?php

namespace TestMonitor\JUnitXmlParser\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use TestMonitor\JUnitXmlParser\Models\Result;
use TestMonitor\JUnitXmlParser\JUnitXmlParser;
use TestMonitor\JUnitXmlParser\Models\TestStatus;
use TestMonitor\JUnitXmlParser\Exceptions\ValidationException;
use TestMonitor\JUnitXmlParser\Exceptions\FileNotFoundException;
use TestMonitor\JUnitXmlParser\Exceptions\MissingAttributeException;

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
        $result = $this->parser->parse(__DIR__ . '/fixtures/sample.xml');

        // Assertions for main suite
        $this->assertInstanceOf(Result::class, $result);
        $this->assertCount(1, $result->getTestSuites());
        $this->assertEquals(1.23, $result->getTotalDuration());
        $this->assertEquals(3, $result->getTotalNumberOfTests());
        $this->assertEquals(6, $result->getTotalNumberOfAssertions());
        $this->assertEquals(0, $result->getTotalNumberOfErrors());
        $this->assertEquals(1, $result->getTotalNumberOfFailures());
        $this->assertEquals(1, $result->getTotalNumberOfSkipped());

        $mainSuite = $result->getTestSuites()[0];
        $this->assertEquals('Main Suite', $mainSuite->getName());
        $this->assertEquals(1.23, $mainSuite->getDuration());
        $this->assertEquals(3, $mainSuite->getNumberOfTests());
        $this->assertEquals(6, $mainSuite->getNumberOfAssertions());
        $this->assertEquals(0, $mainSuite->getNumberOfErrors());
        $this->assertEquals(1, $mainSuite->getNumberOfFailures());
        $this->assertEquals(1, $mainSuite->getNumberOfSkipped());

        // Assertions for nested sub-suite
        $this->assertCount(1, $mainSuite->getNestedTestSuites());
        $subSuite = $mainSuite->getNestedTestSuites()[0];
        $this->assertEquals('Sub Suite', $subSuite->getName());
        $this->assertCount(3, $subSuite->getTestCases());

        // Assertions for test cases
        $testCases = $subSuite->getTestCases();

        $this->assertEquals('Test 1', $testCases[0]->getName());
        $this->assertEquals(TestStatus::PASSED, $testCases[0]->getStatus());
        $this->assertEquals(0.05, $testCases[0]->getDuration());
        $this->assertEquals(2, $testCases[0]->getNumberOfAssertions());

        $this->assertEquals('Test 2', $testCases[1]->getName());
        $this->assertEquals(TestStatus::FAILED, $testCases[1]->getStatus());
        $this->assertEquals('Expected true but got false', $testCases[1]->getFailureMessages()[0]);
        $this->assertEquals(0.1, $testCases[1]->getDuration());
        $this->assertEquals(3, $testCases[1]->getNumberOfAssertions());

        $this->assertEquals('Test 3', $testCases[2]->getName());
        $this->assertEquals(TestStatus::SKIPPED, $testCases[2]->getStatus());
        $this->assertEquals(0.08, $testCases[2]->getDuration());
        $this->assertEquals(1, $testCases[2]->getNumberOfAssertions());
    }

    #[Test]
    public function it_parses_an_empty_file_correctly(): void
    {
        $this->expectException(ValidationException::class);

        $this->parser->parse(__DIR__ . '/fixtures/empty.xml');
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
        $this->expectException(ValidationException::class);

        $this->parser->parse(__DIR__ . '/fixtures/malformed.xml');
    }

    #[Test]
    public function it_parses_deeply_nested_suites(): void
    {
        $result = $this->parser->parse(__DIR__ . '/fixtures/nested_test_suites.xml');

        $this->assertInstanceOf(Result::class, $result);
        $this->assertCount(1, $result->getTestSuites());
        $level1 = $result->getTestSuites()[0]->getNestedTestSuites()[0];
        $level2 = $level1->getNestedTestSuites()[0];
        $level3 = $level2->getNestedTestSuites()[0];

        $this->assertEquals('Level 3', $level3->getName());
        $this->assertCount(1, $level3->getTestCases());
    }

    #[Test]
    public function it_parses_empty_suites(): void
    {
        $result = $this->parser->parse(__DIR__ . '/fixtures/empty_suite.xml');

        $this->assertInstanceOf(Result::class, $result);
        $this->assertCount(1, $result->getTestSuites());

        $empty = $result->getTestSuites()[0]->getNestedTestSuites()[0];
        $this->assertEquals('Empty', $empty->getName());
        $this->assertCount(0, $empty->getTestCases());

        $filled = $result->getTestSuites()[0]->getNestedTestSuites()[1];
        $this->assertEquals('Something', $filled->getName());
        $this->assertCount(2, $filled->getTestCases());
    }

    #[Test]
    public function it_parses_multiple_failures_correctly(): void
    {
        $result = $this->parser->parse(__DIR__ . '/fixtures/multiple_failures.xml');

        $testCase = $result->getTestSuites()[0]->getTestCases()[0];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals('Test With Multiple Failures', $testCase->getName());
        $this->assertEquals(TestStatus::FAILED, $testCase->getStatus());
        $this->assertStringContainsString('First failure message', $testCase->getFailureMessages()[0]);
        $this->assertStringContainsString('Second failure message', $testCase->getFailureMessages()[1]);
    }

    #[Test]
    public function it_parses_test_suite_and_test_case_properties(): void
    {
        $parser = new JUnitXmlParser();
        $result = $parser->parse(__DIR__ . '/fixtures/properties.xml');

        $suite = $result->getTestSuites()[0];
        $this->assertEquals('Suite With Properties', $suite->getName());

        // Suite-level properties
        $suiteProps = $suite->getProperties();
        $this->assertEquals('Chrome', $suiteProps['browser'] ?? null);
        $this->assertEquals('staging', $suiteProps['env'] ?? null);

        // Test case properties
        $testCase = $suite->getTestCases()[0];
        $testProps = $testCase->getProperties();
        $this->assertEquals('true', $testProps['retry'] ?? null);
        $this->assertEquals('5000', $testProps['timeout'] ?? null);
    }

    #[Test]
    public function it_parses_system_out_data_in_test_suites(): void
    {
        $result = $this->parser->parse(__DIR__ . '/fixtures/system_output.xml');

        $testSuite = $result->getTestSuites()[0];

        $this->assertEquals('Suite 1', $testSuite->getName());
        $this->assertEquals('This is system output for a test suite', $testSuite->getSystemOut());
    }

    #[Test]
    public function it_parses_system_err_data_in_test_suites(): void
    {
        $result = $this->parser->parse(__DIR__ . '/fixtures/system_error.xml');

        $testSuite = $result->getTestSuites()[0];

        $this->assertEquals('Suite 1', $testSuite->getName());
        $this->assertEquals('Something went wrong in this suite', $testSuite->getSystemErr());
    }

    #[Test]
    public function it_parses_system_out_data_in_test_cases(): void
    {
        $result = $this->parser->parse(__DIR__ . '/fixtures/system_output.xml');

        $testCase = $result->getTestSuites()[0]->getTestCases()[0];

        $this->assertEquals('Test With Output', $testCase->getName());
        $this->assertEquals(TestStatus::PASSED, $testCase->getStatus());
        $this->assertEquals('This is system output for a test case', $testCase->getSystemOut());
    }

    #[Test]
    public function it_parses_system_err_data_in_test_cases(): void
    {
        $result = $this->parser->parse(__DIR__ . '/fixtures/system_error.xml');

        $testCase = $result->getTestSuites()[0]->getTestCases()[0];

        $this->assertEquals('Test With Output', $testCase->getName());
        $this->assertEquals(TestStatus::FAILED, $testCase->getStatus());
        $this->assertEquals('Something went wrong in this test case', $testCase->getSystemErr());
    }

    #[Test]
    public function it_handles_missing_attributes_gracefully(): void
    {
        $this->expectException(MissingAttributeException::class);

        $this->parser->parse(__DIR__ . '/fixtures/missing_attributes.xml');
    }

    #[Test]
    public function it_handles_a_large_xml_file(): void
    {
        $result = $this->parser->parse(__DIR__ . '/fixtures/large.xml');

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(10, count($result->getTestSuites()));
        $this->assertEquals(1000, count($result->getTestSuites()[0]->getTestCases()));
        $this->assertEquals(1000, count($result->getTestSuites()[9]->getTestCases()));
        $this->assertEquals(TestStatus::PASSED, $result->getTestSuites()[9]->getTestCases()[1]->getStatus());
        $this->assertEquals(TestStatus::FAILED, $result->getTestSuites()[9]->getTestCases()[8]->getStatus());

        // Parsing a file with 10.000 test cases should not use 15MB RAM or more.
        $this->assertLessThanOrEqual(1024 * 1024 * 15, memory_get_usage());
    }
}
