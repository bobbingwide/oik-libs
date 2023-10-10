<?php
/*
Plugin Name: oik-libs
Plugin URI: https://www.oik-plugins.com/oik-plugins/oik-libs-shared-library-repository
Description: Master repository for shared libraries
Version: 0.4.3
Author: bobbingwide
Author URI: https://bobbingwide.com/about-bobbing-wide
Text Domain: oik-libs
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2015-2023 Bobbing Wide (email : herb@bobbingwide.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/
									
/**
 * Function to invoke when oik-libs is loaded
 * 
 * If you're using Composer then this plugin may be used as the master repository of the latest
 * version of the library files. 
 * 
 * It's not intended to be used as an active plugin, though it may eventually be useful for 
 * providing a method for helping you resolve plugin dependency hell.
 * 
 * In the mean time, it's simply a place where the latest version (the master version) of each 'library' can be stored.
 * When a plugin that delivers shared libraries is updated then some form of merge is performed with the master library.
 *
 * We don't store the repository in oik-lib; oik-lib is just the "manager".
 * It shares libraries too. 
 *  
 */									
function oik_libs_loaded() {
	
}


oik_libs_loaded();

