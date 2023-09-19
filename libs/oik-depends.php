<?php // (C) Copyright Bobbing Wide 2012-2023
if ( !defined( "OIK_DEPENDS_INCLUDED" ) ) {
define( "OIK_DEPENDS_INCLUDED", "3.2.8" );

/**
 * Dependency checking library functions
 *
 * Library: oik-depends
 * Provides: oik-depends
 * Type: 
 *
 * Originally implemented in oik/admin/oik-depends.inc 
 * what we primarily need is for oik_lazy_depends() to be loaded
 * so that plugins which have dependencies don't need to load their own 
 * dependency checking function. Historically this function was implemented in 
 * a file called oik-activation.inc
 * 
 *
 */

/**
 * Returns an associative version of the active plugins array  
 *
 * - $active_plugins is an array that looks like column 1 in the table below.
 * - bw_plug() is able to handle it so we can do the same here
 * - EXCEPT bw_plug works at the "top level" not the child plugins
 * - We convert this array into an associative array keyed on the sub-plugin name
 *
 * $active_plugins                                     | $names (keys only - 
 * --------------------------------------------------- | -------------------------------
 *  [0] => abt-featured-heroes/abt-featured-heroes.php |   ['abt-featured-heroes'] 
 *  [1] => effort/tasks.php                            |   ['tasks'] 
 *  [2] => fancybox-for-wordpress/fancybox.php         |   ['fancybox']
 *  [3] => oik/oik-bob-bing-wide.php                   |   ['oik-bob-bing-wide']
 *  [4] => oik/oik-bwtrace.php                         |   ['oik-bwtrace'] 
 *  etcetera                                           |   ...
 * 
 * Note: If two or more plugins offer the same plugin name then we only return the first plugin with that name.
 * This should cater for copies of the plugins with suffixes. e.g `oik` and `oik v3.1`
 * 
 * @param array $active_plugins, may be false
 * @return array associative array of active plugins - may be empty, but very unlikely                                                  
 */
function bw_get_all_plugin_names( $active_plugins ) {
  $names = array();
  if ( is_array( $active_plugins ) ) {
		if ( count( $active_plugins ) ) {
			foreach ( $active_plugins as $key => $value ) {
				$name = basename( $value, '.php' );
				$dir = dirname( $value );
				if ( ( $name == $dir ) && !isset( $names[ $name ] ) ) {
					$names[$name] = $value;
				}
			}
			foreach ( $active_plugins as $key => $value ) {
				$name = basename( $value, '.php' );
				if ( !isset( $names[ $name ] ) ) {
					$names[ $name ] = $value;
				}
			}
		}	
  }
  return( $names ); 
} 

/**
 * Return an associative version of the sitewide active plugins array
 *
 * The MS active plugins array is structured like this 
 *
 *   $key                                    |  $value
 *   ---------------------------------------- | ----------
 *   [oik-nivo-slider/oik-nivo-slider.php] => | 1335804038
 *   [oik/oik.php]                         => | 1335804114
 *
 * we need to make it the same as the array returned by bw_get_all_plugin_names()  
*/
function bw_ms_get_all_plugin_names( $active_plugins ) {
  $names = array();
  if ( count( $active_plugins ) ) {
    foreach ( $active_plugins as $key => $value ) {
      $name = basename( $key, '.php' );
      $names[$name] = $key;
    }  
  }
  return( $names );
}

/**
 * Produce a message when a dependent plugin is inactive or the wrong version 
 *
 * @param string $plugin - the plugin we're checking 
 * @param string $dependencies - format plug_name:version e.g. oik:1.12.1, where :version is optional
 * @return string $message. The message, including a link to: install, activate or upgrade the dependent plugin
*/
if ( !function_exists( "oik_plugin_inactive" ) ) {
function oik_plugin_inactive( $plugin=null, $dependencies=null, $problem=null ) {
	static $checked = array();
  $plug_name = basename( $plugin, '.php' );
  $dependencies = str_replace( ":", __(" version ", null ), $dependencies );
  list( $depends ) = explode(' ', trim( $dependencies ));
  $text = "<p><b>";
  /* translators: %s: plugin name */
	$text .= sprintf( __( 'Plugin %1$s may not be fully functional.', null ), $plug_name );
	$text .= '</b>';
	$text .= ' ';
	/* translators: %s: plugin dependencies, comma separated */
  $text .= sprintf( __( 'Please install and activate the required minimum version of this plugin: %1$s', null ), $dependencies );
	$text .= "</p>";
  if ( current_filter() == "admin_notices" ) {
		if ( !isset( $checked[ $depends] ) ) { 
			$message = '<div class=" updated fade">';
			$message .= $text;
			$message .= oik_oik_install_link( $depends, $problem );
			$message .= '</div>'; 
		}	else {
			$message = null;
		}
		$checked[ $depends ] = $dependencies . $problem;
		bw_trace2( $checked, "checked" );	
  } else {
    $message = '<tr class="plugin-update-tr">';
    $message .= '<td colspan="3" class="plugin-update colspanchange">';
    $message .= '<div class="update-message">';
    $message .= $text;
    $message .= "</div>";
    $message .= "</td>";
    $message .= "</tr>";
  }
  echo $message; 
}
}



/**
 *

 * Display a message when setup is not fully functional due to the dependencies not being activated or installed
 * Note: We can't use oik APIs here as we don't know if it's activated.
 * If the message is issued due to a version mismatch then there is a chance that one plugin attempts to use
 * functions that are not available in the dependent plugin. How do we manage this?
*/
if ( !function_exists( "oik_plugin_plugin_inactive" ) ) {
function oik_plugin_plugin_inactive( $plugin=null, $dependencies=null, $problem=null ) {

	static $checked = array();
  $plugin_name = basename( $plugin, ".php" );
  $dependencies = str_replace( ":", ' ' . __( "version", null ) . ' ', $dependencies );
  list( $depends ) = explode(' ', trim( $dependencies ));
  $text = "<p><b>";
  /* translators: %s: plugin dependencies, comma separated */
  $text .= sprintf( __( '%1$s may not be fully functional.', null), $plugin_name );
  $text .= "</b> ";
  /* translators: %s: plugin dependencies, comma separated */
  $text .= sprintf( __( 'Please install and activate the required minimum version of this plugin: %1$s', null ), $dependencies );
	$text .= "</p>";
  
  if ( current_filter() == "admin_notices" ) {
	
		if ( !isset( $checked[ $depends] ) ) { 
			$message = '<div class=" updated fade">';
			$message .= $text;
			$depends = strtok( $dependencies, " " );
			$message .= oik_plugin_oik_install_link( $depends, $problem );
			$message .= '</div>'; 
		}	else {
			$message = null;
		}
		$checked[ $depends ] = $dependencies . $problem;
		bw_trace2( $checked, "checked" );	
    
  } else {
    $message = '<tr class="plugin-update-tr">';
    $message .= '<td colspan="3" class="plugin-update colspanchange">';
    $message .= '<div class="update-message">';
    $message .= $text;
    $message .= "</div>";
    $message .= "</td>";
    $message .= "</tr>";
  }
  echo $message; 
}
}

/** 
 * Checks that the version of the plugin is at least the value we specify
 * Notes:
 *  If there is no version function then we assume it's OK
 *  If no version is specified then we assume it's OK
 *  We perform string compares on the version - allowing for 1.0.995a etc
*/ 
function oik_check_version( $depend, $version ) {
  $active = true;
  $version_func = "{$depend}_version";
  if ( is_callable( $version_func )) {
    $active_version = $version_func();
    $active = version_compare( $active_version, $version, "ge" ); 
    bw_trace2( $active_version, $version, true, BW_TRACE_DEBUG );
  }
  return( $active );    
}

/**
 * Return an array of ALL active plugins - for single or multisite 
 *
 * @return associative array
 * 
 */
function bw_get_active_plugins() {
  $active_plugins = get_option( 'active_plugins' );
  bw_trace2( $active_plugins, "active plugins", false );
	$names = array();
	if ( $active_plugins ) {
		$names = bw_get_all_plugin_names( $active_plugins );
	}
  if ( is_multisite() ) {
    $active_plugins = get_site_option( 'active_sitewide_plugins');
    $ms_names = bw_ms_get_all_plugin_names( $active_plugins );
    //bw_trace2( $ms_names, "ms plugin names" );
    $names = array_merge( $names, $ms_names ); 
  }
  //bw_trace2( $names, "active plugin names" );
  //bw_backtrace();
    
  return( $names );
}  

/**
 * Check that the plugins that this plugin is dependent upon are active
 *
 * @param string $plugin - name of the plugin being activated
 * @param string $dependencies - list of plugin dependencies - in whatever order you care
 * @param mixed $callback the callback to invoke when the dependency is not satisfied
 * Notes: 
 * The list of plugins could include oik - which should be loaded UNLESS this file is being
 * loaded by some other mechanism.
 */
function oik_lazy_depends( $plugin, $dependencies, $callback="oik_plugin_inactive" ) {
  bw_backtrace( BW_TRACE_DEBUG );
  $names = bw_get_active_plugins();
  bw_trace2( $names, "active plugin names", true, BW_TRACE_DEBUG );
  
  $depends = explode( ",", $dependencies );
  foreach ( $depends as $dependcolver ) {
    list( $depend, $version ) = explode( ":", $dependcolver. ":" );
    //bw_trace2( $dependcolver, "dependcolver" );
    //bw_trace2( $depend, "depend" );
    //bw_trace2( $version, "version" );
    
    $problem = null;
    $active = bw_array_get( $names, $depend, null );
    if ( $active ) {
      $active = oik_check_version( $depend, $version );
      if ( !$active ) {
        $problem = "version";
      }  
    } else {
      $problem = "missing";
    } 
    
    if ( !$active ) {
      bw_trace2( $depend, "$plugin is dependent upon $depend, which is not active or is the wrong version", true, BW_TRACE_WARNING );
      
      if ( !is_callable( $callback ) )
        $callback = "oik_plugin_inactive" ;
      call_user_func( $callback, $plugin, $dependcolver, $problem  );
      //deactivate_plugins( array( $plugin ) );
    }
  }
}



/** 
 * Produce an install plugin link
 *
 * @param string $plugin - the plugin name e.g.
 * @return string - the HTML
 */
function oik_install_plugin( $plugin ) {
  $path = "update.php?action=install-plugin&plugin=$plugin";
  $url = admin_url( $path );
  $url = wp_nonce_url( $url, "install-plugin_$plugin" ); 
  $link = '<a href="';
  $link .= $url;
  $link .= '">';
  /* translators: %s: plugin name */
	$link .= sprintf( __( 'Install %1$s', null ), $plugin );
	$link .= "</a>";
  return( $link );
}

/**
 * Produce an "activate" plugin link
 *
 * @param string $plugin_file - e.g. oik/oik-header.php
 * We may not be activating the main plugin, so we need the relative path filename of the plugin to activate
 * @return string link to enable activation - which user must choose
 * We probably don't need plugin_status OR paged parameters
 
 
   http://qw/oobit/wp-admin/plugins.php?
     action=activate
     &plugin=oik%2Foik.php
     &plugin_status=all
     &paged=1&s
     &_wpnonce=a53a158be5
*/                              
function oik_activate_plugin( $plugin, $plugin_name) {
    $path = "plugins.php?action=activate&plugin_status=all&paged=1&s&plugin=$plugin";
    $url = admin_url( $path );
    $url = wp_nonce_url( $url, "activate-plugin_$plugin" ); 
    $link = '<a href="';
    $link .= $url;
    $link .= '">';
	/* translators: %s: plugin name */
		$link .= sprintf( __( 'Activate %1$s', null ), $plugin_name );
    $link .= "</a>";
    return( $link );
} 
 
/**
 * Produce an "update" plugin link
 *
 * @param string $plugin - the plugin name
 * @return string - the HTML
 */
function oik_update_plugin( $plugin ) {
  $path = "update.php?action=upgrade-plugin&plugin=$plugin";
  $url = admin_url( $path );
  $url = wp_nonce_url( $url, "upgrade-plugin_$plugin" ); 
  $link = '<a href="';
  $link .= $url;
  $link .= '">';
  /* translators: %s: plugin name */
	$link .= sprintf( __( 'Upgrade %1$s', null ), $plugin );
  $link .= "</a>";
  return( $link );
}

/** 
 * Find out of we think the plugin is installed but not activated or not even installed
 * 
 * @param string $plugin - the plugin file name ( without plugin path info? )
 * @return string - null if it's not installed or plugin to be activated
 * 
 C:\apache\htdocs\wordpress\wp-content\plugins\oik\shortcodes\oik-bob-bing-wide.php(289:0) 2012-05-23T07:52:15+00:00 696 cf=the_content bw_get_plugins(4)  Array
(

    [oik/oik.php] => Array
        (
            [Name] => oik base plugin
            [PluginURI] => http://www.oik-plugins.com/oik
            [Version] => 1.13
            [Description] => Lazy smart shortcodes for displaying often included key-information and other WordPress content
            [Author] => bobbingwide
            [AuthorURI] => http://www.bobbingwide.com
            [TextDomain] => 
            [DomainPath] => 
            [Network] => 
            [Title] => oik base plugin
            [AuthorName] => bobbingwide
        )



    [oik/oik-bwtrace.php] => Array
        (
            [Name] => oik bwtrace
            [PluginURI] => http://www.oik-plugins.com/oik
            [Version] => 1.13
            [Description] => Easy to use trace macros for oik plugins
            [Author] => bobbingwide
            [AuthorURI] => http://www.bobbingwide.com
            [TextDomain] => 
            [DomainPath] => 
            [Network] => 
            [Title] => oik bwtrace
            [AuthorName] => bobbingwide
        )

)

 */
function oik_check_installed_plugin( $plugin ) { 
  $plugin_to_activate = null;
  $needle = "/$plugin.php"; 
  
  $plugins = get_plugins();
  //bw_trace2( $plugins ); 
  if ( count( $plugins )) {
    foreach ( $plugins as $plugin_name => $plugin_details ) {
      if ( strpos( $plugin_name, $needle ) ) {
        $plugin_to_activate['Name'] = $plugin_details['Name'];
        $plugin_to_activate['file'] = $plugin_name;
        break;
      }  
    }
  }
  return( $plugin_to_activate );
}

/** 
 * oik_oik_install_link
  
    http://qw/oobit/wp-admin/update.php?action=install-plugin&plugin=oik&_wpnonce=eb1c632af5
    http://qw/oobit/wp-admin/plugin-install.php?tab=search&s=oik&plugin-search-input=Search+Plugins
 */
function oik_oik_install_link( $plugin, $problem ) { 
  if ( $problem == "missing" ) {
    /* Is it missing or just inactive ? */
    $plugin_to_activate = oik_check_installed_plugin( $plugin );
    if ( $plugin_to_activate ) {
      $link = oik_activate_plugin( $plugin_to_activate['file'], $plugin_to_activate['Name'] );
    } else {
      $link = oik_install_plugin( $plugin );
    }
  } else {
    $link = oik_update_plugin( $plugin );
  }  
  return( $link );
}


} /* end !defined */
