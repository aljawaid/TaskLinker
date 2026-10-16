<?php

/**
 * Verifies Assets/js/task-linker-graph-builder.js and Assets/css/task-linker.css.
 */

$script = file_get_contents(__DIR__ . '/../Assets/js/task-linker-graph-builder.js');
$stylesheet = file_get_contents(__DIR__ . '/../Assets/css/task-linker.css');

taskLinkerAssertContains("container === null", $script, 'The graph builder must tolerate every non-graph page.');
taskLinkerAssertContains("window.location.assign", $script, 'Graph nodes must use same-tab navigation.');
taskLinkerAssertNotContains('window.open', $script, 'Graph navigation must not expose a new-window opener.');
taskLinkerAssertContains('ResizeObserver', $script, 'The graph must react to responsive container changes.');
taskLinkerAssertContains('avoidOverlap: 0.7', $script, 'Graph nodes must retain production spacing.');
taskLinkerAssertContains('springLength: 180', $script, 'Linked graph nodes must not crowd their labels.');
taskLinkerAssertContains('@media (max-width: 768px)', $stylesheet, 'The graph must include a mobile layout.');
taskLinkerAssertContains(
    'prefers-reduced-motion: reduce',
    $stylesheet,
    'The graph must respect reduced-motion preferences.'
);
taskLinkerAssertContains(
    'var(--panel-background-color',
    $stylesheet,
    'The graph must inherit Kanboard theme variables.'
);
taskLinkerAssertNotContains('.pp-', $stylesheet, 'TaskLinker must not expose generic global utility classes.');

$vendorNotice = file_get_contents(__DIR__ . '/../Assets/vendor/vis-network/README.md');
taskLinkerAssertContains(
    'vis-network` 10.1.2',
    $vendorNotice,
    'The maintained graph dependency version must be documented.'
);
taskLinkerAssertTrue(
    is_file(__DIR__ . '/../Assets/vendor/vis-network/LICENSE-MIT')
        && is_file(__DIR__ . '/../Assets/vendor/vis-network/LICENSE-APACHE-2.0'),
    'Vendored graph assets must retain their upstream licences.'
);
