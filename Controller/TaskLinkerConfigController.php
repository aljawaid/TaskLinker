<?php

namespace Kanboard\Plugin\TaskLinker\Controller;

use InvalidArgumentException;
use Kanboard\Controller\BaseController;
use Kanboard\Core\Controller\AccessForbiddenException;
use Kanboard\Plugin\TaskLinker\ExternalLink\NextcloudUrlPolicy;
use Kanboard\Plugin\TaskLinker\Plugin;

/**
 * Displays and saves administrator-only TaskLinker settings.
 *
 * Related test file: tests/SettingsIntegrationTest.php.
 */
class TaskLinkerConfigController extends BaseController
{
    public const ALLOWED_SETTINGS = array(
        Plugin::GRAPH_MAX_NODES_KEY,
        Plugin::NEXTCLOUD_ORIGINS_KEY,
    );

    /**
     * Display TaskLinker settings.
     *
     * @param  array $values
     * @param  array $errors
     * @return void
     */
    public function show(array $values = array(), array $errors = array())
    {
        $this->assertAdmin();

        if ($values === array()) {
            $values = array(
                Plugin::GRAPH_MAX_NODES_KEY => $this->configModel->get(
                    Plugin::GRAPH_MAX_NODES_KEY,
                    Plugin::GRAPH_MAX_NODES_DEFAULT
                ),
                Plugin::NEXTCLOUD_ORIGINS_KEY => $this->configModel->get(
                    Plugin::NEXTCLOUD_ORIGINS_KEY,
                    ''
                ),
            );
        }

        $this->response->html($this->helper->layout->config('taskLinker:config/settings', array(
            'title' => t('Settings') . ' &gt; ' . t('Task relationships'),
            'values' => $values,
            'errors' => $errors,
        )));
    }

    /**
     * Save validated graph and Nextcloud settings.
     *
     * @return void
     */
    public function save()
    {
        $this->assertAdmin();

        if (!$this->request->isPost()) {
            throw new AccessForbiddenException();
        }

        $this->checkCSRFForm();

        $submitted = $this->request->getValues();
        if (array_diff(array_keys($submitted), self::ALLOWED_SETTINGS) !== array()) {
            throw new AccessForbiddenException();
        }

        list($valid, $values, $errors) = $this->validateSettings($submitted);
        if (!$valid) {
            $this->show($submitted, $errors);
            return;
        }

        if ($this->configModel->save($values)) {
            $this->languageModel->loadCurrentLanguage();
            $this->flash->success(t('TaskLinker settings saved successfully.'));
        } else {
            $this->flash->failure(t('Unable to save TaskLinker settings.'));
        }

        $this->response->redirect($this->helper->url->to(
            'TaskLinkerConfigController',
            'show',
            array('plugin' => 'TaskLinker')
        ));
    }

    /**
     * Validate and normalise settings before they enter global configuration storage.
     *
     * @param  array $submitted
     * @return array
     */
    private function validateSettings(array $submitted)
    {
        $errors = array();
        $maxNodes = isset($submitted[Plugin::GRAPH_MAX_NODES_KEY])
            && is_scalar($submitted[Plugin::GRAPH_MAX_NODES_KEY])
            ? filter_var($submitted[Plugin::GRAPH_MAX_NODES_KEY], FILTER_VALIDATE_INT, array(
                'options' => array(
                    'min_range' => Plugin::GRAPH_MAX_NODES_MIN,
                    'max_range' => Plugin::GRAPH_MAX_NODES_MAX,
                ),
            ))
            : false;

        if ($maxNodes === false) {
            $errors[Plugin::GRAPH_MAX_NODES_KEY] = array(t('Enter a graph limit between 10 and 250.'));
        }

        $originInput = isset($submitted[Plugin::NEXTCLOUD_ORIGINS_KEY])
            && is_scalar($submitted[Plugin::NEXTCLOUD_ORIGINS_KEY])
            ? trim((string) $submitted[Plugin::NEXTCLOUD_ORIGINS_KEY])
            : '';

        if (strlen($originInput) > NextcloudUrlPolicy::MAX_INPUT_LENGTH) {
            $errors[Plugin::NEXTCLOUD_ORIGINS_KEY] = array(
                t('The Nextcloud origins list is too long.')
            );
            $origins = array();
        } else {
            try {
                $origins = NextcloudUrlPolicy::parseOrigins($originInput);
            } catch (InvalidArgumentException $exception) {
                $errors[Plugin::NEXTCLOUD_ORIGINS_KEY] = array(t(
                    'Enter no more than 10 HTTPS Nextcloud base URLs, one per line, ' .
                    'without credentials, queries or fragments.'
                ));
                $origins = array();
            }
        }

        return array(
            $errors === array(),
            array(
                Plugin::GRAPH_MAX_NODES_KEY => $maxNodes === false
                    ? Plugin::GRAPH_MAX_NODES_DEFAULT
                    : (int) $maxNodes,
                Plugin::NEXTCLOUD_ORIGINS_KEY => NextcloudUrlPolicy::serializeOrigins($origins),
            ),
            $errors,
        );
    }

    /**
     * Keep direct controller calls inside the administrator boundary.
     *
     * @return void
     */
    private function assertAdmin()
    {
        if (!$this->userSession->isAdmin()) {
            throw new AccessForbiddenException();
        }
    }
}
