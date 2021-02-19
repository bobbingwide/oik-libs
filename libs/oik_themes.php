<?php // (C) Copyright Bobbing Wide 2013-2021
if ( !defined( "OIK_THEMES_INCLUDED" ) ) {
	define( "OIK_THEMES_INCLUDED", "0.2.1" );

/**
 * Library: oik_themes
 * Provides: oik_themes
 * Depends: oik-admin, class-oik-update
 * Deferred dependencies: oik-depends, class-oik-remote
 * 
 * Implements oik/admin/oik-themes.inc as a shared library: bobbingwide/oik_themes
 * Note: hyphens for plugins, underscores for libraries
 */ 
 
/**
 * oik themes settings page 
 *
 * Processing depends on the button that was pressed. There should only be one!
 * 
 * Selection                     | Validate? | Perform action      | Display check | Display add | Display edit | Display select list
 * ----------------------------- | -------- |  ------------------- | ------------- | ----------- | ------------ | -------------------
 * check_theme                   | No       | n/a                  |   Yes         |  -          |  -           | -
 * delete_theme                  | No       | delete selected theme |  -           |  -          |  -           | Yes
 * edit_theme                    | No       | n/a                   |  -           |  -          |  Yes         | Yes
 *                                                                              
 * _oik_themes_edit_settings     | Yes      | update selected theme | -            | -           | Yes          | Yes
 * _oik_themes_add_theme         |
 * _oik_themes_add_settings      |
 * 
*/
function oik_lazy_themes_server_settings() {
  BW_::oik_menu_header( __( "theme server settings", null ), "w100pc" );
  $validated = false;
  
  $check_theme = bw_array_get( $_REQUEST, "check_theme", null );
  $delete_theme = bw_array_get( $_REQUEST, "delete_theme", null );
  $edit_theme = bw_array_get( $_REQUEST, "edit_theme", null );
  
  /** These themes override the ones from the list... but why do we need to do it? 
   * Do we have to receive the others in the $_REQUEST **?**
   *
  */
  $oik_themes_edit_settings = bw_array_get( $_REQUEST, "_oik_themes_edit_settings", null );
  $oik_themes_add_settings = bw_array_get( $_REQUEST, "_oik_themes_add_settings", null );
  $oik_themes_add_theme = bw_array_get( $_REQUEST, "_oik_themes_add_theme", null );
  if ( $oik_themes_add_theme || $oik_themes_add_settings ) {
    $check_theme = null;
    $delete_theme = null;
    $edit_theme = null; 
  }  
  
  if ( $check_theme ) {
    BW_::oik_box( NULL, NULL, _x( "Check", "examine", null ) , "oik_themes_check" );
  } 
  
  if ( $delete_theme ) { 
    _oik_themes_delete_settings( $delete_theme );
  }  

  if ( $edit_theme ) {
    global $bw_theme;
    $bw_themes = get_option( "bw_themes" );
    $bw_theme = bw_array_get( $bw_themes, $edit_theme, null );
    if  ( null == $bw_theme ) {
      $bw_theme['server'] = null;
      $bw_theme['apikey'] = null;
    }
    $bw_theme['theme'] = $edit_theme; 
    bw_trace2( $bw_theme );
  }
  if ( $oik_themes_edit_settings ) {  
    $validated = _oik_themes_settings_validate( false );
  }  
  
  if ( $oik_themes_add_settings ) {
    $validated = _oik_themes_settings_validate( true );
  }
  
  if ( $oik_themes_add_theme || ( $oik_themes_add_settings && !$validated )  ) {
    BW_::oik_box( NULL, NULL, __( "Add new", null ), "oik_themes_add_settings" );
  }
  
  if ( $edit_theme || $oik_themes_edit_settings || $validated ) {
    BW_::oik_box( null, null, __( "Edit theme", null ), "oik_themes_edit_settings" );
  }
  BW_::oik_box( NULL, NULL, __( "Settings", null ), "oik_themes_settings" );
  oik_menu_footer();
  bw_flush();
}

/** 
 * Display current settings for a theme
 *
 * Note: Delete may not appear to work as the entry is created automatically by the theme when it registers itself.
 * The Delete action will delete the theme's profile entry.
 *
 * @param string $theme - theme slug
 * @param string $version - current theme version
 * @param string $server - theme server
 * @param string $apikey - API key for premium theme
 * @param bool $programmatically_registered - true if registered by the theme
 */
function _oik_themes_settings_row( $theme, $version, $server, $apikey, $programmatically_registered ) {
	$row = array();
	$row[] = $theme;
	$row[] = $version . "&nbsp;"; 
	$row[] = $server . "&nbsp;"; //itext( "server[$theme]", 100, $server ); //esc_html( stripslashes( $server ) )); //iarea( $theme, 100, $server, 10 );
	$row[] = $apikey . "&nbsp;"; //itext( "apikey[$theme]", 26, $apikey );
	$links = null;
	if ( $programmatically_registered ) {
		$links .= retlink( null, admin_url("admin.php?page=oik_themes&amp;delete_theme=$theme"), __( "Reset", null), __("Reset theme's profile entry", null ) ); 
	} else {
		$links .= retlink( null, admin_url("admin.php?page=oik_themes&amp;delete_theme=$theme"), __( "Delete", null), __( "Delete theme's profile entry", null ) ); 
	}
	$links .= "&nbsp;";
	$links .= retlink( null, admin_url("admin.php?page=oik_themes&amp;edit_theme=$theme"), __( "Edit", null ) ); 
	$links .= "&nbsp;"; 
	$links .= retlink( null, admin_url("admin.php?page=oik_themes&amp;check_theme=$theme&amp;check_version=$version"), __( "Check", null ) );
	$links .= "&nbsp;";
	$row[] = $links;
	bw_tablerow( $row );
}

/**
 * Load registered themes
 * 
 * We don't override the values that the user has defined with the hardcoded values
 * Only apply the hardcoded values when the profile entry does not exist.
 * 
 * @return array of registered themes and their overrides
 */
function _oik_themes_load_registered_themes() {
  $bw_themes = get_option( "bw_themes" );
  global $bw_registered_themes;
  if ( is_array( $bw_registered_themes) && count( $bw_registered_themes )) {
    foreach ( $bw_registered_themes as $theme => $theme_data ) {
      $theme = oik_update::bw_last_path( $theme_data['file'] );
      bw_trace2( $theme );
      if ( !isset( $bw_themes[$theme] ) ) {
        $bw_themes[$theme] = $theme_data;
      } 
			$bw_themes[$theme]['programmatically_registered'] = true;
    }
  }
  return( $bw_themes );
}

/**
 * This should also list the themes that have registered themselves using oik_register_theme_server()
 */
function _oik_themes_settings_table() {
  $bw_themes = _oik_themes_load_registered_themes();
  if ( is_array( $bw_themes) && count( $bw_themes )) {
    foreach ( $bw_themes as $theme => $theme_data ) {
      $theme_object = bw_get_theme_name( $theme );
      $version = bw_get_theme_version( $theme, $theme_object );
      $server = bw_get_theme_server( $theme, $theme_object, $theme_data );
      $apikey = bw_array_get( $theme_data, "apikey", null );
			$programmatically_registered = bw_array_get( $theme_data, "programmatically_registered", false );
      _oik_themes_settings_row( $theme, $version, $server, $apikey, $programmatically_registered );
    }
  }  
}

/**
 * Add the settings for the theme
 * 
 * @param string $theme theme slug
 
 */
function _oik_themes_add_settings( $theme ) {
  $field = bw_array_get( $theme, "theme", null );
  unset( $theme['theme'] );
  bw_update_option( $field, $theme, "bw_themes" );
  $theme['theme'] = "";
  $ok = true;
  return( $ok ); 
}

function _oik_themes_update_settings( $theme ) {
  $field = bw_array_get( $theme, "theme", null );
  if ( $theme ) { 
    unset( $theme['theme'] );
    bw_update_option( $field, $theme, "bw_themes" );
  } else {
    //gobang();
  }  
}

function _oik_themes_delete_settings( $theme ) {
  bw_delete_option( $theme, "bw_themes" );
}  


/**
 * theme must not be blank
 */
function oik_themes_validate_theme( $theme ) {
  $valid = isset( $theme );
  if ( $valid ) { 
    $theme = trim( $theme );
    $valid = strlen( $theme ) > 0;
  } 
  if ( !$valid ) { 
    BW_::p( __( "settings must not be blank", null ) );   
  }  
  return $valid;
}
    
/**
 * Validates the settings for the theme
 *
 * @param bool $add_theme true for add, false for update
 * @return bool true when valid
 */
function _oik_themes_settings_validate( $add_theme=true ) {
  global $bw_theme;
  $bw_theme['theme'] = sanitize_key( trim( bw_array_get( $_REQUEST, "theme", null ) ) );
  $bw_theme['server'] = esc_url_raw( trim( bw_array_get( $_REQUEST, "server", null ) ) );
  $bw_theme['apikey'] = sanitize_key( trim( bw_array_get( $_REQUEST, "apikey", null ) ) );
  
  $ok = oik_themes_validate_theme( $bw_theme['theme'] );
  
  // validate the fields and add the settings IF it's OK to add
  // $add_theme = bw_array_get( $_REQUEST, "_oik_themes_add_settings", false );
  if ( $ok ) {
    if ( $add_theme ) {
      $ok = _oik_themes_add_settings( $bw_theme );  
    } else {
      $ok = _oik_themes_update_settings( $bw_theme );
    }
  }  
  return( $ok );
}

/**
 * Displays the registered theme's settings 
 */
function oik_themes_settings() {
  $default_theme_server = oik_update::oik_get_themes_server();
  $link = retlink( null, $default_theme_server, $default_theme_server , __( "default oik themes server", null ) );
  BW_::p( sprintf( __( 'The default oik themes server is currently set to: %1$s', null ), $link ) );
  bw_form();
  stag( "table", "widefat " );
  stag( "thead");
  bw_tablerow( array( __( "theme", null )
										, __( "version", null )
										, __( "server", null ) 
										, __( "apikey", null )
										, __( "actions", null ) ) );
  etag( "thead");
  _oik_themes_settings_table();
  etag( "table" );
  BW_::p( isubmit( "_oik_themes_add_theme", __( "Add theme", null ), null, "button-primary" ) );
  etag( "form" );
}

/**
 * Displays the add theme box 
 */ 
function oik_themes_add_settings( ) {
  global $bw_theme;
  bw_form();
  stag( "table", "widefat" );
  BW_::bw_textfield( "theme", 20, __( "theme", null ), $bw_theme['theme'] );
  BW_::bw_textfield( "server", 100, __( "server", null ), stripslashes( $bw_theme['server'] ) );
  BW_::bw_textfield( "apikey", 26, __( "apikey", null ) , $bw_theme["apikey"] );
  etag( "table" );
  BW_::p( isubmit( "_oik_themes_add_settings", __( "Add new theme", null ), null, "button-primary" ) );
  etag( "form" );
}

/**
 * Displays the edit theme box
 */
function oik_themes_edit_settings( ) {
  global $bw_theme;
  bw_form();
  stag( "table", "wide-fat" );
  bw_tablerow( array( __( "theme", null ), $bw_theme['theme'] . ihidden( 'theme', $bw_theme['theme']) ) );
  BW_::bw_textfield( "server", 100, __( "server", null ), stripslashes( $bw_theme['server'] ) );
  BW_::bw_textfield( "apikey", 26, __( "apikey?", null ), $bw_theme["apikey"] );
  etag( "table" );
  BW_::p( isubmit( "_oik_themes_edit_settings", __( "Change theme", null ), null, "button-primary" ));
  etag( "form" );
}

/**
 * Check for an updated theme
 *
 */
function oik_themes_check() {
  $check_theme = bw_array_get( $_REQUEST, "check_theme", null );
  $check_version = bw_array_get( $_REQUEST, "check_version", null );
  if ( $check_theme && $check_version ) {
    // Check the theme from the remote server ? What does this mean? Validate the apikey perhaps?
    //$response = oik_themes
    oik_require_lib( "class-oik-remote" );
    $response = oik_remote::oik_check_for_theme_update( $check_theme, $check_version, true );
    bw_trace2( $response, "response-octfu" );
    if ( is_wp_error( $response ) ) {
			BW_::p( sprintf( __( 'Error checking the theme: %1$s', null ), $check_theme ) );
			$error_message = $response->get_error_message();
			BW_::p( $error_message );
    } else {
      $new_version = bw_array_get( $response, "new_version", null );
      if ( $new_version ) { 
				BW_::p( __( "A new version of the theme is available", null ) );
				BW_::p( sprintf( __( 'Theme: %1$s', null ), $check_theme ) );
				BW_::p( sprintf( __( 'Current version: %1$s', null ), $check_version ) );
				BW_::p( sprintf( __( 'New version: %1$s', null ), $new_version ) );
        oik_theme_record_new_version( $check_theme, $check_version, $response ); 
        oik_theme_new_version( $response );
      } else {
        BW_::p( __( "Theme is up to date.", null ) );
        BW_::p( sprintf( __( 'Theme: %1$s', null ), $check_theme) );
        BW_::p( sprintf( __( 'Current version: %1$s', null ), $check_version ) );
      }  
    }
  }
}

/**
 * Let WordPress know that there is a new version of the theme
 * 
 * Response contains...
 
    [new_version] => 1.1
    [url] => http://qw/wordpress/oik-themes/rf0311/
    [package] => http://qw/wordpress/themes/download?theme=rf0311&version=1.1&id=10106&action=update

 *
 * Site transient update themes contains something like
 * 

    [last_checked] => 1397232118
    [checked] => Array
        (
            [rf0311] => 1.0
            [twentythirteen] => 1.0
        )
    [response] => Array
        (
            [twentythirteen] => Array
                (
                    [theme] => twentythirteen
                    [new_version] => 1.1
                    [url] => https://wordpress.org/themes/twentythirteen
                    [package] => https://wordpress.org/themes/download/twentythirteen.1.1.zip
                )
        )
    [translations] => Array
        (
        )
 *
 * Set/update the [checked] version of the theme
 * Set/update the [response] for the checked theme
 *        
 
 */ 
function oik_theme_record_new_version( $theme, $check_version, $response ) { 
  //bw_trace2( $response );
  $option = get_site_option( "_site_transient_update_themes" );
  //bw_trace2( $option, "option" );
  $new_version = bw_array_get( $response, "new_version", null );
  $option->checked[$theme] = $check_version;
  $option->response[$theme] = $response;
  //bw_trace2( $option, "option" );
  update_site_option( "_site_transient_update_themes", $option );
}  

/** 
 * Enable upgrade to the new version of the theme 
 *
 */
function oik_theme_new_version( $response ) {
  $theme = bw_array_get( $response, "theme", null );
  //$theme_name = bw_get_theme_name( $slug );
  BW_::p( oik_update_theme( $theme ) );
}

/** 
 * Creates the Upgrade theme link
 * 
 * @param string $theme
 * @return string the Upgrade theme link
 */
function oik_update_theme( $theme ) {
	$path = "update.php?action=upgrade-theme&theme=$theme";
	$url = admin_url( $path );
	$url = wp_nonce_url( $url, "upgrade-theme_$theme" ); 
	$link = '<a href="';
	$link .= $url;
	$link .= '">';
	$link .= sprintf( __( 'Upgrade %1$s', null ), $theme );
	$link .= "</a>";
	return $link ;
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

function bw_get_theme_slugs() {
  $theme_slugs = get_transient( 'theme_slugs' );
  if ( false === $theme_slugs ) {
    $theme_slugs = array_keys( get_themes() );
    set_transient( 'theme_slugs', $theme_slugs, 86400 );
  }
  bw_trace2( $theme_slugs );
  return( $theme_slugs );
}

/** 
 * Get the theme version given the theme name
 *
 * @param string $theme_name
 * @param object $theme_object 
 * @return string version or 'Not installed'
 */
function _bw_get_theme_version( $theme_name, $theme_object=null ) {
  // $file = WP_THEME_DIR . '/'. $theme_name;
  $stylesheet = $theme_name;
  $theme_root = null;
  $theme_data = wp_get_theme( $stylesheet, $theme_root );
  bw_trace2( $theme_data, "theme_data" );
  if ( $theme_data->exists() ) {
    $version = $theme_data->Version;
  } else {
    $version = "Not installed";
  } 
  return( $version );
}

function _bw_get_themes() {
  static $themes = null;
  if ( !$themes ) {
    $theme_slugs = bw_get_theme_slugs();
    oik_require( "admin/oik-depends.inc" );
    $themes = bw_get_all_theme_names( $theme_slugs);
  }
  return( $themes );
} 

/**
 * Return the WP_theme object for the named theme
 */
function bw_get_theme_name( $theme_name="oik" ) {
  $theme_object = wp_get_theme( $theme_name, null );
  bw_trace2( $theme_object, "theme_data" );
  return( $theme_object ); 
}

/**
 * Returns current theme version.
 *
 * @param string $theme Theme name - no longer used
 * @param object $theme_object The theme object
 * @return string theme version
 */
function bw_get_theme_version( $theme, $theme_object ) {
  if ( $theme_object->exists() ) {
    $version = $theme_object->Version;
  } else {
    $version = "Not installed";
  } 
  return( $version );      
}  

/**
 * Determines the server that supports this theme.
 *
 * @param string $theme - theme name
 * @param WP_theme $theme_object - the WP_theme object
 * @param array $theme_data - oik theme registration information
 * @return string  
 */
function bw_get_theme_server( $theme, $theme_object, $theme_data ) {
  $server = bw_array_get( $theme_data, "server", null );
  if ( !$server ) {
    if ( $theme_object->exists() ) {
      $server = $theme_object->get( 'ThemeServer' );
    }
  } 
  if ( !$server ) {
    $server = "&nbsp;";
  }  
  bw_trace2( $server, "theme-server" );
  return( $server );
}


} else {
	//echo __FILE__;
}
