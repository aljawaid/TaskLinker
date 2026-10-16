<?php

/**
 * Verifies Model/TaskGraphModel.php and Template/task/show.php.
 */

use Kanboard\Core\Security\Role;

$viewerId = $container['userModel']->create(array(
    'username' => 'task_linker_viewer',
    'password' => 'task-linker-test-password',
    'role' => Role::APP_USER,
    'is_active' => 1,
));
taskLinkerAssertTrue($viewerId !== false, 'The graph fixture user must be created.');

$visibleProjectId = $container['projectModel']->create(array('name' => 'Visible graph project'));
$hiddenProjectId = $container['projectModel']->create(array('name' => 'Hidden graph project'));
taskLinkerAssertTrue($visibleProjectId > 0 && $hiddenProjectId > 0, 'Graph fixture projects must be created.');
taskLinkerAssertTrue(
    $container['projectUserRoleModel']->addUser($visibleProjectId, $viewerId, Role::PROJECT_VIEWER),
    'The graph fixture user must receive visible-project access.'
);

$rootTaskId = $container['taskCreationModel']->create(array(
    'project_id' => $visibleProjectId,
    'title' => 'Root graph task',
));
$visibleTaskId = $container['taskCreationModel']->create(array(
    'project_id' => $visibleProjectId,
    'title' => 'Visible linked task',
));
$hiddenTaskId = $container['taskCreationModel']->create(array(
    'project_id' => $hiddenProjectId,
    'title' => 'Hidden linked task',
));
taskLinkerAssertTrue(
    $rootTaskId > 0 && $visibleTaskId > 0 && $hiddenTaskId > 0,
    'Graph fixture tasks must be created.'
);

$linkTypes = $container['linkModel']->getAll();
taskLinkerAssertTrue($linkTypes !== array(), 'Kanboard must provide a task-link type for graph fixtures.');
$linkTypeId = (int) $linkTypes[0]['id'];
taskLinkerAssertTrue(
    $container['taskLinkModel']->create($rootTaskId, $visibleTaskId, $linkTypeId) !== false,
    'The visible graph fixture link must be created.'
);
taskLinkerAssertTrue(
    $container['taskLinkModel']->create($rootTaskId, $hiddenTaskId, $linkTypeId) !== false,
    'The cross-project graph fixture link must be created.'
);

$viewer = $container['userModel']->getById($viewerId);
$container['userSession']->initialize($viewer);
$rootTask = $container['taskFinderModel']->getDetails($rootTaskId);
$graph = $container['taskGraphModel']->build($rootTask, $viewerId, 100);

taskLinkerAssertSame(2, count($graph['nodes']), 'The graph must include the root and accessible linked task only.');
taskLinkerAssertSame(1, count($graph['edges']), 'Links to inaccessible projects must not enter graph edges.');
taskLinkerAssertFalse($graph['truncated'], 'A small graph must not report truncation.');
taskLinkerAssertNotContains(
    'Hidden linked task',
    var_export($graph, true),
    'Inaccessible task details must not enter graph output.'
);
taskLinkerAssertContains(
    'Visible linked task',
    var_export($graph, true),
    'Accessible linked task details must remain visible.'
);

for ($index = 1; $index <= 10; $index++) {
    $extraTaskId = $container['taskCreationModel']->create(array(
        'project_id' => $visibleProjectId,
        'title' => 'Bounded task ' . $index,
    ));
    taskLinkerAssertTrue($extraTaskId > 0, 'Each bounded graph fixture task must be created.');
    taskLinkerAssertTrue(
        $container['taskLinkModel']->create($rootTaskId, $extraTaskId, $linkTypeId) !== false,
        'Each bounded graph fixture link must be created.'
    );
}

$boundedGraph = $container['taskGraphModel']->build($rootTask, $viewerId, 10);
taskLinkerAssertSame(10, count($boundedGraph['nodes']), 'The graph must enforce the configured node limit.');
taskLinkerAssertTrue($boundedGraph['truncated'], 'A graph over its limit must report truncation.');

$markup = $container['template']->render('taskLinker:task/show', array(
    'task' => $rootTask,
    'graph' => $graph,
    'max_nodes' => 100,
));
taskLinkerAssertContains('vis-network.min.js', $markup, 'The maintained graph library must load on the graph page.');
taskLinkerAssertContains(
    'task-linker-graph-builder.js',
    $markup,
    'The guarded graph builder must load on the graph page.'
);
taskLinkerAssertContains('type="application/json"', $markup, 'Graph data must use inert JSON script elements.');
taskLinkerAssertContains('Visible linked task', $markup, 'The normal-link fallback must include accessible tasks.');
taskLinkerAssertNotContains(
    'Hidden linked task',
    $markup,
    'The normal-link fallback must exclude inaccessible tasks.'
);
taskLinkerAssertContains('csrf', strtolower($container['template']->render('taskLinker:config/settings', array(
    'values' => array(
        'task_linker_graph_max_nodes' => 100,
        'task_linker_nextcloud_origins' => '',
    ),
    'errors' => array(),
))), 'The settings form must retain CSRF protection.');
