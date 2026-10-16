<?php

// Renders the graph canvas and a normal-link fallback covered by tests/GraphIntegrationTest.php.
$jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE;
$nodesJson = json_encode($graph['nodes'], $jsonFlags);
$edgesJson = json_encode($graph['edges'], $jsonFlags);
?>
<?= $this->asset->css('plugins/TaskLinker/Assets/vendor/vis-network/vis-network.min.css') ?>
<?= $this->asset->css('plugins/TaskLinker/Assets/css/task-linker.css') ?>
<?= $this->asset->js('plugins/TaskLinker/Assets/vendor/vis-network/vis-network.min.js') ?>
<?= $this->asset->js('plugins/TaskLinker/Assets/js/task-linker-graph-builder.js') ?>

<section class="task-linker-page" aria-labelledby="task-linker-heading">
    <div class="page-header">
        <h2 id="task-linker-heading">
            <?= t('Relationship graph') ?>
            <?= $this->app->tooltipHTML($this->text->e(t(
                'The graph includes only tasks from projects you can access. Select a node to open that task.'
            ))) ?>
        </h2>
    </div>

    <?php if ($graph['truncated']): ?>
        <p class="task-linker-notice" role="status">
            <?= e(
                'The graph reached its configured limit of %d tasks. ' .
                'Increase the limit in TaskLinker settings if a larger graph is required.',
                $max_nodes
            ) ?>
        </p>
    <?php endif ?>

    <?php if ($graph['linked_count'] === 0): ?>
        <p class="task-linker-notice"><?= t('This task has no linked tasks.') ?></p>
    <?php endif ?>

    <div class="task-linker-toolbar">
        <p class="task-linker-summary">
            <?= t(
                $graph['linked_count'] === 1 ? '%d linked task shown.' : '%d linked tasks shown.',
                $graph['linked_count']
            ) ?>
        </p>
        <button type="button" class="btn" data-task-linker-action="fit">
            <i class="fa fa-compress" aria-hidden="true"></i> <?= t('Fit graph') ?>
        </button>
    </div>

    <ul class="task-linker-legend" aria-label="<?= $this->text->e(t('Graph legend')) ?>">
        <li>
            <span class="task-linker-legend-swatch task-linker-legend-swatch-current" aria-hidden="true"></span>
            <?= t('Current task') ?>
        </li>
        <li>
            <span class="task-linker-legend-swatch task-linker-legend-swatch-closed" aria-hidden="true"></span>
            <?= t('Closed task') ?>
        </li>
    </ul>

    <div
        id="task-linker-network"
        class="task-linker-network"
        role="region"
        aria-label="<?= $this->text->e(t('Interactive task relationship graph')) ?>"
        tabindex="0"
    ></div>

    <script type="application/json" id="task-linker-nodes"><?= $nodesJson === false ? '[]' : $nodesJson ?></script>
    <script type="application/json" id="task-linker-edges"><?= $edgesJson === false ? '[]' : $edgesJson ?></script>

    <details class="task-linker-task-list">
        <summary><?= t('Linked tasks list') ?></summary>
        <?php if ($graph['linked_count'] === 0): ?>
            <p><?= t('No linked tasks are available.') ?></p>
        <?php else: ?>
            <ul>
                <?php foreach ($graph['nodes'] as $node): ?>
                    <?php if (!$node['is_current']): ?>
                        <li>
                            <a href="<?= $this->text->e($node['url']) ?>">
                                #<?= (int) $node['id'] ?> <?= $this->text->e($node['title_text']) ?>
                            </a>
                            <?php if ($node['project_name'] !== ''): ?>
                                <span>— <?= $this->text->e($node['project_name']) ?></span>
                            <?php endif ?>
                            <?php if (!$node['is_active']): ?>
                                <span>(<?= t('Closed') ?>)</span>
                            <?php endif ?>
                        </li>
                    <?php endif ?>
                <?php endforeach ?>
            </ul>
        <?php endif ?>
    </details>

    <noscript>
        <p class="task-linker-notice">
            <?= t('JavaScript is required for the interactive graph. Use the linked tasks list instead.') ?>
        </p>
    </noscript>
</section>
