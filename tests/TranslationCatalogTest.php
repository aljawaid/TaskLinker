<?php

/**
 * Verifies Locale/en_US/translations.php, en_GB and the contributor starter catalogue.
 */

$english = require __DIR__ . '/../Locale/en_US/translations.php';
$britishEnglish = require __DIR__ . '/../Locale/en_GB/translations.php';
$starter = require __DIR__ . '/../Locale/translations-starter-template.php';

taskLinkerAssertSame(
    array_keys($english),
    array_keys($britishEnglish),
    'English catalogues must share one ordered key set.'
);
taskLinkerAssertSame(
    array_keys($english),
    array_keys($starter),
    'The starter catalogue must contain every translation key.'
);

foreach ($english as $key => $value) {
    taskLinkerAssertTrue(
        $key !== '' && $value !== '',
        'English fallback translations must not contain blank entries.'
    );
}

foreach ($britishEnglish as $key => $value) {
    taskLinkerAssertTrue($value !== '', 'British English translations must not contain blank entries.');
}
