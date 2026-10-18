# Changelog

## v1.1.2 - 2026-10-18

- Fixed preserved Nextcloud links breaking task pages after TaskLinker was
  uninstalled by storing them as Kanboard Web Links.
- Added an automatic compatibility migration for links created by TaskLinker
  1.1.0 and 1.1.1 without deleting their titles, URLs or relationships.
- Retained normal Web Link matching and metadata behaviour for links outside
  the configured Nextcloud origins.

## v1.1.1 - 2026-10-18

- Increased the theme-derived muted text contrast so graph summaries remain
  readable at WCAG AA contrast in Kanboard Light and Dark.

## v1.1.0 - 2026-10-18

- Added a bounded, permission-aware relationship graph for internal task links.
- Added responsive, theme-aware presentation and an accessible linked-task
  fallback.
- Added an administrator-configured Nextcloud external-link provider without
  credentials or remote metadata requests. Closes #2.
- Replaced the obsolete graph bundle with page-scoped `vis-network` 10.1.2
  assets and preserved its upstream licences.
- Added complete US and British English catalogues and a corrected translation
  starter template.
- Raised the supported baseline to Kanboard 1.2.54 and added focused regression
  checks for PHP 8.2 to 8.5.
- Removed obsolete graph assets and placeholder files.

## v1.0

- Added the initial TaskLinker plugin.
- Added a translation starter template.
