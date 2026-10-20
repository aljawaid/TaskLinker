<?php

/**
 * Verifies Plugin.php registrations, metadata and page-scoped asset behaviour.
 */

use Kanboard\Plugin\TaskLinker\Plugin;
use Kanboard\Plugin\TaskLinker\ExternalLink\NextcloudLinkProvider;

$plugin = $loadedPlugins['TaskLinker'];

taskLinkerAssertSame('1.1.3', $plugin->getPluginVersion(), 'The rollback-safe migration release must expose version 1.1.3.');
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

$migrationProjectId = $container['projectModel']->create(array('name' => 'External link migration fixture'));
$migrationTaskId = $container['taskCreationModel']->create(array(
    'project_id' => $migrationProjectId,
    'title' => 'Preserved external link task',
));
$legacyLinkId = $container['db']->table('task_has_external_links')->persist(array(
    'link_type' => NextcloudLinkProvider::LEGACY_TYPE,
    'dependency' => 'related',
    'title' => 'Nextcloud shared item',
    'url' => 'https://cloud.example.test/nextcloud/index.php/s/preserved-link',
    'date_creation' => time(),
    'date_modification' => time(),
    'task_id' => $migrationTaskId,
    'creator_id' => 0,
));
taskLinkerAssertTrue($legacyLinkId !== false, 'The legacy external-link fixture must be created.');
$container['configModel']->save(array(
    Plugin::EXTERNAL_LINK_STORAGE_KEY => 'weblink-v1',
));
$container['configModel']->remove(Plugin::EXTERNAL_LINK_CURSOR_KEY);
$container['memoryCache']->flush();
$plugin->initialize();
$migratedLink = $container['db']->table('task_has_external_links')->eq('id', $legacyLinkId)->findOne();
taskLinkerAssertSame(
    NextcloudLinkProvider::TYPE,
    $migratedLink['link_type'],
    'Legacy provider records must migrate to uninstall-safe Web Link storage.'
);
taskLinkerAssertSame(
    'https://cloud.example.test/nextcloud/index.php/s/preserved-link',
    $migratedLink['url'],
    'The compatibility migration must preserve stored external-link data.'
);
$container['memoryCache']->flush();
taskLinkerAssertSame(
    Plugin::EXTERNAL_LINK_STORAGE_VERSION,
    $container['configModel']->get(Plugin::EXTERNAL_LINK_STORAGE_KEY),
    'The compatibility migration must record its storage version.'
);
taskLinkerAssertSame(
    (int) $legacyLinkId,
    (int) $container['configModel']->get(Plugin::EXTERNAL_LINK_CURSOR_KEY),
    'The compatibility migration must record the latest checked external-link ID.'
);

$rollbackLinkId = $container['db']->table('task_has_external_links')->persist(array(
    'link_type' => NextcloudLinkProvider::LEGACY_TYPE,
    'dependency' => 'related',
    'title' => 'Rollback-created Nextcloud item',
    'url' => 'https://cloud.example.test/nextcloud/index.php/s/rollback-link',
    'date_creation' => time(),
    'date_modification' => time(),
    'task_id' => $migrationTaskId,
    'creator_id' => 0,
));
taskLinkerAssertTrue($rollbackLinkId !== false, 'The rollback-created external-link fixture must be created.');
$container['memoryCache']->flush();
$plugin->initialize();
$migratedRollbackLink = $container['db']->table('task_has_external_links')->eq('id', $rollbackLinkId)->findOne();
taskLinkerAssertSame(
    NextcloudLinkProvider::TYPE,
    $migratedRollbackLink['link_type'],
    'A re-upgrade must migrate legacy links created after the earlier migration.'
);
taskLinkerAssertSame(
    'Rollback-created Nextcloud item',
    $migratedRollbackLink['title'],
    'The re-upgrade migration must preserve rollback-created external-link data.'
);
$container['memoryCache']->flush();
taskLinkerAssertSame(
    (int) $rollbackLinkId,
    (int) $container['configModel']->get(Plugin::EXTERNAL_LINK_CURSOR_KEY),
    'The re-upgrade migration must advance the latest checked external-link ID.'
);

taskLinkerAssertTrue(isset($container['taskGraphModel']), 'The bounded task graph model must be registered.');
