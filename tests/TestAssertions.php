<?php

/**
 * Provides dependency-free assertions for TaskLinker's focused test suite.
 *
 * Related test files: every *Test.php file in this directory.
 */

function taskLinkerAssertTrue($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function taskLinkerAssertFalse($condition, $message)
{
    taskLinkerAssertTrue(!$condition, $message);
}

function taskLinkerAssertSame($expected, $actual, $message)
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            $message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true) . '.'
        );
    }
}

function taskLinkerAssertContains($needle, $haystack, $message)
{
    taskLinkerAssertTrue(strpos($haystack, $needle) !== false, $message);
}

function taskLinkerAssertNotContains($needle, $haystack, $message)
{
    taskLinkerAssertTrue(strpos($haystack, $needle) === false, $message);
}
