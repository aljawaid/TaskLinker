<?php

/**
 * Verifies Plugin.php registrations, metadata and page-scoped asset behaviour.
 */

use Kanboard\Plugin\TaskLinker\Plugin;

$plugin = $loadedPlugins['TaskLinker'];

taskLinkerAssertSame('1.1.0', $plugin->getPluginVersion(), 'The production feature set must expose version 1.1.0.');
taskLinkerAssertSame(
    '>=1.2.54',
    $plugin->getCompatibleVersion(),
    'Compatibility must match the tested Kanboard baseline.'
);
taskLinkerAssertSame(
    100,
    Plugin::normalizeGraphMaxNodes('invalid'),
    'Invalid graph limits must use the documented default.'
);
taskLinkerAssertSame(10, Plugin::normalizeGraphMaxNodes('10'), 'The lower graph limit must remain available.');
taskLinkerAssertSame(250, Plugin::normalizeGraphMaxNodes('250'), 'The upper graph limit must remain available.');

$pluginSource = file_get_contents(__DIR__ . '/../Plugin.php');
taskLinkerAssertContains(
    "projectAccessMap->add('TaskLinkerController', '*', Role::PROJECT_VIEWER)",
    $pluginSource,
    'The graph controller must retain Kanboard project-viewer authorisation.'
);
taskLinkerAssertContains(
    "applicationAccessMap->add('TaskLinkerConfigController', '*', Role::APP_ADMIN)",
    $pluginSource,
    'TaskLinker settings must remain administrator-only.'
);

$taskHooks = $container['hook']->getListeners('template:task:sidebar:information');
$configHooks = $container['hook']->getListeners('template:config:sidebar');
taskLinkerAssertContains(
    'taskLinker:task/sidebar',
    var_export($taskHooks, true),
    'The task graph navigation hook must be registered.'
);
taskLinkerAssertContains(
    'taskLinker:config/sidebar',
    var_export($configHooks, true),
    'The settings navigation hook must be registered.'
);

foreach (array('template:layout:js', 'template:layout:css') as $hookName) {
    $listeners = $container['hook']->getListeners($hookName);
    taskLinkerAssertNotContains(
        'TaskLinker',
        var_export($listeners, true),
        'Large graph assets must not load globally.'
    );
}

taskLinkerAssertTrue(isset($container['taskGraphModel']), 'The bounded task graph model must be registered.');
