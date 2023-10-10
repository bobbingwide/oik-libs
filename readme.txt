=== oik-libs ===
Contributors: bobbingwide
Donate link: https://www.oik-plugins.com/oik/oik-donate/
Tags: shared, library, repository
Requires at least: 4.9.8
Tested up to: 6.4-beta2
Stable tag: 0.4.3
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
= 0.4.3 = 
Synchronized with updates for PHP 8.1 and PHP 8.2 support.

== Changelog ==
= 0.4.3 = 
* Changed: PHP 8.2: Reconcile changes #19
* Added: PHP 8.2: Test loading all shared library files #19
* Tested: With WordPress 6.3.1 and WordPress Multisite
* Tested: With WordPress 6.4-beta2 and WordPress Multisite
* Tested: With PHP 8.0, PHP 8.1 and PHP 8.2
* Tested: With PHPUnit 9.6