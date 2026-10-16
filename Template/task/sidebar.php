<?php

// Adds the relationship graph to Kanboard's task navigation; registered by tests/PluginIntegrationTest.php.
?>
<li <?= $this->app->checkMenuSelection('TaskLinkerController', 'show', 'TaskLinker') ?>>
    <i class="fa fa-share-alt fa-fw" aria-hidden="true"></i>
    <?= $this->url->link(t('Relationship graph'), 'TaskLinkerController', 'show', array(
        'plugin' => 'TaskLinker',
        'task_id' => $task['id'],
        'project_id' => $task['project_id'],
    )) ?>
</li>
