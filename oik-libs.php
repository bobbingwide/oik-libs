<?php // (C) Copyright Bobbing Wide 2015

/*
Plugin Name: oik-libs: Shared Library Repository
Plugin URI: http://www.oik-plugins.com/oik-plugins/oik-libs
Description: Master repository for shared libraries
Version: 0.0.1
Author: bobbingwide
Author URI: http://www.oik-plugins.com/author/bobbingwide
Text Domain: oik-lib
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2015 Bobbing Wide (email : herb@bobbingwide.com )

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
 * It's not intended to be used as an active plugin, though it may eventually be usefull for 
 * providing a method for helping you resolve plugin dependency hell.
 * 
 * In the mean time, it's simply a place where the latest version (the master version) of each 'library' can be stored.
 * When a plugin that delivers shared libraries is updated then some form of merge is performed with the master library.
 *
 * We don't store the repository in oik-lib; oik-lib is just the "manager"
 * It shares libraries too. 
 *  
 */									
function oik_libs_loaded() {
	
}


oik_libs_loaded();

