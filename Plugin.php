<?php

namespace Kanboard\Plugin\TaskLinker;

use InvalidArgumentException;
use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Security\Role;
use Kanboard\Core\Translator;
use Kanboard\Plugin\TaskLinker\ExternalLink\NextcloudLinkProvider;
use Kanboard\Plugin\TaskLinker\ExternalLink\NextcloudUrlPolicy;

/**
 * Registers TaskLinker's graph, settings and optional Nextcloud link provider.
 *
 * Related test files: tests/PluginIntegrationTest.php,
 * tests/GraphIntegrationTest.php and tests/NextcloudProviderTest.php.
 */
class Plugin extends Base
{
    public const GRAPH_MAX_NODES_KEY = 'task_linker_graph_max_nodes';
    public const GRAPH_MAX_NODES_DEFAULT = 100;
    public const GRAPH_MAX_NODES_MIN = 10;
    public const GRAPH_MAX_NODES_MAX = 250;
    public const NEXTCLOUD_ORIGINS_KEY = 'task_linker_nextcloud_origins';

    public function initialize()
    {
        // Graph views inherit Kanboard's project role checks and settings remain administrator-only.
        $this->projectAccessMap->add('TaskLinkerController', '*', Role::PROJECT_VIEWER);
        $this->applicationAccessMap->add('TaskLinkerConfigController', '*', Role::APP_ADMIN);

        $this->route->addRoute(
            '/task/:task_id/relationship-graph',
            'TaskLinkerController',
            'show',
            'TaskLinker'
        );
        $this->route->addRoute(
            '/settings/task-linker',
            'TaskLinkerConfigController',
            'show',
            'TaskLinker'
        );

        // Navigation hooks expose the graph from tasks and settings from the administrator area.
        $this->template->hook->attach('template:task:sidebar:information', 'taskLinker:task/sidebar');
        $this->template->hook->attach('template:config:sidebar', 'taskLinker:config/sidebar');

        // Invalid stored values must not prevent Kanboard from loading the plugin.
        try {
            $origins = NextcloudUrlPolicy::parseOrigins(
                (string) $this->configModel->get(self::NEXTCLOUD_ORIGINS_KEY, '')
            );
        } catch (InvalidArgumentException $exception) {
            $origins = array();
            $this->logger->warning('TaskLinker ignored invalid stored Nextcloud origins.');
        }

        if ($origins !== array()) {
            $this->externalLinkManager->register(new NextcloudLinkProvider($this->container, $origins));
        }
    }

    public function onStartup()
    {
        // English strings provide a complete fallback before an optional locale override is loaded.
        Translator::load('en_US', __DIR__ . '/Locale');

        $language = $this->languageModel->getCurrentLanguage();
        if ($language !== 'en_US') {
            Translator::load($language, __DIR__ . '/Locale');
        }
    }

    public function getClasses()
    {
        return array(
            'Plugin\TaskLinker\Model' => array(
                'TaskGraphModel',
            ),
        );
    }

    /**
     * Keep graph limits within the range supported by the interactive view.
     *
     * @param  mixed $value
     * @return integer
     */
    public static function normalizeGraphMaxNodes($value)
    {
        $validated = filter_var($value, FILTER_VALIDATE_INT, array(
            'options' => array(
                'min_range' => self::GRAPH_MAX_NODES_MIN,
                'max_range' => self::GRAPH_MAX_NODES_MAX,
            ),
        ));

        return $validated === false ? self::GRAPH_MAX_NODES_DEFAULT : (int) $validated;
    }

    public function getPluginName()
    {
        return 'TaskLinker';
    }

    public function getPluginDescription()
    {
        return t('Visualise authorised task relationships and recognise links from configured Nextcloud sites.');
    }

    public function getPluginAuthor()
    {
        return 'aljawaid';
    }

    public function getPluginVersion()
    {
        return '1.1.1';
    }

    public function getCompatibleVersion()
    {
        return '>=1.2.54';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/aljawaid/TaskLinker';
    }
}
