<?php

namespace Kanboard\Plugin\TaskLinker\ExternalLink;

use Kanboard\Core\ExternalLink\ExternalLinkProviderInterface;
use Kanboard\ExternalLink\BaseLinkProvider;
use Kanboard\ExternalLink\WebLink;
use Pimple\Container;

/**
 * Recognises links belonging to administrator-approved Nextcloud sites.
 *
 * Related test file: tests/NextcloudProviderTest.php.
 */
class NextcloudLinkProvider extends BaseLinkProvider implements ExternalLinkProviderInterface
{
    public const TYPE = 'weblink';
    public const LEGACY_TYPE = 'task_linker_nextcloud';


    /**
     * @var array
     */
    private $origins;

    /**
     * @param Container $container
     * @param array     $origins
     */
    public function __construct(Container $container, array $origins)
    {
        parent::__construct($container);
        $this->origins = $origins;
    }

    public function getName()
    {
        return t('Web Link');
    }

    public function getType()
    {
        return self::TYPE;
    }

    public function getDependencies()
    {
        return array(
            'related' => t('Related'),
        );
    }

    public function match()
    {
        $startsWithHttp = strpos($this->userInput, 'http://') === 0
            || strpos($this->userInput, 'https://') === 0;

        return $startsWithHttp && filter_var($this->userInput, FILTER_VALIDATE_URL) !== false;
    }

    public function getLink()
    {
        // Store the core Web Link type so preserved records remain usable after uninstall.
        $link = NextcloudUrlPolicy::matches($this->userInput, $this->origins)
            ? new NextcloudLink($this->container)
            : new WebLink($this->container);
        $link->setUrl($this->userInput);

        return $link;
    }
}
