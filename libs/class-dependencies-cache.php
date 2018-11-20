<?php // (C) Copyright BobbingWide 2017, 2018
if ( !defined( "CLASS_DEPENDENCIES_CACHE_INCLUDED" ) ) {
define( "CLASS_DEPENDENCIES_CACHE_INCLUDED", "0.1.1" );

/**
 * Script and style functions
 * 
 * Library: class-dependencies-cache
 * Depends: 
 * Provides: dependencies_cache class
 *
 * This class implements methods used by oik-widget-cache and oik's shortcode help logic.
 * 
 * For oik-widget cache it provides methods to allow widgets to enqueue styles and scripts,
 * storing the changes as part of the cached data so that it can be replayed when 
 * the cached widget is redisplayed. 
 * 
 * For oik-sc-help it's needed for displaying the links for styles and scripts associated with HTML snippets.
 * This information may get passed back to the browser so that it can load the required scripts and styles.
 * 
 */

class dependencies_cache {

	public $bw_scripts;
	public $bw_styles;
	
	public $registered_scripts;
	public $registered_styles;
	public $queued_scripts;
	public $queued_styles;
	
	public $captured_html = null;
	public $latest_html = null;
	
	/**
	 * @var dependencies_cache the true instance
	 */
	private static $instance;
	
	/**
	 * Return a single instance of this class
	 *
	 * @return object 
	 */
	public static function instance() {
		if ( !isset( self::$instance ) && !( self::$instance instanceof self ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/**
	 * Constructor for dependencies_cache
	 * 
	 * Do we need to save the globals immediately?
	 * Probably not when we're using this for cacheing.
	 *
	 */
	function __construct() {
		add_action( "wp_footer", array( $this, "echo_captured_html" ) );
		add_action( "admin_print_footer_scripts", array( $this, "echo_captured_html" ) );
	}
	
	function reset_scripts() {
		$this->registered_scripts = array();
		$this->queued_scripts = array();
	}
	
	function reset_styles() {
		$this->registered_styles = array();
		$this->queued_styles = array();
	}

	function save_dependencies() {
		$this->reset_scripts();
		$this->reset_styles();
		$this->save_scripts();
		$this->save_styles();
	}
	
	/**
	 * Saves the current state of dependencies for scripts
	 *
	 * 
	 */
	function save_scripts() {
		global $wp_scripts;
		$init = wp_scripts();

		$this->bw_scripts = clone $wp_scripts;

		$count_wp = count( $wp_scripts->registered );
		$count_bw = count( $this->bw_scripts->registered );
    bw_trace2( $count_wp, "count_wp and bw: $count_bw" );
		bw_trace2( $this->bw_scripts, "bw_scripts", false, BW_TRACE_VERBOSE );
		
	}
	
	/**
	 * Saves the current state of dependencies for styles
	 *
	 * 
	 */
	function save_styles() {
	
		global $wp_styles;
		$init = wp_styles();

		$this->bw_styles = clone $wp_styles;

		$count_wp = count( $wp_styles->registered );
		$count_bw = count( $this->bw_styles->registered );
		bw_trace2( $count_wp, "count_wp and bw: $count_bw" );
		bw_trace2( $this->bw_styles, "bw_styles", false, BW_TRACE_VERBOSE );
	}
	
	/**
	 * Queries the changes to dependencies.
	 *
	 * The code needs to take into account dependencies that have been registered as well as those that have been enqueued.
	 * So we need to find the differences in multiple arrays.
 	 *
	 * field      | handle? | purpose
	 * ---------- | ------  | -----------
	 * registered | yes     | to repeat wp_register_script
	 * queue      | yes     | to repeat wp_enqueue_script after registered
	 * to_do      | no?			|
	 * done       | no?			|
	 * args       | no?			|
	 * groups     |	no?			|
	 * 
	 */
	function query_dependencies_changes() {
		$this->query_scripts_changes();
		$this->query_styles_changes();
		return( true );
	}
	
	/**
	 * Determines scripts changes.
	 * 
	 */
	function query_scripts_changes() {
	
		global $wp_scripts;
		$this->reset_scripts();
	
		$count_wp = count( $wp_scripts->registered );
		$count_bw = count( $this->bw_scripts->registered );
		bw_trace2( $count_wp, "count_wp and bw: $count_bw" );
	
		bw_trace2( $wp_scripts, "wp_scripts" );
		bw_trace2( $this->bw_scripts, "bw_scripts" );
	
		$this->registered_scripts = array_udiff_assoc( $wp_scripts->registered, $this->bw_scripts->registered, array( $this, "wp_dependency_compare" ) );
		bw_trace2( $this->registered_scripts, "registered scripts" );
		$this->queued_scripts = array_diff( $wp_scripts->queue, $this->bw_scripts->queue );
		bw_trace2( $this->queued_scripts, "enqueued scripts" );
	}
	
	/**
	 * Determines styles changes.
	 */
	function query_styles_changes() {
	
		global $wp_styles;
		$this->reset_styles();
	
		$count_wp = count( $wp_styles->registered );
		$count_bw = count( $this->bw_styles->registered );
		bw_trace2( $count_wp, "count_wp and bw: $count_bw" );
	
		bw_trace2( $wp_styles, "wp_styles" );
		bw_trace2( $this->bw_styles, "bw_styles" );
	
		$this->registered_styles = array_udiff_assoc( $wp_styles->registered, $this->bw_styles->registered, array( $this, "wp_dependency_compare" ) );
		bw_trace2( $this->registered_styles, "registered styles" );
		$this->queued_styles = array_diff( $wp_styles->queue, $this->bw_styles->queue );
		bw_trace2( $this->queued_styles, "enqueued styles" );
	}
	
	/**
	 * Compares dependency objects.
	 *
	 * We don't expect them to be different. 
	 * All we really need is a callback function to be used by array_udiff_assoc()
	 * 
	 * @param object $wp_script - the object that may have changed
	 * @param object $bw_script - the saved object
	 * @return integer - less than, equal to, or greater than zero
	 */
	function wp_dependency_compare( $wp_script, $bw_script ) {
		bw_trace2();
		return( 0 );
	}
	
	/**
	 * Registers a script.
	 * 
	 * @TODO We may be able to get away with just passing the WP_Dependency object
	 * ... but not through register_script
	 *
	 * @param _WP_Dependency $register
	 */
	function register_script( $register ) {
		$src = set_url_scheme( $register->src );
		$footer = bw_array_get( $register->extra, 'group', false );
		wp_register_script( $register->handle, $src, $register->deps, $register->ver, $register->args, $footer );
	}
	
	/**
	 * Registers a style.
	 *
	 * @param _WP_Dependency $register
	 */
	function register_style( $register ) {
		$src = set_url_scheme( $register->src );
		$footer = bw_array_get( $register->extra, 'group', false );
		wp_register_style( $register->handle, $src, $register->deps, $register->ver, $register->args, $footer );
	}
	
	/** 
	 * Enqueues a script
	 *
	 * @param string $queue - the handle
	 */
	function enqueue_script( $queue ) {
		wp_enqueue_script( $queue );
	}
	
	/** 
	 * Enqueues a style
	 *
	 * @param string $queue - the handle
	 */
	function enqueue_style( $queue ) {
		wp_enqueue_style( $queue );
	}
	
	
	/**
	 * Returns serialized dependencies
	 * 
	 * @TODO This doesn't actually do the serializing
	 * 
	 * @return array 
	 * 
	 */
	function serialize_dependencies() {
		$dependencies = array();
		$dependencies['scripts'] = $this->registered_scripts;
		$dependencies['queued_scripts'] = $this->queued_scripts;
		$dependencies['styles'] = $this->registered_styles;
		$dependencies['queued_styles'] = $this->queued_styles;
		return( $dependencies );
	}
	
	/**
	 * Reloads from serialized dependencies
	 *
	 * @param array $dependencies 
	 */
	function reload_dependencies( $dependencies ) {
		$this->registered_scripts = $dependencies['scripts'];
		$this->queued_scripts = $dependencies['queued_scripts'];
		$this->registered_styles = $dependencies['styles'];
		$this->queued_styles = $dependencies['queued_styles'];
	}
	
	/**
	 * Replays dependencies onto the queues.
	 * 
	 * Replays dependencies after a reload
	 */ 
	function replay_dependencies() {
		$this->replay_scripts();
		$this->replay_styles();
	}

	/**
	 * Replays scripts.
	 *
	 */ 
	function replay_scripts() {
		foreach ( $this->registered_scripts as $register ) {
			$this->register_script( $register );
		}
		foreach ( $this->queued_scripts as $queue ) {
			$this->enqueue_script( $queue );
		}
	}
	
	/**
	 * Replays styles.
	 */
	function replay_styles() {
		foreach ( $this->registered_styles as $register ) {
			$this->register_style( $register );
		}
		foreach ( $this->queued_styles as $queue ) {
			$this->enqueue_style( $queue );
		}
	}
	
	/**
	 * Echoes captured HTML 
	 * 
	 * Since we're going to capture scripts ourselves then we need to implement
	 * a footer function to echo the captured stuff later on.
	 * We respond to wp_footer and admin_print_footer_scripts to do this.
	 */
	function echo_captured_html() {
		if ( $this->captured_html ) {
			echo $this->captured_html;
		}
		$this->captured_html = null;
	}
	
	/**
	 * Captures scripts
	 *
	 * Performs an early invocation of the code to display footer scripts, saving
	 * the latest output in latest_html and accumulating the lot in captured_html.
	 * 
	 * Note: We don't use wp_print_footer_scripts() since this can have side effects
	 * due to other hooks being attached to 'wp_print_footer_scripts'
	 * We therefore directly call the private action hook that WordPress uses.
	 * 
	 * @TODO Confirm this is acceptable.
	 */
	function capture_scripts() {
		if ( !doing_filter( "replace_editor" ) ) {
			ob_start();
			_wp_footer_scripts();
			$html = ob_get_contents();
			ob_end_clean();
			$this->captured_html .= $html;
			$this->latest_html = $html;
		}
	}	
	
	/** 
	 * Returns the captured HTML
	 */
	function get_captured_html() {
		return $this->captured_html;
	}
	
	/** 
	 * Returns the latest HTML
	 */
	function get_latest_html() {	
		return $this->latest_html;
	}
	

}

} /* end if !defined */
	



