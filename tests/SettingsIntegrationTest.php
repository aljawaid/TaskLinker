<?php

/**
 * Verifies Controller/TaskLinkerConfigController.php and Template/config/settings.php.
 */

use Kanboard\Controller\BaseController;
use Kanboard\Core\Security\Role;
use Kanboard\Plugin\TaskLinker\Controller\TaskLinkerConfigController;
use Kanboard\Plugin\TaskLinker\Plugin;

foreach (array('show', 'save') as $action) {
    taskLinkerAssertTrue(
        $container['applicationAuthorization']->isAllowed('TaskLinkerConfigController', $action, Role::APP_ADMIN),
        'Administrators must retain access to TaskLinkerConfigController::' . $action . '.'
    );
    taskLinkerAssertFalse(
        $container['applicationAuthorization']->isAllowed('TaskLinkerConfigController', $action, Role::APP_USER),
        'Ordinary users must not access TaskLinkerConfigController::' . $action . '.'
    );
}

taskLinkerAssertSame(
    BaseController::class,
    get_parent_class(TaskLinkerConfigController::class),
    'The settings controller must not inherit unrelated configuration actions.'
);

$controllerSource = file_get_contents(__DIR__ . '/../Controller/TaskLinkerConfigController.php');
taskLinkerAssertContains('isPost()', $controllerSource, 'Settings writes must require POST requests.');
taskLinkerAssertContains('checkCSRFForm()', $controllerSource, 'Settings writes must validate CSRF tokens.');
taskLinkerAssertContains(
    'ALLOWED_SETTINGS',
    $controllerSource,
    'Settings writes must use an explicit field allowlist.'
);
taskLinkerAssertContains(
    'array_diff(array_keys($submitted)',
    $controllerSource,
    'Unexpected setting names must be rejected.'
);

$controller = new TaskLinkerConfigController($container);
$validateSettings = new ReflectionMethod($controller, 'validateSettings');

list($valid, $normalised, $errors) = $validateSettings->invoke($controller, array(
    Plugin::GRAPH_MAX_NODES_KEY => '150',
    Plugin::NEXTCLOUD_ORIGINS_KEY => "https://cloud.example.test/\nhttps://files.example.test/nextcloud/",
));
taskLinkerAssertTrue($valid, 'Legitimate graph and Nextcloud settings must remain valid.');
taskLinkerAssertSame(150, $normalised[Plugin::GRAPH_MAX_NODES_KEY], 'Graph limits must be stored as integers.');
taskLinkerAssertSame(
    "https://cloud.example.test\nhttps://files.example.test/nextcloud",
    $normalised[Plugin::NEXTCLOUD_ORIGINS_KEY],
    'Nextcloud origins must be stored in stable normalised form.'
);
taskLinkerAssertSame(array(), $errors, 'Legitimate settings must not produce errors.');

list($valid, $normalised, $errors) = $validateSettings->invoke($controller, array(
    Plugin::GRAPH_MAX_NODES_KEY => '9999',
    Plugin::NEXTCLOUD_ORIGINS_KEY => 'https://user:password@cloud.example.test/?token=secret',
));
taskLinkerAssertFalse($valid, 'Out-of-range limits and credential-bearing origins must be rejected.');
taskLinkerAssertTrue(isset($errors[Plugin::GRAPH_MAX_NODES_KEY]), 'Invalid graph limits must produce a field error.');
taskLinkerAssertTrue(isset($errors[Plugin::NEXTCLOUD_ORIGINS_KEY]), 'Invalid origins must produce a field error.');

$settingsMarkup = $container['template']->render('taskLinker:config/settings', array(
    'values' => array(
        Plugin::GRAPH_MAX_NODES_KEY => 100,
        Plugin::NEXTCLOUD_ORIGINS_KEY => 'https://cloud.example.test',
    ),
    'errors' => array(),
));
taskLinkerAssertContains('name="task_linker_graph_max_nodes"', $settingsMarkup, 'The graph limit must render.');
taskLinkerAssertContains('name="task_linker_nextcloud_origins"', $settingsMarkup, 'The origin list must render.');
taskLinkerAssertContains('fa-info-circle', $settingsMarkup, 'Complex settings must include information popovers.');
taskLinkerAssertContains('csrf_token', $settingsMarkup, 'The settings form must include Kanboard CSRF protection.');

$container['configModel']->save($normalised);
$container['memoryCache']->flush();
taskLinkerAssertSame(
    Plugin::GRAPH_MAX_NODES_DEFAULT,
    Plugin::normalizeGraphMaxNodes($container['configModel']->get(Plugin::GRAPH_MAX_NODES_KEY)),
    'Invalid submitted values must fall back to the safe graph default rather than expand the graph.'
);
