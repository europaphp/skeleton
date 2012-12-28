<?php

$percent  = $this->context('percent');
$report   = $this->context('report');
$suite    = $this->context('suite');
$untested = $this->context('untested');

echo PHP_EOL . sprintf('Ran %d test%s.', count($suite), count($suite) === 1 ? '' : 's') . PHP_EOL;
echo PHP_EOL . 'Coverage: ' . $report->getPercentTested() . '%' . PHP_EOL . PHP_EOL;

if ($suite->isPassed()) {
    echo $this->helper('cli')->color('Passed!', 'green');
} else {
    echo $this->helper('cli')->color('Failed!', 'red/white');
}

echo PHP_EOL . PHP_EOL;

if (count($assertions = $suite->getAssertions()->getFailed())) {
    echo 'Assertions' . PHP_EOL;
    echo '----------' . PHP_EOL;

    foreach ($assertions as $ass) {
        echo '  ' . $ass->getTestClass() . ':' . $ass->getTestLine() . ' ' . $ass->getMessage() . PHP_EOL;
    }

    echo PHP_EOL;
}

if (count($exceptions = $suite->getExceptions())) {
    echo 'Exceptions' . PHP_EOL;
    echo '----------' . PHP_EOL;

    foreach ($exceptions as $exc) {
        echo '  ' . $exc->getFile() . ':' . $exc->getLine() . ' ' . $exc->getMessage() . PHP_EOL;
    }

    echo PHP_EOL;
}

if (count($benchmarks = $suite->getBenchmarks())) {
    echo 'Benchmarks' . PHP_EOL;
    echo '----------' . PHP_EOL;

    foreach ($benchmarks as $name => $bench) {
        echo '  ' . $name . ': ' . round($bench->getTime(), 3) . PHP_EOL;
    }

    echo PHP_EOL;
}

if ($untested && $report->getUntestedFileCount()) {
    echo 'Untested Files and Lines' . PHP_EOL;
    echo '------------------------' . PHP_EOL;

    foreach ($report->getUntestedFiles() as $file) {
        echo PHP_EOL . $this->helper('cli')->color($file, 'cyan');

        foreach ($file->getUntestedLines() as $line) {
            echo PHP_EOL . '  ';
            echo $this->helper('cli')->color($line->getNumber() . ': ', 'yellow');
            echo rtrim($line);
        }
    }

    echo PHP_EOL . PHP_EOL;
}

if ($suite->isFailed()) {
    exit(1);
}