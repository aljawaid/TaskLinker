<?php

namespace Kanboard\Plugin\TaskLinker;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Translator;

class Plugin extends Base
{
    public function initialize()
    {
        // CSS - Asset Hook
        //  - Keep filename lowercase
        $this->hook->on('template:layout:css', array('template' => 'plugins/TaskLinker/Assets/css/task-linker.css'));
        $this->hook->on('template:layout:css', array('template' => 'plugins/TaskLinker/Assets/css/vis.css'));

        // JS - Asset Hook
        //  - Keep filename lowercase
        $this->hook->on('template:layout:js', array('template' => 'plugins/TaskLinker/Assets/js/task-linker.js'));
        $this->hook->on('template:layout:js', array('template' => 'plugins/TaskLinker/Assets/js/task-linker-graph-builder.js'));
        $this->hook->on('template:layout:js', array('template' => 'plugins/TaskLinker/Assets/js/vis.min.js'));

        // Views - Template Hook
        //  - Override name should start lowercase e.g. pluginNameExampleCamelCase
        //$this->template->hook->attach('template:project-header:view-switcher-before-project-overview', 'pluginNameExampleCamelCase:project_header/actions');

        // Views - Add Menu Item - Template Hook
        //  - Override name should start lowercase e.g. pluginNameExampleCamelCase
        //  - Example for menu item in kanboard settings page: $this->template->hook->attach('template:config:sidebar', 'pluginNameExampleCamelCase:config/sidebar');

        // Extra Page - Routes
        //  - Example: $this->route->addRoute('/my/custom/route', 'MyController', 'show', 'PluginNameExampleStudlyCaps');
        //  - Must have the corresponding action in the matching controller
        //$this->route->addRoute('/ / ', ' ', ' ', 'TaskLinker');
    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__ . '/Locale');
    }

    public function getPluginName()
    {
        // Plugin Name MUST be identical to namespace for Plugin Directory to detect updated versions
        // Do not translate the plugin name here
        return 'TaskLinker';
    }

    public function getPluginDescription()
    {
        return t('description text');
    }

    public function getPluginAuthor()
    {
        return 'aljawaid';
    }

    public function getPluginVersion()
    {
        return '1.0.0';
    }

    public function getCompatibleVersion()
    {
        // Examples:
        // >=1.0.37
        // <1.0.37
        // <=1.0.37
        return '>=1.2.20';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/aljawaid/TaskLinker';
    }
}
