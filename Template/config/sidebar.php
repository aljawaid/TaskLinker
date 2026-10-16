<?php

// Adds TaskLinker to administrator settings navigation; registered by tests/PluginIntegrationTest.php.
?>
<li <?= $this->app->checkMenuSelection('TaskLinkerConfigController', 'show', 'TaskLinker') ?>>
    <?= $this->url->link(
        t('Task relationships'),
        'TaskLinkerConfigController',
        'show',
        array('plugin' => 'TaskLinker')
    ) ?>
</li>
