# TaskLinker installation

## Requirements

- Kanboard 1.2.54 or later.
- PHP 8.2 to 8.5.
- Permission to create the Kanboard `plugins` directory or write inside it.

TaskLinker uses Kanboard's existing database tables and does not add or alter
the database schema. Upgrades can normalise legacy TaskLinker external links
to Kanboard Web Links and record migration progress in the existing
configuration table. It does not require Composer or npm on the Kanboard
server.

## Install from a downloaded archive

1. Download the repository archive from
   [GitHub](https://github.com/aljawaid/TaskLinker).
2. Extract it outside the Kanboard installation.
3. Rename the extracted directory to exactly `TaskLinker` if the branch name
   was included in the archive name.
4. Copy the complete directory to `kanboard/plugins/TaskLinker`.
5. Sign in to Kanboard as an administrator and confirm that TaskLinker appears
   on the **Plugins** page.
6. Open **Settings > Task relationships** and review the graph limit and
   optional Nextcloud base URLs.

The plugin directory name is case-sensitive on Linux and must be `TaskLinker`.

## Install with Git

From Kanboard's `plugins` directory, run:

```shell
git clone https://github.com/aljawaid/TaskLinker.git TaskLinker
```

Pin production installations to a reviewed release tag when one is available;
do not leave a production server following an unreviewed development branch.

## Upgrade

1. Take the normal Kanboard application and configuration backup.
2. Replace the existing `plugins/TaskLinker` files with the reviewed new
   version, preserving the exact directory name.
3. Do not merge old and new vendor asset directories.
4. Sign in and verify the plugin version, a representative relationship graph,
   and one configured Nextcloud link.
5. If validation fails, restore the previous plugin directory from the backup.

TaskLinker stores its settings and external links in Kanboard's existing
tables, so the normal Kanboard database backup protects them.
