<?php

namespace Kanboard\Plugin\TaskLinker\ExternalLink;

use InvalidArgumentException;

/**
 * Normalises administrator-approved Nextcloud URLs and matches submitted links.
 *
 * Related test file: tests/NextcloudProviderTest.php.
 */
final class NextcloudUrlPolicy
{
    public const MAX_ORIGINS = 10;
    public const MAX_INPUT_LENGTH = 4096;

    /**
     * Parse and normalise one HTTPS base URL per line.
     *
     * @param  string $input
     * @return array
     * @throws InvalidArgumentException
     */
    public static function parseOrigins($input)
    {
        $input = trim((string) $input);
        if ($input === '') {
            return array();
        }

        if (strlen($input) > self::MAX_INPUT_LENGTH) {
            throw new InvalidArgumentException('The origin list is too long.');
        }

        $lines = preg_split('/\R+/', $input);
        if ($lines === false || count($lines) > self::MAX_ORIGINS) {
            throw new InvalidArgumentException('Too many origins were supplied.');
        }

        $origins = array();
        foreach ($lines as $line) {
            $origin = self::normalizeOrigin(trim($line));
            $origins[$origin] = $origin;
        }

        return array_values($origins);
    }

    /**
     * Store origins in a stable, human-editable form.
     *
     * @param  array $origins
     * @return string
     */
    public static function serializeOrigins(array $origins)
    {
        return implode("\n", $origins);
    }

    /**
     * Return true only when a link belongs to an approved HTTPS base URL.
     *
     * @param  string $url
     * @param  array  $origins
     * @return boolean
     */
    public static function matches($url, array $origins)
    {
        $parts = self::parseUrl((string) $url, true);
        if ($parts === null) {
            return false;
        }

        foreach ($origins as $origin) {
            $originParts = self::parseUrl($origin, false);
            if ($originParts === null) {
                continue;
            }

            if ($parts['host'] !== $originParts['host'] || $parts['port'] !== $originParts['port']) {
                continue;
            }

            $basePath = $originParts['path'];
            if ($basePath === '' || $parts['path'] === $basePath || strpos($parts['path'], $basePath . '/') === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalise a single configured base URL.
     *
     * @param  string $origin
     * @return string
     * @throws InvalidArgumentException
     */
    private static function normalizeOrigin($origin)
    {
        $parts = self::parseUrl($origin, false);
        if ($parts === null) {
            throw new InvalidArgumentException('Invalid Nextcloud base URL.');
        }

        $host = strpos($parts['host'], ':') !== false ? '[' . $parts['host'] . ']' : $parts['host'];
        $port = $parts['port'] === 443 ? '' : ':' . $parts['port'];

        return 'https://' . $host . $port . $parts['path'];
    }

    /**
     * Parse a URL into the fields needed for exact origin and path matching.
     *
     * @param  string  $url
     * @param  boolean $allowQueryAndFragment
     * @return array|null
     */
    private static function parseUrl($url, $allowQueryAndFragment)
    {
        if ($url === '' || strlen($url) > 2048 || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $parts = parse_url($url);
        if (
            !is_array($parts)
            || strtolower(isset($parts['scheme']) ? $parts['scheme'] : '') !== 'https'
            || empty($parts['host'])
            || isset($parts['user'])
            || isset($parts['pass'])
            || (!$allowQueryAndFragment && (isset($parts['query']) || isset($parts['fragment'])))
        ) {
            return null;
        }

        $port = isset($parts['port']) ? (int) $parts['port'] : 443;
        if ($port < 1 || $port > 65535) {
            return null;
        }

        $path = isset($parts['path']) ? preg_replace('#/+#', '/', $parts['path']) : '';
        if (!is_string($path) || strpos($path, '\\') !== false || preg_match('/[\x00-\x1F\x7F]/', $path)) {
            return null;
        }

        $decodedPath = rawurldecode($path);
        if (preg_match('/[\x00-\x1F\x7F]/', $decodedPath)) {
            return null;
        }

        $decodedSegments = explode('/', $decodedPath);
        if (in_array('.', $decodedSegments, true) || in_array('..', $decodedSegments, true)) {
            return null;
        }

        $path = $path === '/' ? '' : rtrim($path, '/');

        return array(
            'host' => strtolower($parts['host']),
            'port' => $port,
            'path' => $path,
        );
    }
}
