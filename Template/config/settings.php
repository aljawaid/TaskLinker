<?php

use Kanboard\Plugin\TaskLinker\Plugin;

// The administrator and request-integrity boundaries are covered by tests/SettingsIntegrationTest.php.
?>
<?= $this->asset->css('plugins/TaskLinker/Assets/css/task-linker.css') ?>

<div class="task-linker-page">
    <div class="page-header">
        <h2>
            <?= t('Task relationships') ?>
            <?= $this->app->tooltipHTML($this->text->e(t(
                'Control graph size and the Nextcloud sites that TaskLinker can recognise.'
            ))) ?>
        </h2>
    </div>

    <form
        method="post"
        action="<?= $this->url->href('TaskLinkerConfigController', 'save', array('plugin' => 'TaskLinker')) ?>"
        autocomplete="off"
    >
        <?= $this->form->csrf() ?>

        <fieldset>
            <legend><?= t('Relationship graph') ?></legend>

            <div class="task-linker-settings-field">
                <?= $this->form->label(t('Maximum tasks in one graph'), Plugin::GRAPH_MAX_NODES_KEY) ?>
                <?= $this->form->number(
                    Plugin::GRAPH_MAX_NODES_KEY,
                    $values,
                    $errors,
                    array('min="10"', 'max="250"', 'step="10"', 'required')
                ) ?>
                <p class="form-help">
                    <?= t('A bounded graph keeps large relationship networks responsive. The default is 100 tasks.') ?>
                </p>
            </div>
        </fieldset>

        <fieldset>
            <legend>
                <?= t('Nextcloud links') ?>
                <?= $this->app->tooltipHTML($this->text->e(t(
                    'TaskLinker only recognises matching links. It does not store credentials, ' .
                    'browse files or request private metadata.'
                ))) ?>
            </legend>

            <div class="task-linker-settings-field">
                <?= $this->form->label(t('Approved Nextcloud base URLs'), Plugin::NEXTCLOUD_ORIGINS_KEY) ?>
                <?= $this->form->textarea(
                    Plugin::NEXTCLOUD_ORIGINS_KEY,
                    $values,
                    $errors,
                    array('placeholder="https://cloud.example.com"', 'spellcheck="false"')
                ) ?>
                <p class="form-help">
                    <?= t('Enter up to 10 HTTPS base URLs, one per line. Leave this empty to disable the provider.') ?>
                </p>
            </div>
        </fieldset>

        <div class="form-actions">
            <button type="submit" class="btn btn-blue">
                <i class="fa fa-save" aria-hidden="true"></i> <?= t('Save') ?>
            </button>
            <?= $this->url->link(t('Back to Settings'), 'ConfigController', 'index', array(), false, 'btn') ?>
        </div>
    </form>
</div>
