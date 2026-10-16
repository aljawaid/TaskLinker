# TaskLinker

TaskLinker makes Kanboard's internal task relationships easier to follow. It
provides a responsive relationship graph and can recognise links from approved
Nextcloud sites without connecting to a Nextcloud API.

[![Code Scan](https://github.com/aljawaid/TaskLinker/actions/workflows/linter.yml/badge.svg?branch=master&event=push)](https://github.com/aljawaid/TaskLinker/actions/workflows/linter.yml)
[![PHP Compatibility](https://github.com/aljawaid/TaskLinker/actions/workflows/php-compatibility.yaml/badge.svg?branch=master&event=push)](https://github.com/aljawaid/TaskLinker/actions/workflows/php-compatibility.yaml)

## Features

- Opens a relationship graph from each task's information sidebar.
- Follows linked tasks across projects only when the current user can access
  those projects.
- Limits large graphs to an administrator-configured number of tasks.
- Provides responsive, theme-aware controls and a normal linked-task list for
  keyboard, assistive-technology and no-JavaScript access.
- Recognises external links from up to 10 administrator-approved Nextcloud
  HTTPS base URLs.
- Uses generic Nextcloud link titles without requesting remote metadata or
  storing credentials.

## Configuration

Administrators can open **Settings > Task relationships** to:

- set the maximum graph size from 10 to 250 tasks; and
- enter approved Nextcloud HTTPS base URLs, one per line.

Leaving the Nextcloud list empty disables that provider. The provider only
classifies a matching URL already entered into Kanboard; it does not browse
files, authenticate to Nextcloud or make a remote request.

## Compatibility

- Kanboard 1.2.54 or later.
- PHP 8.2, 8.3, 8.4 and 8.5.
- No Kanboard core overrides.
- No database migrations.

See [INSTALL.md](INSTALL.md) for installation and upgrade instructions, and
[changelog.md](changelog.md) for release notes.

## Third-party software

The graph was inspired by the
[Relationgraph plugin](https://github.com/TimoStahl/kanboard_plugin_relationgraph).
TaskLinker includes the `vis-network` 10.1.2 browser distribution. Its MIT and
Apache-2.0 licence files are preserved in `Assets/vendor/vis-network`.

## Licence

TaskLinker is distributed under the [MIT Licence](LICENSE).
