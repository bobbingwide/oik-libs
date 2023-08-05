=== oik-libs ===
Contributors: bobbingwide
Donate link: https://www.oik-plugins.com/oik/oik-donate/
Tags: shared, library, repository
Requires at least: 4.9.8
Tested up to: 6.3
Stable tag: 0.4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
oik-libs: Shared Library Repository

The Master repository for shared libraries.

Contains the latest versions of the library files that cooperate using the shared library management API.

This plugin is a stop gap until a proper solution for managing the delivery of shared libraries is 
implemented using Composer or a similar solution.

== Installation ==
1. Upload the contents of the oik-libs plugin to the `/wp-content/plugins/oik-libs' directory
1. Don't bother to activate the oik-libs plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= What shared libraries are available? = 
See the FAQs for oik-lib

= How does this integrate with Composer? =
TBC


== Screenshots ==
None

== Upgrade Notice ==
= 0.4.2 = 
Synchronized with oik v4.9.1

= 0.4.1 = 
Synchronized with oik v4.4.0

= 0.4.0 = 
Synchronized with oik v4.2.0, oik-css v1.3.0, and oik-bob-bing-wide v1.35.1

= 0.3.0 = 
Synchronized with oik v4.1.1, oik-bwtrace v3.1.0, etc

= 0.2.0 =
Synchronized with oik v3.3.3, oik-bwtrace v3.0.0-RC1, etc

= 0.1.0 =
Synchronized with oik v3.2.9, oik-batch v1.0.0

= 0.0.8 = 
Synchronized with oik v3.2.6, oik-bwtrace v3.0.0-alpha, oik-batch v0.9.4, etc

= 0.0.7 = 
Synchronized with oik v3.2.3, oik-bwtrace v2.1.1, oik-lib v0.1.1, etc

= 0.0.6 = 
Synchronized with oik v3.2.0-RC1, oik-bwtrace v2.1.1-beta-20171023, oik-lib v0.1.0, oik-weight-zone-shipping-pro v0.2.2, and genesis-image v1.1.1

= 0.0.5 =
Synchronized with oik v3.0.1, oik-bwtrace v2.0.12 and oik-lib v0.0.7

= 0.0.4 = 
Synchronized with oik v3.0.0-RC3, oik-lib, oik-bwtrace and oik-batch

= 0.0.3 =
Synchronized with oik v3.0.0-alpha.0917, oik-bwtrace v2.0.7 and oik-lib v0.0.3

= 0.0.2 = 
Synchronized with oik v3.0.0-alpha.0806, oik-bwtrace v2.0.1 and oik-lib v0.0.2

= 0.0.1 =
First version containing the merged libraries from: oik-lib, oik base plugin and oik-bwtrace

== Changelog ==
= 0.4.2 = 
* Changed: Make compatible with WordPress 6.3,[github bobbingwide oik-libs issues 18]
* Changed: Reconcile oik-libs,[github bobbingwide oik-libs issues 16] 
* Tested: With WordPress 6.3 and WordPress Multi Site
* Tested: With PHP 8.0
* Tested: With PHPUnit 9

= 0.4.1 = 
* Changed: Reconcile bw_fields.php with oik
* Tested: With WordPress 5.8 and WordPress Multi Site
* Tested: With PHP 8.0
* Tested: With PHPUnit 9

= 0.4.0 =
* Added: libs/class-oik-attachment-contents and unit test,[github bobbingwide oik-libs issues 14]
* Changed: Expect loading=lazy,[github bobbingwide oik-libs issues 13]
* Changed: Reconcile libs with changes made for PHP 8,[github bobbingwide oik-libs issues 13]
* Changed: Reconcile with oik autoload updates,[github bobbingwide oik-libs issues 13]
* Tested: With WordPress 5.7.2 and WordPress Multi Site
* Tested: With PHP 8.0
* Tested: With PHPUnit 9

= 0.3.0 = 
* Changed: Reconcile shared libraries and update PHPUnit tests,[github bobbingwide oik-libs issues 12]
* Tested: With WordPress 5.6
* Tested: With PHPUnit 8
* Tested: With PHP 7.4

= 0.2.0 =
* Changed: Update tests for running on s.b.
* Tested: oik issue 126 [github bobbingwide oik issue 126]
* Tested: oik issue 114
* Tested: oik-libs issue 9
* Tested: With WordPress 5.1.1
* Tested: With PHP 7.2
* Tested: With Gutenberg 5.4.0

= 0.1.0 =
* Added: hexdump, [github bobbingwide oik-libs issue 11]
* Changed: bobbforms, class-oik-remote, oik-cli, [github bobbingwide oik-libs issue 11]
* Changed: Updated PHPUnit tests for WordPress 5.0, [github bobbingwide oik-libs issue 10]
* Tested: With WordPress 5.0.3
* Tested: With PHP 7.2

= 0.0.8 = 
* Added: bw_fields - from oik and oik-fields [github bobbingwide oik-libs issue 9]
* Changed: Updated other library files as necessary

= 0.0.7 = 
* Changed: Update shared library tests for WordPress 4.9 [github bobbingwide oik-libs issue 8]
* Changed: Reconcile tests with oik v3.2.3 and update for environment independence [github bobbingwide oik-libs issue 9]
* Tested: With WordPress 4.9.1 and WordPress Multisite
* Tested: With PHP 7.0, 7.1 and 7.2

= 0.0.6 = 
* Added: Implement i18n/l10n solution for shared library files [github bobbingwide oik-libs issue 7]
* Added: Reconcile updates for oik issue 55 - extract plugin and theme update into shared libraries [github bobbingwide oik-libs issue 5]
* Added: UK English language files 
* Added: bwtrace_log.php
* Added: class-BW-.php
* Added: class-bobbcomp.php
* Added: class-dependencies-cache.php
* Added: class-oik-autoload.php
* Added: class-oik-plugin-update.php
* Added: class-oik-remote.php
* Added: class-oik-theme-update.php
* Added: class-oik-update.php
* Added: oik-l10n.php
* Added: oik_themes.php
* Changed: Change tests associated with oik issue #80
* Changed: Changes associated with oik issue #67
* Changed: bb_BB language files
* Tested: Added PHPUnit tests for shared library functions [github bobbingwide oik-libs issue 6]
* Tested: With PHP 7.0 and 7.1 
* Tested: With WordPress 4.8.2 and 4.9-beta3

= 0.0.5 = 
* Added: libs/oik-honeypot.php
* Changed: Other lib files to synchronize with other plugins

= 0.0.4 = 
* Added: libs/oik-git.php 
* Added: libs/oik-cli.php
* Added: language files - though they may be out of date
* Changed: Other lib files to synchronize with other plugins

= 0.0.3 =
* Added: libs/oik-libs.php - to peform synchronization with other plugins ( Issue #1 )
* Changed: Synchronized with oik v3.0.0-alpha.0917
* Changed: Synchronized with oik-bwtrace v2.0.7
* Changed: Synchronized with oik-lib v0.0.3

= 0.0.2 =
* Changed: See the git log
 
= 0.0.1 
* Added: New dummy WordPress plugin


