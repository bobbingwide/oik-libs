<?php // (C) Copyright Bobbing Wide 2012-2018
if ( !defined( "OIK_PLUGINS_INCLUDED" ) ) {
	define( "OIK_PLUGINS_INCLUDED", "0.2.1" );

/**
 * Library: oik_plugins
 * Provides: oik_plugins
 * Depends: oik-admin, class-oik-update
 * Deferred dependencies: oik-depends, class-oik-remote
 * Version: see above ?
 * 
 * Implements oik/admin/oik-plugins.inc as a shared library: bobbingwide/oik_plugins
 * Note: hyphens for plugins, underscores for libraries, hyphens for class libraries
 */ 
 
 

/**
 * oik plugins settings page 
 *
 * Processing depends on the button that was pressed. There should only be one!
 * 
 * Selection                     |  Validate? | Perform action         | Display check | Display add  | Display edit | Display select list
 * ----------------------------- | --------   |-------------------     |-------------  | ------------ | ------------ | -------------------
 * check_plugin                  |  No        | n/a                    | Yes           | -            | -            | -
 * delete_plugin                 |  No        | delete selected plugin | -             | -            | -            | Yes
 * edit_plugin                   |  No        | n/a                    | -             | -            | Yes          | Yes
 * _oik_plugins_edit_settings    |  Yes       | update selected plugin | -             | -            | Yes          | Yes
 * _oik_plugins_add_plugin			 |	No				| n/a										 | -						 | Yes          | - 					 | ?
 * _oik_plugins_add_settings		 |	Yes				| add selected plugin		 | -						 | -						| - 					 | ?
 * 
 */
function oik_lazy_plugins_server_settings() {
	//bw_trace2();
	//bw_backtrace();
  BW_::oik_menu_header( __( "plugin server settings", null ), "w100pc" );
  $validated = false;
  
  $check_plugin = bw_array_get( $_REQUEST, "check_plugin", null );
  $delete_plugin = bw_array_get( $_REQUEST, "delete_plugin", null );
  $edit_plugin = bw_array_get( $_REQUEST, "edit_plugin", null );
  
  /** These plugins override the ones from the list... but why do we need to do it? 
   * Do we have to receive the others in the $_REQUEST **?**
   *
  */
  $oik_plugins_edit_settings = bw_array_get( $_REQUEST, "_oik_plugins_edit_settings", null );
  $oik_plugins_add_settings = bw_array_get( $_REQUEST, "_oik_plugins_add_settings", null );
  $oik_plugins_add_plugin = bw_array_get( $_REQUEST, "_oik_plugins_add_plugin", null );
  if ( $oik_plugins_add_plugin || $oik_plugins_add_settings ) {
    $check_plugin = null;
    $delete_plugin = null;
    $edit_plugin = null; 
  }  
  
  if ( $check_plugin ) {
    BW_::oik_box( NULL, NULL, _x( "Check", "examine", null ) , "oik_plugins_check" );
  } 
  
  if ( $delete_plugin ) { 
    _oik_plugins_delete_settings( $delete_plugin );
  }  

  if ( $edit_plugin ) {
    global $bw_plugin;
    $bw_plugins = get_option( "bw_plugins" );
    $bw_plugin = bw_array_get( $bw_plugins, $edit_plugin, null );
    if ( $bw_plugin == null ) {
      $bw_plugin['server'] = null;
      $bw_plugin['apikey'] = null;
    }
    $bw_plugin['plugin'] = $edit_plugin; 
    bw_trace2( $bw_plugin );
  }
  if ( $oik_plugins_edit_settings ) {  
    $validated = _oik_plugins_settings_validate( false );
  }  
  
  if ( $oik_plugins_add_settings ) {
    $validated = _oik_plugins_settings_validate( true );
  }
  
  if ( $oik_plugins_add_plugin || ( $oik_plugins_add_settings && !$validated )  ) {
    BW_::oik_box( NULL, NULL, __( "Add new", null ), "oik_plugins_add_settings" );
  }
  
  if ( $edit_plugin || $oik_plugins_edit_settings || $validated ) {
    BW_::oik_box( null, null, __( "Edit plugin", null ), "oik_plugins_edit_settings" );
  }
  BW_::oik_box( NULL, NULL, __( "Settings", null ), "oik_plugins_settings" );
  oik_menu_footer();
  bw_flush();
}

/** 
 * Display current settings for a plugin
 *
 * Note: The Delete function doesn't delete the plugin, just the profile information that overrides the values set by oik_register_plugin_server()
 *
 * @param string $theme - theme slug
 * @param string $version - current theme version
 * @param string $server - theme server
 * @param string $apikey - API key for premium theme
 * @param bool $programmatically_registered - true if registered by the theme
 */
function _oik_plugins_settings_row( $plugin, $version, $server, $apikey, $programmatically_registered ) {
  $row = array();
  $row[] = $plugin;
  $row[] = $version . "&nbsp;"; 
  $row[] = $server . "&nbsp;"; //itext( "server[$plugin]", 100, $server ); //esc_html( stripslashes( $server ) )); //iarea( $plugin, 100, $server, 10 );
  $row[] = $apikey . "&nbsp;"; //itext( "apikey[$plugin]", 26, $apikey );
  $links = null;
	
	if ( $programmatically_registered ) {
		$links .= retlink( null, admin_url("admin.php?page=oik_plugins&amp;delete_plugin=$plugin"), __( "Reset", null ), __( "Reset plugin's profile entry", null ) ); 
	} else {
		$links .= retlink( null, admin_url("admin.php?page=oik_plugins&amp;delete_plugin=$plugin"), __( "Delete", null ), __( "Delete plugin's profile entry", null ) ); 
	}
  $links .= "&nbsp;";
  $links .= retlink( null, admin_url("admin.php?page=oik_plugins&amp;edit_plugin=$plugin"), __( "Edit", null ) ); 
  $links .= "&nbsp;"; 
  $links .= retlink( null, admin_url("admin.php?page=oik_plugins&amp;check_plugin=$plugin&amp;check_version=$version"), __( "Check", null ) );
  $links .= "&nbsp;";
  $row[] = $links;
  bw_tablerow( $row );
}


/**
 * Load registered plugins
 * 
 * We don't override the values that the user has defined with the hardcoded values.
 * We only apply the hardcoded values when the profile entry does not exist.
 * 
 * @return array of registered plugins and their overrides
 */
function _oik_plugins_load_registered_plugins() {
  $bw_plugins = get_option( "bw_plugins" );
  global $bw_registered_plugins;
  //bw_trace2( $bw_registered_plugins );
  
  if ( is_array( $bw_registered_plugins) && count( $bw_registered_plugins )) {
    foreach ( $bw_registered_plugins as $plugin => $plugin_data ) {
      $plugin = oik_update::bw_last_path( $plugin_data['file'] );
      //bw_trace2( $plugin );
      //bw_trace2( $plugin_data );
      if ( !isset( $bw_plugins[$plugin] ) ) {
        $bw_plugins[$plugin] = $plugin_data;
      }  
			$bw_plugins[$plugin]['programmatically_registered'] = true;
    }
  }
  //bw_trace2( $bw_plugins );
  return( $bw_plugins );
}

/**
 * Display the oik plugins profile values and other information
 * 
 * This should also list the plugins that have registered themselves using oik_register_plugin_server()
 */
function _oik_plugins_settings_table() {
  //$bw_plugins = get_option( "bw_plugins" );
  $bw_plugins = _oik_plugins_load_registered_plugins();
  
  if ( is_array( $bw_plugins) && count( $bw_plugins )) {
    foreach ( $bw_plugins as $plugin => $plugin_data ) {
      //$plugin = bw_array_get( $plugin, "plugin", null );
      $version = bw_get_plugin_version( $plugin );
      $server = bw_array_get( $plugin_data, "server", "&nbsp;" );
      $apikey = bw_array_get( $plugin_data, "apikey", null );
			$programmatically_registered = bw_array_get( $plugin_data, "programmatically_registered", false );
      _oik_plugins_settings_row( $plugin, $version, $server, $apikey, $programmatically_registered );
    }
  }  
}

/**
 * Add the settings for the plugin
 * 
 * @param array $plugin
 * @return bool true
 */
function _oik_plugins_add_settings( $plugin ) {
  $field = bw_array_get( $plugin, "plugin", null );
  unset( $plugin['plugin'] );
  bw_update_option( $field, $plugin, "bw_plugins" );
  $plugin['plugin'] = "";
  $ok = true;
  return( $ok ); 
}

/** 
 * Update the settings for a plugin
 * 
 * @param array $plugin
 */
function _oik_plugins_update_settings( $plugin ) {
  $field = bw_array_get( $plugin, "plugin", null );
  if ( $plugin ) { 
    unset( $plugin['plugin'] );
    bw_update_option( $field, $plugin, "bw_plugins" );
  } else {
    //gobang();
  }  
}

/**
 * Delete the settings for a plugin
 *
 * @param array $plugin
 */
function _oik_plugins_delete_settings( $plugin ) {
  bw_delete_option( $plugin, "bw_plugins" );
}  

/**
 * Validate the plugin name: plugin must not be blank
 * 
 * @param string $plugin - plugin name
 * @return bool true if the plugin name is valid
 */
function oik_plugins_validate_plugin( $plugin ) {
  $valid = isset( $plugin );
  if ( $valid ) { 
    $plugin = trim( $plugin );
    $valid = strlen( $plugin ) > 0;
  } 
  if ( !$valid ) { 
    BW_::p( __( "settings must not be blank", null ) );   
  }  
  return $valid;
}
    
/**
 * Validate the plugin's settings and add/update if required
 * 
 * @param bool $add_plugin 
 * @return bool - validation result
 */
function _oik_plugins_settings_validate( $add_plugin=true ) {
	global $bw_plugin;
	$bw_plugin['plugin'] = trim( bw_array_get( $_REQUEST, "plugin", null ) );
	$bw_plugin['server'] = trim( bw_array_get( $_REQUEST, "server", null ) );
	$bw_plugin['apikey'] = trim( bw_array_get( $_REQUEST, "apikey", null ) );
  
	$ok = oik_plugins_validate_plugin( $bw_plugin['plugin'] );
  
	// validate the fields and add the settings IF it's OK to add
	// $add_plugin = bw_array_get( $_REQUEST, "_oik_plugins_add_settings", false );
	if ( $ok ) {
		if ( $add_plugin ) {
			$ok = _oik_plugins_add_settings( $bw_plugin );  
		} else {
			$ok = _oik_plugins_update_settings( $bw_plugin );
		}
	}  
	return( $ok );
}

/**
 * Display the plugin settings table form
 */
function oik_plugins_settings() {
  $default_plugin_server = oik_get_plugins_server();
  $link = retlink( null, $default_plugin_server, $default_plugin_server , __( "default oik plugins server", null ) );
  BW_::p( sprintf( __( 'The default oik plugins server is currently set to: %1$s', null ),  $link ) );
  bw_form();
  stag( "table", "widefat " );
  stag( "thead");
  bw_tablerow( array( __( "plugin", null )
										, __( "version", null )
										, __( "server", null ) 
										, __( "apikey", null )
										, __( "actions", null ) ) );
  etag( "thead");
  _oik_plugins_settings_table();
  etag( "table" );
  BW_::p( isubmit( "_oik_plugins_add_plugin", __( "Add plugin", null ), null, "button-primary" ) );
  etag( "form" );
}

/**
 * Display the add settings form
 */ 
function oik_plugins_add_settings( ) {
  global $bw_plugin;
  bw_form();
  stag( "table", "widefat" );
  BW_::bw_textfield( "plugin", 20, __( "plugin", null ), $bw_plugin['plugin'] );
  BW_::bw_textfield( "server", 100, __( "server", null ), stripslashes( $bw_plugin['server'] ) );
  BW_::bw_textfield( "apikey", 26, __( "apikey", null ), $bw_plugin["apikey"] );
  etag( "table" );
  BW_::p( isubmit( "_oik_plugins_add_settings", __( "Add new plugin", null ), null, "button-primary" ) );
  etag( "form" );
}

/**
 * Display the edit settings form
 */
function oik_plugins_edit_settings( ) {
  global $bw_plugin;
  bw_form();
  stag( "table", "wide-fat" );
  bw_tablerow( array( __( "plugin", null ), $bw_plugin['plugin'] . ihidden( 'plugin', $bw_plugin['plugin']) ) );
  BW_::bw_textfield( "server", 100, __( "server", null ), stripslashes( $bw_plugin['server'] ) );
  BW_::bw_textfield( "apikey", 26, __( "apikey?", null ), $bw_plugin["apikey"] );
  etag( "table" );
  BW_::p( isubmit( "_oik_plugins_edit_settings", __( "Change plugin", null ), null, "button-primary" ));
  etag( "form" );
}

/**
 * Checks a plugin for updates
 *
 * The expected response from oik_check_for_update() is an array.
 * If it contains 'new_version' then there's an update.
 *  
 * `
    [slug] => oik-fum
    [new_version] => 1.2.1
    [url] => https://oik-plugins.com/oik_plugin/oik-fum
		[plugin] => "oik-fum/oik-fum.php"
    [package] => "https://qw/oikcom/plugins/download?plugin=oik-fum-flexible-update-manager&version=1.2.1&id=29228&action=update"
	 `
 */
function oik_plugins_check() {
  $check_plugin = bw_array_get( $_REQUEST, "check_plugin", null );
  $check_version = bw_array_get( $_REQUEST, "check_version", null );
  if ( $check_plugin && $check_version ) {
    // Check the plugin from the remote server ? What does this mean? Validate the apikey perhaps?
    //$response = oik_plugins
    oik_require_lib( "class-oik-remote" );
    $response = oik_remote::oik_check_for_update( $check_plugin, $check_version, true );
    bw_trace2( $response );
    if ( is_wp_error( $response ) ) {
      BW_::p( sprintf( __( 'Error checking the plugin: %1$s', null ), $check_plugin ) );
      $error_message = $response->get_error_message();
      BW_::p( $error_message );
    } else {
      $new_version = bw_array_get( $response, "new_version", null );
      if ( $new_version ) { 
        BW_::p( __( "A new version of the plugin is available", null ) );
        BW_::p( sprintf( __( 'Plugin: %1$s', null ), $check_plugin ) );
        BW_::p( sprintf( __( 'Current version: %1$s', null ), $check_version ) );
        BW_::p( sprintf( __( 'New version: %1$s', null ), $new_version ) );
        oik_plugin_record_new_version( $check_plugin, $check_version, $response ); 
        oik_plugin_new_version( $response );
      } else {
        BW_::p( __( "Plugin is up to date.", null ) );
        BW_::p( sprintf( __( 'Plugin: %1$s', null ), $check_plugin ) );
        BW_::p( sprintf( __( 'Current version: %1$s', null ), $check_version ) );
      }  
    }
  }
}

/**
 * Let WordPress know that there is a new version of the plugin
 *
 * Update the "_site_transient_update_plugins" option with the latest information about this plugin
 *
 * @param string $plugin - the plugin slug 
 * @param string $check_version - the version we checked against (it's used for theme updates! )
 * @param object $response - for this plugin
 * 
 
 * Example: Parameters contain...
 
    [0] => oik-fields
    [1] => 1.19.1107
    [2] => stdClass Object
        (
            [slug] => oik-fields
            [plugin] => oik-fields/oik-fields.php
            [new_version] => 1.34
            [url] => http://oik-plugins.co.uk/oik_plugin/oik-fields
            [package] => http://oik-plugins.co.uk/plugins/download?plugin=oik-fields-custom-post-type-field-apis&version=1.34&id=3465&action=update
        )



 *
 * Site transient contains something like...  note the fields that WordPress also provides ( [id] and [upgrade_notice] )
 * 
 (
    [last_checked] => 1397238677
    [response] => Array
        (

            [oik-css/oik-css.php] => stdClass Object
                (
                    [id] => 42249
                    [slug] => oik-css
                    [plugin] => oik-css/oik-css.php
                    [new_version] => 0.5
                    [upgrade_notice] => Now dependent upon oik v2.1. Tested with WordPress 3.9-beta3
                    [url] => https://wordpress.org/plugins/oik-css/
                    [package] => https://downloads.wordpress.org/plugin/oik-css.0.5.zip
                )
         )
 * 
 * We add/update the [oik-fields/oik-fields.php] entry

 
 */ 
function oik_plugin_record_new_version( $plugin, $check_version, $response ) { 
	bw_trace2( $response );
	$option = get_site_option( "_site_transient_update_plugins" );
	bw_trace2( $option, "option", false );
  
	$new_version = bw_array_get( $response, "new_version", null );
	$plugin_name = bw_array_get( $response, "plugin", "$plugin/$plugin.php" );
	//$option->checked[$plugin] = $check_version;
	$option->response[$plugin_name] = $response;
	$option->last_checked = time();
  
	bw_trace2( $option, "option", false );
	update_site_option( "_site_transient_update_plugins", $option );
}  

/** 
 * Produce an Update plugin link
 *
 * @param object $response
 *
 * `
    [slug] => oik
    [new_version] => 1.17.1030.1702
    [url] => http://oik-plugins.co.uk/oik_plugin/oik
    [package] => http://oik-plugins.co.uk/plugins/download?plugin=oik-often-included-key-information-kit&version=1.17.1030.1702&id=419&action=update
    
  http://oik-plugins.co.uk/wp-admin/plugin-install.php?tab=plugin-information&plugin=oik&section=changelog&TB_iframe=true&width=640&height=662
  http://oik-plugins.co.uk/wp-admin/update.php?action=upgrade-plugin&plugin=oik%2Foik.php&_wpnonce=7efefad99d
 * `
 */
function oik_plugin_new_version( $response ) {
  $slug = bw_array_get( $response, "slug", null );
  $plugin_name = bw_get_plugin_name( $slug );
  BW_::p( oik_update_plugin( $plugin_name ) );

}

if ( !function_exists( "bw_update_option" ) ) {
/** Set the value of an option field in the options group
 *
 * @param string $field the option field to be set
 * @param mixed $value the value of the option
 * @param string $options - the name of the option field
 * @return mixed $value
 *
 * Parms are basically the same as for update_option
 */
function bw_update_option( $field, $value=NULL, $options="bw_options" ) {
  $bw_options = get_option( $options );
  $bw_options[ $field ] = $value;
  bw_trace2( $bw_options );
  update_option( $options, $bw_options );
  return( $value );
}
}

/** Remove an option field from a set
 *
 * @param string $field the option field to be removed
 * @param string $options - the name of the options set
 * @return mixed $value - current values for the options
 *
 */
if ( !function_exists( "bw_delete_option" ) ) {
function bw_delete_option( $field, $options="bw_options" ) {
  $bw_options = get_option( $options );
  unset( $bw_options[ $field ] );
  // bw_trace2( $bw_options );
  update_option( $options, $bw_options );
  return( $options );
}
}

/**
 * Obtain the plugin slugs
 *
 * The plugins slugs are the array keys from get_plugins() saved in 
 * a transient named 'plugin_slugs' for 24 hours.
 *
 * @return array of plugin slugs 
 */
function bw_get_plugin_slugs() {
  $plugin_slugs = get_transient( 'plugin_slugs' );
  if ( false === $plugin_slugs ) {
	
		require_once( ABSPATH . "wp-admin/includes/plugin.php" );
    $plugins = get_plugins();
    bw_trace2( $plugins, "plugins", false, BW_TRACE_DEBUG );
    $plugin_slugs = array_keys( $plugins );
    set_transient( 'plugin_slugs', $plugin_slugs, 86400 );
  }
  bw_trace2( $plugin_slugs, "plugin_slugs", false, BW_TRACE_DEBUG );
  return( $plugin_slugs );
}

/**
 * Return the plugin version
 * 
 * Note: get_plugin_data() may not find a plugin that's been renamed ( plugin folder or file )
 * and it doesn't check that the plugin is present, so we do here.
 * 
 * @param string $plugin_name expected form "plugin/plugin.php"
 * @return string|null plugin version or null
 */
function _bw_get_plugin_version( $plugin_name ) {
  $file = WP_PLUGIN_DIR . '/'. $plugin_name;
	$version = null;
	if ( file_exists( $file ) ) { 
		require_once( ABSPATH . "wp-admin/includes/plugin.php" );
		$plugin_data = get_plugin_data( $file, false, false );
		// We assume get_plugins() is loaded since we're doing admin stuff! 
		//$plugin_folder = get_plugins( $plugin_name );
		//bw_trace2( $plugin_folder, "plugin_folder" );
		//$plugin_data = bw_array_get( $plugin_folder, $plugin_name, null ); 
		$version = bw_array_get( $plugin_data, 'Version', null );
	}
  return( $version );
}

/**
 * Returns the plugin names
 * 
 * Returns a cached array of $plugins. 
 * We expect there to be at least one.
 * 
 * @return array $plugins 
 */
function _bw_get_plugins() {
  static $plugins = null;
  if ( !$plugins ) {
    $plugin_slugs = bw_get_plugin_slugs();
    //oik_require( "admin/oik-depends.inc" );
		oik_require_lib( "oik-depends" );
    $plugins = bw_get_all_plugin_names( $plugin_slugs);
  }
	bw_trace2( $plugins, "plugins", false, BW_TRACE_VERBOSE );
  return( $plugins );
} 

/**
 * Get the full plugin name given the slug
 * 
 * Note: This may return the wrong name if there are two plugins with the same plugin slug! 
 * Which can happen if someone manually renames a plugin folder in order to apply a new version.
 *
 * @return string $plugin_name
 */
function bw_get_plugin_name( $plugin="oik" ) {
  $plugins = _bw_get_plugins();
  $plugin_name = bw_array_get( $plugins, $plugin, null );
  return( $plugin_name ); 
}

/**
 * Returns current plugin version.
 * 
 * @return string Plugin version
 */
function bw_get_plugin_version( $plugin="oik" ) {
  $plugin_name = bw_get_plugin_name( $plugin );
  if ( $plugin_name ) {
    $version = _bw_get_plugin_version( $plugin_name );
  } else {
    $version = null;
		bw_trace2( $plugin, "Can't get plugin_name" );
  }
  return( $version );      
}

  
/** 
 * Return the URL for the Premium (Pro) or Freemium version
 * 
 * If BW_OIK_PLUGINS_SERVER is defined we'll use that.
 * Else, we'll use the value of OIK_PLUGINS_COM
 * which we'll define if it's not already defined
 * 
 * @return string URL for an oik-plugins server
 */
if ( !function_exists( "oik_get_plugins_server" ) ) { 
	function oik_get_plugins_server() {
		if ( defined( 'BW_OIK_PLUGINS_SERVER' )) {
			$url = BW_OIK_PLUGINS_SERVER;
		} else {
		if ( !defined( "OIK_PLUGINS_COM" ) ) {
			define( "OIK_PLUGINS_COM", "https://www.oik-plugins.com" );
		}
			$url = OIK_PLUGINS_COM;
		}
		return( $url );
	}
}

} else {
	//echo __FILE__;
}