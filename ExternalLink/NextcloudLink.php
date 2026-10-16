<?php

namespace Kanboard\Plugin\TaskLinker\ExternalLink;

use Kanboard\Core\ExternalLink\ExternalLinkInterface;
use Kanboard\ExternalLink\BaseLink;

/**
 * Represents a Nextcloud external link without requesting remote metadata.
 *
 * Related test file: tests/NextcloudProviderTest.php.
 */
class NextcloudLink extends BaseLink implements ExternalLinkInterface
{
    /**
     * Return a predictable title without exposing share tokens or storing credentials.
     *
     * @return string
     */
    public function getTitle()
    {
        $path = (string) parse_url($this->url, PHP_URL_PATH);

        if (preg_match('#/(?:index\.php/)?s/[^/]+/?$#', $path)) {
            return t('Nextcloud shared item');
        }

        if (strpos($path, '/apps/files/') !== false || preg_match('#/f/\d+/?$#', $path)) {
            return t('Nextcloud file');
        }

        return t('Nextcloud link');
    }
}
