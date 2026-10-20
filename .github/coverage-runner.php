<?php

declare(strict_types=1);

// Coverage gate for the unit suite: runs PHPUnit with a PHP coverage report (pcov or xdebug driver), then fails
// unless every executable line of the module code is covered. The module code is what phpunit.xml lists under
// <source>. PHPUnit itself must pass too, a red suite fails the gate.
//
// Usage: php .github/coverage-runner.php

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Node\File;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);
$report = $root . '/coverage.php';

@unlink($report);

$command = implode(' ', [
    escapeshellarg(PHP_BINARY),
    '-d pcov.enabled=1',
    '-d ' . escapeshellarg('pcov.directory=' . $root),
    escapeshellarg($root . '/vendor/bin/phpunit'),
    '--coverage-php ' . escapeshellarg($report),
]);

passthru($command, $suiteStatus);

if (!is_file($report)) {
    fwrite(STDERR, "COVERAGE: no report written, is pcov or xdebug enabled?\n");
    exit(2);
}

/** @var CodeCoverage $coverage */
$coverage = require $report;

$uncovered = [];

foreach ($coverage->getReport() as $node) {
    if (!$node instanceof File) {
        continue;
    }

    foreach ($node->lineCoverageData() as $line => $tests) {
        if ($tests !== null && $tests === []) {
            $uncovered[str_replace($root . '/', '', $node->pathAsString())][] = $line;
        }
    }
}

foreach ($uncovered as $file => $lines) {
    fwrite(STDOUT, "uncovered: $file lines " . implode(', ', $lines) . "\n");
}

$lines = $coverage->getReport()->percentageOfExecutedLines()->asFloat();

printf("COVERAGE: %.2f%% lines%s\n", $lines, $lines < 100.0 ? ' (GATE: FAIL)' : ' (GATE: PASS)');

exit($suiteStatus === 0 && $lines >= 100.0 ? 0 : 1);
