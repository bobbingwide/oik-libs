<?php
if ( !defined( 'OIK_SHORTCODES_INCLUDED' ) ) {
	define( 'OIK_SHORTCODES_INCLUDED', "3.5.2" );
	/**
	 * Library: oik-shortcodes
	 * Provides: oik-shortcodes
	 * Type: shared
	 * Type: shared
	 * Depends:    bwtrace
	 * @copyright (C) Copyright Bobbing Wide 2020-2023
	 *
	 */

	/**
	 * OIK Shortcodes APIs - copied from oik-add-shortcodes.php ( oik base plugin )
	 * with oik base plugin specific logic removed.
	 *
	 * Note: The logic to add the OIK shortcodes is in includes/oik-shortcodes.php
	 */

	/**
	 * Detect the fact that the function to expand a shortcode is not available
	 */
	function _bw_missing_shortcodefunc( $atts, $content, $tag ) {
		global $bw_sc_file;
		// get_last_error ?
		$result='<span>&#91;</span>' . $tag . ']';
		$result.=__( "<b>Unable to locate routine to expand shortcode.</b>", "oik" );

		// Stop it from attempting to load an external file over and over again
		$bw_sc_file[ $tag ]=false;

		return ( $result );
	}

	/**
	 * Wrapper to include_once to prevent Warning messages returned in JSON response
	 *
	 * @param string $file - filename of file to load
	 *
	 * @return bool - return code from include_once or false if file does not exist
	 *
	 */
	function bw_include_once( $file ) {
		if ( $efile=bw_file_exists( $file ) ) {
			$rc=include_once( $efile );
		} else {
			$rc=false;
			bw_trace2( $file, "File does not exist", BW_TRACE_ERROR );
		}

		return ( $rc );
	}

	/**
	 * Check the "enhanced" file exists
	 *
	 * An enhanced file is where we convert the drive letter to uppercase
	 * and convert backslashes to forward.
	 * BUT we don't look for the real file in plugin_paths
	 *
	 */
	function bw_file_exists( $file ) {
		$efile=$file;
		//if ( ':' === substr( $efile, 1, 1 ) ) {
		$efile=ucfirst( $efile );
		//}
		$efile=str_replace( "\\", "/", $efile );
		if ( $efile <> $file ) {
			bw_trace2( $efile, "enhanced file", true, BW_TRACE_DEBUG );
			bw_backtrace( BW_TRACE_DEBUG );
		}
		if ( ! file_exists( $efile ) ) {
			bw_trace2( $efile, "enhanced file", true, BW_TRACE_WARNING );
			$efile=null;
		}

		return ( $efile );
	}

	/**
	 * Loads the file that implements the shortcode if necessary
	 *
	 * The file is expected to be the fully qualified file name.
	 * For oik shortcodes these can be specified using oik_path( 'shortcodes/file.php' )
	 * on the call to bw_add_shortcode().
	 *
	 * @param string $shortcode - the shortcode to expand
	 *
	 * @return bool - false if the file does not exist
	 */
	function bw_load_shortcodefile( $shortcode ) {
		global $bw_sc_file;
		bw_trace2( $bw_sc_file, "bw_sc_file", true, BW_TRACE_VERBOSE );
		$file=bw_array_get( $bw_sc_file, $shortcode, false );
		if ( $file ) {
			$file=bw_include_once( $file );
		}

		return ( $file );
	}

	/**
	 * Invokes the shortcode
	 *
	 * @param string $shortcodefunc function name to invoke
	 * @param string $shortcode name of the shortcode
	 *
	 * @return string the result of the shortcode
	 */
	function bw_load_shortcodefunc( $shortcodefunc, $shortcode ) {
		if ( is_array( $shortcodefunc ) ) {
			if ( is_callable( $shortcodefunc ) ) {
				$scfunc=$shortcodefunc;
			} else {
				// Don't know what to do here
			}
		} else {
			if ( function_exists( $shortcodefunc ) ) {
				$scfunc=$shortcodefunc;
			} else {
				$scfunc='_bw_missing_shortcodefunc';
				if ( bw_load_shortcodefile( $shortcode ) && function_exists( $shortcodefunc ) ) {
					$scfunc=$shortcodefunc;
				}
			}
		}

		return ( $scfunc );
	}

	/**
	 * Save/restore the global post and id
	 *
	 * @param post $saved_post - the post to restore to the globals
	 *
	 * @return post $post
	 *
	 * Note: Using $GLOBALS['var'] is (almost) equivalent to using global $var;
	 *
	 */
	function bw_global_post( $saved_post=null ) {
		if ( isset( $GLOBALS['post'] ) ) {
			$post=$GLOBALS['post'];
		} else {
			$post=null;
		}
		if ( $saved_post ) {
			$GLOBALS['post']=$saved_post;
			$GLOBALS['id']  =$saved_post->ID;
		}

		return ( $post );
	}

	/**
	 * Expands a shortcode if the function is defined for the event
	 *
	 * For oik v2.3 we now support the 'all' event which takes precedence over the event for the specific 'current filter'
	 * except when the current filter is "the_title".
	 * When it's 'the_title' we only expand the shortcode if $the_title was true on bw_add_shortcode().
	 *
	 * We still need to know the current filter in order to determine whether or not any post processing should be performed.
	 *
	 * If the function is not defined then simply return the tag inside []'s
	 * Note: We use the HTML symbol for [ (&#91;) to prevent the shortcode being expanded multiple times
	 * Extract from codex...
	 * `
	 *
	 * ; NOTE on confusing regex/callback name reference:
	 * The zeroeth entry of the attributes array ('''$atts[0]''') will contain the string that matched the shortcode regex,
	 * but ONLY if that differs from the callback name, which otherwise appears as the third argument to the callback function.
	 * ; (Appears to always appear as third argument as of 2.9.2.)
	 *
	 * add_shortcode('foo','foo'); // two shortcodes referencing the same callback
	 * add_shortcode('bar','foo');
	 * produces this behavior:
	 * [foo a='b'] ==> callback to: foo(array('a'=>'b'),NULL,"foo");
	 * [bar a='c'] ==> callback to: foo(array(0 => 'bar', 'a'=>'c'),NULL,"");
	 *
	 * This is confusing and perhaps reflects an underlying bug,
	 * but an overloaded callback routine can correctly determine what shortcode was used to call it,
	 * by checking BOTH the third argument to the callback and the zeroeth attribute.
	 * (It is NOT an error to have two shortcodes reference the same callback routine, which allows for common code.)
	 * `
	 *
	 * @param array $atts - the shortcode parameters
	 * @param string $content - content for a shortcode with start and end codes
	 * @param string $tag - see NOTE
	 *
	 * @return string - the generated HTML
	 */
	function bw_shortcode_event( $atts, $content=null, $tag=null ) {
		global $bw_sc_ev, $bw_sc_ev_pp, $bw_sc_te;
		// bw_trace( "<$tag>", __FUNCTION__, __LINE__, __FILE__, "tag" );
		$cf=current_filter();
		if ( ! isset( $tag ) || $tag == null ) {
			$tag=bw_array_get( $atts, 0, null );
		}
		if ( $cf == "the_title" ) {
			$expand=bw_get_shortcode_title_expansion( $tag );
		} else {
			$expand=true;
		}
		$shortcodefunc=null;
		bw_trace2( $cf, "current_filter $tag $expand $shortcodefunc", true, BW_TRACE_DEBUG );
		if ( $expand ) {
			if ( isset( $bw_sc_ev[ $tag ]['all'] ) ) {
				$shortcodefunc=$bw_sc_ev[ $tag ]['all'];
			} else {
				if ( empty( $cf ) ) {
					$cf='all'; // was 'wp_footer';
				}
				if ( isset( $bw_sc_ev[ $tag ][ $cf ] ) ) {
					$shortcodefunc=$bw_sc_ev[ $tag ][ $cf ];
				}
			}
		}
		//bw_trace( $tag, __FUNCTION__, __LINE__, __FILE__, "tag" );
		$saved_post=bw_global_post();
		$result    ='<span>&#91;</span>' . $tag . ']';
		//bw_trace( $cf, __FUNCTION__, __LINE__, __FILE__, "current_filter" );
		if ( $shortcodefunc ) {
			//bw_trace( $bw_sc_ev, __FUNCTION__, __LINE__, __FILE__, "bw_sc_ev" );
			$atts         =bw_cast_array( $atts );
			$atts         =apply_filters( "oik_shortcode_atts", $atts, $content, $tag );
			$content      =apply_filters( "oik_shortcode_content", $content, $atts, $tag );
			$shortcodefunc=bw_load_shortcodefunc( $shortcodefunc, $tag );
			$result       =call_user_func( $shortcodefunc, $atts, $content, $tag );
			$result       =apply_filters( "oik_shortcode_result", $result, $atts, $content, $tag );
		}

		/**
		 * Regardless of what routine we used to expand the shortcode we still need to check
		 * if there's a specific function to post process the result. e.g. remove attrs in admin processing
		 */
		//bw_trace( $result, __FUNCTION__, __LINE__, __FILE__, "result" );
		if ( isset( $bw_sc_ev_pp[ $tag ][ $cf ] ) ) {
			$ppfunc=$bw_sc_ev_pp[ $tag ][ $cf ];
			if ( function_exists( $ppfunc ) ) {
				$result=$ppfunc( $result, $cf );
			} else {
			    /* translators: %s: function name */
				$result.='<b>' . sprintf( __( 'missing post processing function: %1$s', 'oik' ), $ppfunc ) . '</b>';
			}

			//bw_trace( $result, __FUNCTION__, __LINE__, __FILE__, "result" );

		}
		bw_global_post( $saved_post );

		return $result;
	}

	/**
	 * Casts to an array
	 *
	 * An empty string is converted to array()
	 * not array( 0 => '' ), which is what happens if you simply cast it.
	 *
	 * @param mixed $atts Not an object
	 *
	 * @return array - which may be empty
	 */
	function bw_cast_array( $atts=array() ) {
		if ( is_null( $atts ) ) {
			$atts=array();
		} else {
			if ( is_scalar( $atts ) ) {
				if ( '' == $atts ) {
					$atts=array();
				} else {
					$atts=(array) $atts;
				}
			}
		}

		return ( $atts );
	}

	/**
	 * Strip all tags from a string
	 *
	 * @param string $string - the string from which tags are to be stripped
	 * @param string $current_filter - the current filter ( future use )
	 *
	 * @return string - the stripped string
	 *
	 * bw_strip_tags() is equivalent to esc_attr( strip_tags() )
	 * but it also gets passed the current_filter - future use
	 */
	if ( ! function_exists( "bw_strip_tags" ) ) {
		function bw_strip_tags( $string, $current_filter=null ) {
			$rstring=$string;
			$rstring=strip_tags( $rstring );
			$rstring=esc_attr( $rstring );

			return $rstring;
		}
	}

	/**
	 * Strip tags if the content is being displayed on an admin page
	 *
	 * @param string $string -  - the string from which tags are to be stripped
	 * @param string $current_filter - the current filter ( future use )
	 *
	 * @return string - the stripped string
	 *
	 * @uses bw_strip_tags()
	 */
	function bw_admin_strip_tags( $string, $current_filter=null ) {
		//bw_trace( $string, __FUNCTION__, __LINE__, __FILE__, "string" );
		$rstring=$string;
		if ( is_admin() ) {
			$rstring=bw_strip_tags( $rstring, $current_filter );
		}

		return $rstring;
	}

	/**
	 * Add a shortcode function for a specific set of events
	 *
	 * This is a wrapper API for add_shortcode() that will affect how shortcodes are expanded during do_shortcode() processing.
	 * Instead of calling the shortcode expansion function directly we always invoke bw_shortcode_event()
	 * bw_shortcode_event() checks to see if the shortcode should be expanded in the context.
	 *
	 * As of oik version 2.3 we support a special event of "all" which will allow shorrtcode expansion to be
	 * performed when do_shortcode() is invoked directly rather than during filter processing.
	 * The default eventlist therefore becomes 'all'
	 *
	 *
	 * @param string $shortcode - the shortcode name e.g. bw
	 * @param callback $function - the function to implement the shortcode
	 * @param string $eventlist - the list of events for which the shortcode should be expanded
	 * @param callback $postprocess - function name for performing post processing of the $result in certain contexts
	 * Possible functions are: bw_strip_tags, bw_admin_strip_tags, etcetera tbc
	 *
	 */
	function bw_add_shortcode_event( $shortcode, $function=null, $eventlist='all', $postprocess=null ) {
		global $bw_sc_ev, $bw_sc_ev_pp;
		//bw_trace( $shortcode, __FUNCTION__, __LINE__, __FILE__, "shortcode" );
		if ( $function == null ) {
			$function=$shortcode;
		}
		$events=explode( ",", $eventlist );
		foreach ( $events as $event ) {
			$bw_sc_ev[ $shortcode ][ $event ]=$function;
			if ( $postprocess != null ) {
				$bw_sc_ev_pp[ $shortcode ][ $event ]=$postprocess;
			}
		}
		// bw_trace( $bw_sc_ev, __FUNCTION__, __LINE__, __FILE__, "bw_sc_ev" );
		add_shortcode( $shortcode, "bw_shortcode_event" );
	}

	/**
	 * Add the location for the lazy shortcode
	 *
	 * @param string $shortcode - the shortcode tag
	 * @param string $file - the full file name
	 */
	function bw_add_shortcode_file( $shortcode, $file=null ) {
		global $bw_sc_file;
		if ( $file ) {
			$bw_sc_file[ $shortcode ]=$file;
		}
	}

	/**
	 * Set the value for shortcode expansion for "the_title"
	 *
	 * @param string $shortcode - the shortcode tag
	 * @param bool $the_title - true when the shortcode may be expanded in the title, false otherwise
	 */
	function bw_add_shortcode_title_expansion( $shortcode, $the_title ) {
		global $bw_sc_te;
		$bw_sc_te[ $shortcode ]=$the_title;
	}

	/**
	 * Query the value for shortcode expansion for "the_title"
	 *
	 * @param string $shortcode
	 *
	 * @return bool - true if set, false if it doesn't expand, or null if not specified for this shortcode
	 */
	function bw_get_shortcode_title_expansion( $shortcode ) {
		global $bw_sc_te;
		bw_trace2( $bw_sc_te, "bw_sc_te", true, BW_TRACE_VERBOSE );
		$expand=bw_array_get( $bw_sc_te, $shortcode, null );

		return ( $expand );
	}

	/**
	 * Add a shortcode that safely expands in admin page titles
	 * but is properly expanded in content and widget text
	 *
	 * Up to oik version 2.2 the events that we'd respond to defaulted to
	 *
	 * 'the_content,widget_text,wp_footer,get_the_excerpt,settings_page_bw_email_signature,bp_screens'
	 *
	 *
	 * Note: settings_page_bw_email_signature is included to allow the shortcodes to be shown on the "oik email signature" page
	 * bp_screens is included to support BuddyPress
	 * get_the_excerpt is to support Artisteer 3.1 beta 1
	 * and is used in oik-plugins server
	 *
	 * @param string $shortcode - the shortcode tag
	 * @param string|array $function - the implementing function
	 * @param string $file - the full file name to be loaded when the function is not already loaded
	 * @param bool $the_title - true if the shortcode is allowed to expand in "the_title" processing
	 */
	function bw_add_shortcode( $shortcode, $function=null, $file=null, $the_title=true ) {
		$function=apply_filters( "oik_add_shortcode", $function, $shortcode );
		if ( $function ) {
			bw_add_shortcode_event( $shortcode, $function );
			bw_add_shortcode_title_expansion( $shortcode, $the_title );
			if ( $the_title ) {
				bw_add_shortcode_event( $shortcode, $function, 'the_title', 'bw_admin_strip_tags' );
			}
		}
		if ( $file ) {
			bw_add_shortcode_file( $shortcode, $file );
		}
	}



	/**
	 * Implement "oik_do_shortcode" filter for oik
	 *
	 * Decide whether or not to register shortcodes
	 *
	 * If the content includes a left square bracket ( [ ) then we assume that there is a shortcode
	 * so invoke the "oik_add_shortcodes" action hook to register our own shortcodes
	 * and advise other plugins to register theirs if they haven't already done so.
	 *
	 * Similarly, if the content contains a reusable block we have to make the same assumption.
	 * This is probably more efficient than hooking into render_block or render_block_data
	 *
	 * @param string $content
	 *
	 * @return string unchanged $content
	 */
	function oik_do_shortcode( $content ) {

		if ( null === $content || false === strpos( $content, '[' )
			/* && 	false === strpos( $content, '<!-- wp:blocks ') */ ) {
			// no need to do anything yet!
		} else {
			static $oik_do_shortcode=0;
			if ( ! $oik_do_shortcode ) {
				//bw_trace2( $oik_do_shortcode );
				//bw_backtrace();
				//bw_oik_add_shortcodes();
				/**
				 * Advise plugins to register shortcodes
				 *
				 * Plugins may choose to defer shortcode registration until this action is invoked.
				 */
				do_action( "oik_add_shortcodes" );
			}
			$oik_do_shortcode ++;
		}

		return ( $content );
	}

	/**
	 * Function to invoke when oik-add-shortcodes is loaded
	 *
	 * The 'oik_do_shortcode' filter determines whether or not the content being filtered contains a shortcode
	 * If so, then the shortcodes are registered by "oik_add_shortcodes"
	 * Shortcodes are not expanded until later... during "do_shortcode" processing.
	 *
	 * Fix on oik v2.3: For 'the_content' we use a priority between 0 and 10 to allow other plugins to add shortcodes to the content
	 * without them having to also invoke "oik_do_shortcode"
	 *
	 */
	function bw_oik_add_shortcodes_loaded() {
		//add_filter( "do_shortcode", "oik_do_shortcode", 0 );
		add_filter( 'widget_text', 'oik_do_shortcode', 0 );
		add_filter( 'the_title', 'oik_do_shortcode', 0 );
		//add_filter('wpbody-content', 'do_shortcode' );
		add_filter( 'wp_footer', 'oik_do_shortcode', 0 );
		add_filter( 'get_the_excerpt', 'oik_do_shortcode', 0 );
		add_filter( 'the_excerpt', 'oik_do_shortcode', 0 );
		add_filter( 'the_content', 'oik_do_shortcode', 9 );
		//add_filter('get_pages', 'do_shortcode' );
		add_filter( "oik_do_shortcode", "oik_do_shortcode", 0 );
		//add_action( "oik_add_shortcodes", "bw_oik_add_shortcodes" );
        add_filter( 'term_description', 'oik_do_shortcode', 0 );

		remove_filter( "widget_text", "balanceTags", 10 );
		if ( 1 == get_option( "use_balanceTags" ) ) {
			add_filter( 'widget_text', 'balanceTags', 8 );
		}
		add_filter( 'widget_text', 'do_shortcode' );
		add_filter( 'the_title', 'do_shortcode' );
		//add_filter('wpbody-content', 'do_shortcode' );
		add_filter( 'wp_footer', 'do_shortcode' );
		add_filter( 'get_the_excerpt', 'do_shortcode' );
		add_filter( 'the_excerpt', 'do_shortcode' );
        add_filter( 'term_description', 'do_shortcode' );
		// do_shortcode for 'the_content' should already be registered with priority 11

		// Is there any point in checking if it's an embed request?
		add_filter( 'the_excerpt_embed', "oik_do_shortcode", 0 );
		add_filter( 'the_excerpt_embed', "do_shortcode" );
	}

	bw_oik_add_shortcodes_loaded();


} /* end if defined */
