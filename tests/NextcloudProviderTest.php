<?php

/**
 * Verifies ExternalLink/NextcloudUrlPolicy.php, NextcloudLinkProvider.php and NextcloudLink.php.
 */

use Kanboard\Plugin\TaskLinker\ExternalLink\NextcloudLinkProvider;
use Kanboard\Plugin\TaskLinker\ExternalLink\NextcloudUrlPolicy;

$origins = NextcloudUrlPolicy::parseOrigins(
    "https://Cloud.Example.test/nextcloud/\nhttps://cloud.example.test/nextcloud"
);
taskLinkerAssertSame(
    array('https://cloud.example.test/nextcloud'),
    $origins,
    'Configured Nextcloud URLs must be normalised and deduplicated.'
);
taskLinkerAssertTrue(
    NextcloudUrlPolicy::matches('https://cloud.example.test/nextcloud/index.php/s/share-token', $origins),
    'A public share below an approved base path must match.'
);
taskLinkerAssertFalse(
    NextcloudUrlPolicy::matches('https://cloud.example.test/nextcloud-evasion/s/share-token', $origins),
    'A path-prefix lookalike must not match.'
);
taskLinkerAssertFalse(
    NextcloudUrlPolicy::matches('https://cloud.example.test.evil/nextcloud/s/share-token', $origins),
    'A hostname-prefix lookalike must not match.'
);
taskLinkerAssertFalse(
    NextcloudUrlPolicy::matches('http://cloud.example.test/nextcloud/s/share-token', $origins),
    'Unencrypted Nextcloud links must not match.'
);
taskLinkerAssertFalse(
    NextcloudUrlPolicy::matches('https://user:password@cloud.example.test/nextcloud/s/share-token', $origins),
    'Credential-bearing Nextcloud links must not match.'
);
taskLinkerAssertFalse(
    NextcloudUrlPolicy::matches('https://cloud.example.test/nextcloud/%0Ahidden', $origins),
    'Encoded control characters must not match an approved Nextcloud base URL.'
);

try {
    NextcloudUrlPolicy::parseOrigins('http://cloud.example.test');
    throw new RuntimeException('Unencrypted origins must be rejected.');
} catch (\InvalidArgumentException $exception) {
    taskLinkerAssertTrue(true, 'Invalid origins are rejected.');
}

$provider = new NextcloudLinkProvider($container, $origins);
$provider->setUserTextInput('https://cloud.example.test/nextcloud/index.php/s/share-token');
taskLinkerAssertTrue($provider->match(), 'The provider must recognise an approved Nextcloud share.');
taskLinkerAssertSame(
    'task_linker_nextcloud',
    $provider->getType(),
    'The provider type must not replace Kanboard Web Link.'
);
$link = $provider->getLink();
taskLinkerAssertSame('Nextcloud shared item', $link->getTitle(), 'Share tokens must not become external-link titles.');
taskLinkerAssertSame(
    'https://cloud.example.test/nextcloud/index.php/s/share-token',
    $link->getUrl(),
    'The original approved link must be preserved.'
);
