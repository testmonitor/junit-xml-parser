<?php

namespace TestMonitor\JUnitXmlParser\Models;

enum TestStatus: string
{
    case PASSED = 'PASSED';
    case FAILED = 'FAILED';
    case SKIPPED = 'SKIPPED';
    case ERROR = 'ERROR';
}
