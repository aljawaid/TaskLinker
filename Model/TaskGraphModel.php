<?php

namespace Kanboard\Plugin\TaskLinker\Model;

use Kanboard\Core\Base;

/**
 * Builds bounded graph data from task links visible to the current user.
 *
 * Related test file: tests/GraphIntegrationTest.php.
 */
class TaskGraphModel extends Base
{
    /**
     * Build graph nodes and edges without crossing project access boundaries.
     *
     * @param  array   $rootTask
     * @param  integer $userId
     * @param  integer $maxNodes
     * @return array
     */
    public function build(array $rootTask, $userId, $maxNodes)
    {
        $rootTaskId = (int) $rootTask['id'];
        $maxNodes = max(1, (int) $maxNodes);
        $nodes = array($rootTaskId => $this->buildNode($rootTask, $rootTaskId));
        $edges = array();
        $queued = array($rootTaskId => true);
        $queue = array($rootTask);
        $truncated = false;

        while ($queue !== array()) {
            $task = array_shift($queue);
            $taskId = (int) $task['id'];

            foreach ($this->taskLinkModel->getAllGroupedByLabel($taskId) as $label => $links) {
                foreach ($links as $link) {
                    $linkedTaskId = isset($link['task_id']) ? (int) $link['task_id'] : 0;
                    if ($linkedTaskId <= 0 || $linkedTaskId === $taskId) {
                        continue;
                    }

                    $edgeKeyParts = array($taskId, $linkedTaskId);
                    sort($edgeKeyParts, SORT_NUMERIC);
                    $edgeKey = implode(':', $edgeKeyParts);
                    if (isset($edges[$edgeKey])) {
                        continue;
                    }

                    $linkedTask = $this->taskFinderModel->getDetails($linkedTaskId);
                    if (
                        empty($linkedTask)
                        || !$this->projectPermissionModel->isUserAllowed(
                            (int) $linkedTask['project_id'],
                            (int) $userId
                        )
                    ) {
                        continue;
                    }

                    if (!isset($nodes[$linkedTaskId])) {
                        if (count($nodes) >= $maxNodes) {
                            $truncated = true;
                            continue;
                        }

                        $nodes[$linkedTaskId] = $this->buildNode($linkedTask, $rootTaskId);
                    }

                    $edges[$edgeKey] = array(
                        'from' => $taskId,
                        'to' => $linkedTaskId,
                        'label' => t((string) $label),
                        'arrows' => 'to',
                    );

                    if (!isset($queued[$linkedTaskId])) {
                        $queued[$linkedTaskId] = true;
                        $queue[] = $linkedTask;
                    }
                }
            }
        }

        return array(
            'nodes' => array_values($nodes),
            'edges' => array_values($edges),
            'truncated' => $truncated,
            'linked_count' => max(0, count($nodes) - 1),
        );
    }

    /**
     * Convert task details into presentation-safe graph data.
     *
     * @param  array   $task
     * @param  integer $rootTaskId
     * @return array
     */
    private function buildNode(array $task, $rootTaskId)
    {
        $taskId = (int) $task['id'];
        $isActive = !empty($task['is_active']);
        $color = $this->colorModel->getColorProperties($task['color_id']);
        $projectName = isset($task['project_name']) ? (string) $task['project_name'] : '';
        $columnTitle = isset($task['column_title']) ? (string) $task['column_title'] : '';
        $label = '#' . $taskId . ' ' . (string) $task['title'];

        if ($projectName !== '') {
            $label .= "\n" . $projectName;
        }

        return array(
            'id' => $taskId,
            'label' => $label,
            'shape' => 'box',
            'borderWidth' => $taskId === $rootTaskId ? 4 : 2,
            'color' => array(
                'background' => $color['background'],
                'border' => $color['border'],
            ),
            'font' => array('color' => '#111827'),
            'shapeProperties' => array(
                'borderDashes' => $isActive ? false : array(6, 5),
            ),
            'url' => $this->helper->url->to('TaskViewController', 'show', array(
                'task_id' => $taskId,
                'project_id' => (int) $task['project_id'],
            )),
            'title_text' => (string) $task['title'],
            'project_name' => $projectName,
            'column_title' => $columnTitle,
            'is_active' => $isActive,
            'is_current' => $taskId === $rootTaskId,
        );
    }
}
