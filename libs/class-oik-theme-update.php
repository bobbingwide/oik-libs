<?php // (C) Copyright Bobbing Wide 2016
if ( !defined( "CLASS_OIK_THEME_UPDATE_INCLUDED" ) ) {
define( "CLASS_OIK_THEME_UPDATE_INCLUDED", "0.0.1" );

/**
 * Implements oik's theme update logic
 *
 * Class: OIK_Theme_Update
 
 *
 * Note: We use bw_trace2() and bw_backtrace() but can't use trace levels since we don't know if trace is actually available. 
 * @TODO: Confirm the above!
 * 
 * We also use oik library functions with bootstrapping logic 
 */
class OIK_Theme_Update {

	/**
	 * @var $instance - the true instance
	 */
	private static $instance;

	/**
	 * Return the single instance of this class
	 */
	public static function instance() {
		if ( !isset( self::$instance ) && !( self::$instance instanceof self ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/** 
	 * Constructor for OIK_Theme_Update
	 * 
	 */
	function __construct() {
	}
	
	/**
	 * Display an "update" message
	 * 
	 * @param string $text the message to display
	 * @return string the generated HTML
	 */								 
	function show_update_message( $text ) {
		$message = '<tr class="theme-update-tr">';
		$message .= '<td colspan="3" class="theme-update colspanchange">';
		$message .= '<div class="update-message">';
		$message .= $text;
		$message .= "</div>";
		$message .= "</td>";
		$message .= "</tr>";
		echo $message;
	}
	
	/**
	 * Display an "update-nag" message
	 */
	function show_update_nag( $text ) {
		$message = '<div class="update-nag">';
		$message .= $text;
		$message .= "</div>";
		echo $message;
	}
	
	/**
	 * Implement "after_theme_row" for this theme
	 * 
	 * Quick and dirty solution to decide if data migration is required
	 * and if so produce a link to the Migration settings page.
	 */
	function after_theme_row( $theme_file, $theme_data, $status ) {
		bw_trace2();
		$message = "Use oik to check for updates";
		$this->show_update_message( "$message" );
	}
	
	/**
	 * Returns admin page URL 
	 */
	public static function get_page_url() {
		$args = array( 'page' => 'oik_themes' );
		$url = add_query_arg( $args, admin_url( "admin.php" ) );
		return $url;
	}

	/**
	 * Implement "theme_action_links" 
	 * 
	 * @TODO Determine if it's really necessary to create the link to the "oik themes" page.
	 *
	 */
	function theme_action_links( $links, $file, $theme_data, $context ) {
		bw_trace2();
		//bw_trace2( theme_basename( __FILE__ ), "theme_basename" );
		//if ( $file == theme_basename( __FILE__ ) ) {
			$links['update'] =  '<a href="' . esc_url( self::get_page_url() ) . '">'.esc_html__( 'Settings' , 'oik-weight-zone-shipping-pro').'</a>';
		//}
		return( $links );
	}
	
	function query_menu( $menu_slug ) {
		global $submenu, $menu, $_wp_real_parent_file, $_wp_submenu_nopriv, $_registered_pages, $_parent_pages;
		
		//$menu_slug = bw_array_get( $submenu, $menu_slug, null );
		$menu_slug = array_key_exists( $menu_slug, $submenu );
		
		//bw_trace2( $submenu, "submenu", true );
		//bw_trace2( $menu, "menu", false );
		//bw_trace2( $_registered_pages, "_registered_pages", false );
		//bw_trace2( $_parent_pages, "_parent_pages", false );
		return( $menu_slug );
	} 

	/**
	 * Implement "admin_menu" 
	 *
	 * We do not need to implement the "admin_menu" if oik has already done it.
	 * Can we check did_action( "oik_admin_menu" ) ?
	 * 
	 *
	 * Actually, we need to check for both oik_menu and oik_themes 
		
	 * 
	 * 
		//add_options_page( __( 'oik themes', 'oik' ), __( 'themes', 'oik' ), 'manage_options', "api-key-config", array( $this, 'oik_themes_do_page' ) );
	 */
	function admin_menu() {
		$oik_themes = array( $this, 'oik_themes_do_page' );
		$menu_slug = $this->query_menu( "oik_menu" );
		bw_trace2( $menu_slug, "menu_slug" );
		$themes_slug = $this->query_menu( "oik_themes" );
		if ( !$menu_slug ) {
			$hook = add_menu_page( __('[oik] Options', 'oik'), __('oik themes', 'oik'), 'manage_options', 'oik_themes', $oik_themes );
			bw_trace2( $hook, "hook" );
			
		}
		
		if ( !$themes_slug ) {
			add_submenu_page( 'oik_menu', __( 'oik themes', 'oik' ), __('themes', 'oik'), 'manage_options', 'oik_themes', $oik_themes );
			add_submenu_page( 'oik_plugins', __( 'oik themes', 'oik' ), __('themes', 'oik'), 'manage_options', 'oik_themes', $oik_themes );
			$loaded = $this->bootstrap_oik_libs();
			if ( $loaded ) {
				$dependencies = array( "class-bobbcomp" => "0.0.1" 
														 , "bobbfunc" => "3.0.0"
														 , "class-oik-update" => "0.1.0"
														 );
				$loaded = $this->require_dependencies( $dependencies ); 
				if ( $loaded ) {
					do_action( "oik_register_theme_server" );
				}
			}	
		}		
	}

	/**
	 * Implement oik's themes page
	 *
	 * This allows oik-weight-zone-shipping-pro and other oik themes to 
	 * implement automatic / manual updates from an oik-themes server.
	 *
	 * The logic caters for oik, or another theme, already providing the logic. See "admin_menu".
	 * 
	 * When we've 
	 */
	function oik_themes_do_page() {
		$loaded = $this->bootstrap_oik_libs();
		if ( $loaded ) {
			$dependencies = array( "class-bobbcomp" => "0.0.1" 
													 , "bobbfunc" => "3.0.0"
													 , "bobbforms" => "3.0.1"
													 , "oik-admin" => "3.0.1"
													 , "oik-depends" => "3.1.0"
													 , "oik_themes" => "0.0.2"
													 );
			
			$loaded = $this->require_dependencies( $dependencies ); 
			
			//$bobbcomp = $this->require_lib( "class-bobbcomp", "0.0.1" );
			//if ( $bobbcomp ) {
			//		$bobbfunc = $this->require_lib( "bobbfunc", "3.0.0" );
			//		if ( $bobbfunc ) { 
			//			$bobbforms = $this->require_lib( "bobbforms", "3.0.1" );
			//				if ( $bobbforms ) {
			//					$admin = $this->require_lib( "oik-admin", "3.0.1" );
			//					if ( $admin ) {
			//						$depends = $this->require_lib( "oik-depends", "3.1.0" ); 
			//						if ( $depends ) {
			//							$themes = $this->require_lib( "oik_themes", "0.1.0" );
			//							if ( $themes ) {
			if ( $loaded ) {							
				oik_lazy_themes_server_settings();
				bw_flush()	;
			} else {
				$this->show_update_nag( "eh?" );
			}
		}		
	}
	
	/**
	 * Load the dependent libraries
	 * 
	 * @param array of dependent libraries and minumum required versions
	 * @return bool|null 
	 */
	function require_dependencies( $dependencies ) {
		foreach ( $dependencies as $lib => $version ) {
			$loaded = $this->require_lib( $lib, $version );
			if ( !$loaded ) {
				break;
			}
		}
		return( $loaded );
	}
	
	/**
	 * Require a library
	 * 
	 * @param string $lib library name
	 * @param string $version the required minimum version
	 * @return bool true if the required version has been loaded
	 */
	function require_lib( $lib, $version='*' ) {
		$lib_loaded = oik_require_lib( $lib );
		if ( $lib_loaded && !is_wp_error( $lib_loaded ) ) {
			$lib_loaded = $this->version_check( $lib, $version );
		} else {
			bw_trace2( $lib_loaded, "require_lib error", true );
			bw_backtrace();
			$this->show_update_nag( "Failed to load library: $lib. version: $version ");
			
			$lib_loaded = null;
		}
		return( $lib_loaded );
	}
	
	/**
	 * Check minimum required version loaded
	 * 
	 * 
	 */
	function version_check( $lib, $version='*' ) {
		$constant_name = str_replace( "-", "_", $lib );
		$constant_name = strtoupper( $constant_name );
		$constant_name .= '_INCLUDED';
		if ( defined( $constant_name ) ) {
			$current_version = constant( $constant_name );
			$acceptable = $this->compatible_version( $current_version, $version );
		} else {
			$current_version = "unknown";
			$acceptable = false;
		}
		if ( !$acceptable ) { 
			$this->report_error( "Incompatible version already loaded. Library: $lib. Current version: $current_version. Required version: $version" );
		}
		return( $acceptable );
	}

	function report_error( $text ) {
		$this->show_update_nag( $text );
	}

	/**
	 * Checks compatible versions
	 *
	 */
	function compatible_version( $current_version, $required_version ) {
    bw_trace2( null, null, true, BW_TRACE_VERBOSE );
    if ( "*" != $required_version ) {
      $version_compare = version_compare( $current_version, $required_version );
      $acceptable = false;
      bw_trace2( $version_compare, "version compare", false, BW_TRACE_VERBOSE );
      switch ( $version_compare ) {
        case 0:
            $acceptable = true;
          break;
        case -1:
          break;
          
        default:
          // Now we have to check semantic versioning
          // but in the mean time pretend it's acceptable
          $acceptable = true;
      }
        
    } else { 
      $acceptable = true;
    }
    return( $acceptable );
  }

	/**
	 *
	 */
	function bootstrap_oik_libs() {
		$loaded = false;
		if ( function_exists( "oik_require_lib" ) ) {
			$loaded = true;
		} else {
			require_once( __DIR__ . "/oik_boot.php" );
			$loaded = true;
		}
		
		if ( $loaded && function_exists( "oik_lib_fallback" ) ) {
			oik_lib_fallback( __DIR__ );
			oik_init();
		}
		//echo "Loaded!";
		//print_r( get_included_files() );
		return( $loaded );
	}

}

} /* end if !defined */
