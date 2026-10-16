<?php

namespace Kanboard\Plugin\TaskLinker\Controller;

use Kanboard\Controller\BaseController;
use Kanboard\Core\Controller\AccessForbiddenException;
use Kanboard\Plugin\TaskLinker\Plugin;

/**
 * Displays a bounded relationship graph for an authorised task.
 *
 * Related test file: tests/GraphIntegrationTest.php.
 */
class TaskLinkerController extends BaseController
{
    /**
     * Display the relationship graph.
     *
     * @return void
     */
    public function show()
    {
        $task = $this->getTask();
        $userId = (int) $this->userSession->getId();

        // Related tasks can cross project boundaries, so the root is checked explicitly too.
        if (!$this->projectPermissionModel->isUserAllowed((int) $task['project_id'], $userId)) {
            throw new AccessForbiddenException();
        }

        $maxNodes = Plugin::normalizeGraphMaxNodes(
            $this->configModel->get(Plugin::GRAPH_MAX_NODES_KEY, Plugin::GRAPH_MAX_NODES_DEFAULT)
        );
        $graph = $this->taskGraphModel->build($task, $userId, $maxNodes);

        $this->response->html($this->helper->layout->task('taskLinker:task/show', array(
            'title' => t('Relationship graph'),
            'task' => $task,
            'project' => $this->projectModel->getById($task['project_id']),
            'graph' => $graph,
            'max_nodes' => $maxNodes,
        )));
    }
}
