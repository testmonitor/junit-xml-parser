# TestMonitor JUnit Parser

[![Latest Stable Version](https://poser.pugx.org/testmonitor/junit-xml-parser/v/stable)](https://packagist.org/packages/testmonitor/junit-xml-parser)
[![CircleCI](https://img.shields.io/circleci/project/github/testmonitor/junit-xml-parser.svg)](https://circleci.com/gh/testmonitor/junit-xml-parser)
[![StyleCI](https://styleci.io/repos/934299329/shield)](https://styleci.io/repos/934299329)
[![codecov](https://codecov.io/gh/testmonitor/junit-xml-parser/graph/badge.svg?token=OX609Y0IJY)](https://codecov.io/gh/testmonitor/junit-xml-parser)
[![License](https://poser.pugx.org/testmonitor/junit-xml-parser/license)](https://packagist.org/packages/testmonitor/junit-xml-parser)

This package provides a very basic, convenient parser for JUnit XML reports.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Examples](#examples)
- [Tests](#tests)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)

## Installation

To install the client you need to require the package using composer:

	$ composer require testmonitor/junit-xml-parser

Use composer's autoload:

```php
require __DIR__.'/../vendor/autoload.php';
```

You're all set up now!

## Usage

Include the parser in your project:

```php
use TestMonitor\JUnitXmlParser\JUnitXmlParser;

$parser = new JUnitXmlParser();
$testSuites = $parser->parse('path/to/junit.xml');
```

## Examples

Below are some examples demonstrating how to use the JUnit XML Parser to extract and process test results.

### Parsing a JUnit XML file

This example shows how to parse a JUnit XML report and retrieve test suite and test case information.

```php
use TestMonitor\JUnitXmlParser\JUnitXmlParser;

$parser = new JUnitXmlParser();
$testSuites = $parser->parse('tests/results.xml');

foreach ($testSuites as $suite) {
    echo "Suite: " . $suite->getName() . "\n";

    foreach ($suite->getTestCases() as $testCase) {
        echo "  Test: " . $testCase->getName() . " - Status: " . $testCase->getStatus()->name . "\n";
    }
}
```

### Processing Failures and Skipped Tests

This example demonstrates how to identify and handle failed and skipped test cases.

```php
use TestMonitor\JUnitXmlParser\JUnitXmlParser;

$parser = new JUnitXmlParser();
$testSuites = $parser->parse('tests/results.xml');

foreach ($testSuites as $suite) {
    foreach ($suite->getTestCases() as $testCase) {
        if ($testCase->getStatus() === TestStatus::FAILED) {
            echo "Test " . $testCase->getName() . " failed: " . $testCase->getFailureMessage() . "\n";
        } elseif ($testCase->getStatus() === TestStatus::SKIPPED) {
            echo "Test " . $testCase->getName() . " was skipped.\n";
        }
    }
}
```

### Processing System Out and System Err

This example demonstrates how to retrieve the information for the system-out and system-err tags:

```php
use TestMonitor\JUnitXmlParser\JUnitXmlParser;
use TestMonitor\JUnitXmlParser\Enums\TestStatus;

$parser = new JUnitXmlParser();
$result = $parser->parse('tests/results.xml');

foreach ($result->getTestSuites() as $suite) {
    foreach ($suite->getTestCases() as $testCase) {
        if ($testCase->getStatus() === TestStatus::PASSED) {
            echo "Test '{$testCase->getName()}' passed:\n";
            echo $testCase->getSystemOut() . "\n\n";
        } else {
            echo "Test '{$testCase->getName()}' didn't pass:\n";
            echo $testCase->getSystemErr() . "\n\n";
        }
    }
}
```

### Extracting Execution Time and Timestamp

JUnit XML reports include execution times and timestamps, which can be accessed as shown below.

```php
use TestMonitor\JUnitXmlParser\JUnitXmlParser;

$parser = new JUnitXmlParser();
$testSuites = $parser->parse('tests/results.xml');

foreach ($testSuites as $suite) {
    echo "Suite: " . $suite->getName() . " executed in " . $suite->getDuration() . " seconds on " . $suite->getTimestamp() . "\n";
}
```

## Tests

The package contains integration tests. You can run them using PHPUnit.

    $ vendor/bin/phpunit

## Changelog

Refer to [CHANGELOG](CHANGELOG.md) for more information.

## Contributing

Refer to [CONTRIBUTING](CONTRIBUTING.md) for contributing details.

## Credits

* **Thijs Kok** - *Lead developer* - [ThijsKok](https://github.com/thijskok)
* **Stephan Grootveld** - *Developer* - [Stefanius](https://github.com/stefanius)
* **Frank Keulen** - *Developer* - [FrankIsGek](https://github.com/frankisgek)

## License

The MIT License (MIT). Refer to the [License](LICENSE.md) for more information.
