<?php

namespace Kanboard\Plugin\TaskLinker\ExternalLink;

use Kanboard\Core\ExternalLink\ExternalLinkProviderInterface;
use Kanboard\ExternalLink\BaseLinkProvider;
use Pimple\Container;

/**
 * Recognises links belonging to administrator-approved Nextcloud sites.
 *
 * Related test file: tests/NextcloudProviderTest.php.
 */
class NextcloudLinkProvider extends BaseLinkProvider implements ExternalLinkProviderInterface
{
    public const TYPE = 'task_linker_nextcloud';

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
        return t('Nextcloud link');
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
        return NextcloudUrlPolicy::matches($this->userInput, $this->origins);
    }

    public function getLink()
    {
        $link = new NextcloudLink($this->container);
        $link->setUrl($this->userInput);

        return $link;
    }
}
