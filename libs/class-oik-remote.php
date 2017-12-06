<?php // (C) Copyright Bobbing Wide 2012-2017

if ( !defined( "CLASS_OIK_REMOTE_INCLUDED" ) ) {
	define( "CLASS_OIK_REMOTE_INCLUDED", "0.2.0" );

/**
 * Library: class-oik-remote
 * Provided: class-oik-remote
 * Depends: class-oik-update - a cyclical dependency
 * Version: v0.2.0
 * 
 * Implements oik/includes/oik-remote.inc as a shared library.
 * Note: hyphens for plugins, underscores for libraries, hyphens for class libraries :-)
 */ 
 
class oik_remote {


/**
 * Wrapper to wp_remote_head
 *
 * @param string $url - URL for the HEAD request
 * @return request - result of the request
 *
 */
static function bw_remote_head( $url ) {
	$request = wp_remote_head( $url );
	bw_trace2( $request );
	return( $request );
}

/**
 * wrapper to wp_remote_get for both HEAD and json body
 *
 * The resulting array contains the request returned from the call - so that we can look at information in the 'headers' -
 * and the decoded JSON from the retrieved body, if set.
 * Note: The default method, 'GET', can be overridden in the $args array. This allows us to use 'PUT' as well. 
 * 
 * @param string $url - the target URL for the GET
 * @param array $args - additional parameters to wp_remote_get
 * @return array - consisting of the result of the request and the JSON result
 */
static function bw_remote_geth( $url, $args=null ) {
	$request = wp_remote_get( $url, $args );
	bw_trace2( $request, "request" );
	if ( is_wp_error( $request ) ) {
		bw_trace2( $request, "request is_wp_error" );
		$result = null;
	} else {
		$json = wp_remote_retrieve_body( $request );
		bw_trace2( $json );
		if ( empty( $json ) ) {
			$result = null;
		} else {
			$result = json_decode( $json );
		}  
	}
	//bw_trace2( $result, "result", false );
	return( array( $request, $result ) );
}

/**
 * Wrapper to wp_remote_get
 * 
 * @param string $url with parameters already added
 * @return decoded result - a json object OR null 
 */ 
static function bw_remote_get( $url ) {
	$request = wp_remote_get( $url );
	if ( is_wp_error( $request ) ) {
		bw_trace2( $request, "request is_wp_error" );
		$result = null;
	} else {
		$json = wp_remote_retrieve_body( $request );
		bw_trace2( $json );
		if ( empty( $json ) ) {
			$result = null;
		} else {
			$result = json_decode( $json );
		}  
	}
	bw_trace2( $result, "result" );
	return( $result );
}

/**
 * Wrapper to wp_remote_get2
 * 
 * @param string $url with parameters already added
 * @return 
 */ 
static function bw_remote_get2( $url ) {
	$request = wp_remote_get( $url );
	if ( is_wp_error ($request ) ) {
		bw_trace2( $request, "request is_wp_error" );
		$result = null;
	} else {
		$result = self::bw_retrieve_result( $request );
	}
	bw_trace2( $result, "result" );
	return( $result );
}

/**
 * Return the result if the response code is 200 ( OK )
 *
 * For the REST API we also support 201 ( Created ) 
 *
 * @TODO - better handling of error code and body
 *
 * `
 * [body] => [{"code":"empty_password","message":"<strong>ERROR<\/strong>: The password field is empty."}]
 * [response] => Array
 *    (
 *        [code] => 500
 *        [message] => Internal Server Error
 *    )
 * `
 * 
 * @param $request from wp_remote_post()
 * @return result - if it's acceptable 
 */
static function bw_retrieve_result( $request ) {
	$response_code = wp_remote_retrieve_response_code( $request );
	if ( $response_code == 200 || $response_code == 201 ) {
		$response = wp_remote_retrieve_body( $request );
		bw_trace2( $response, $response_code );
		if ( empty( $response ) ) {
			$result = null;
		} else {
			$result = maybe_unserialize( $response ); //json_decode( $json );
		}
		//bw_trace2( "can we see cookies?", "can we?" );
	} else {
		bw_trace2( $response_code, "unexpected response_code" );
		$response_message = wp_remote_retrieve_response_message( $request );
		bw_trace2( $response_message, "response_message" );
		$result = null;
	}
	return( $result );      
}

/**
 * Wrapper to wp_remote_post
 * 
 * @param string $url the request URL
 * @param array $args - array of args including body array 
 * @return unserialized result or null
 */ 
static function bw_remote_post( $url, $args ) {
	$args = self::bw_adjust_args( $args, $url );
	$request = wp_remote_post( $url, $args );
	if ( is_wp_error ($request ) ) {
		bw_trace2( $request, "request is_wp_error" );
		$result = $request; // Was NULL
	} else {
		$result = self::bw_retrieve_result( $request );
	}
	bw_trace2( $result, "result" );
	return( $result );
}

/**
 * Update site_transient_update_plugins for each of our plugins
 * 
 * On the second call to this function we perform our own checks to see if the plugins have been updated.
 * 
 * The response array is updated for each plugin with a new version.
 * 
 * 
 * @param array $transient - structure as in example below
 * @return array $transient - with updates to the response array
 *
 * $plugin_slug is the path and plugin name e.g. oik/oik.php 
 *
 *  [last_checked] => 1342601824
 *  [checked] => Array
 *      (
 *          [backwpup/backwpup.php] => 2.1.11
 *          [oik-pro/oik-altapi.php] => 1.0
 *      )
 *
 *  [response] => Array
 *      (
 *          [backwpup/backwpup.php] => stdClass Object
 *              (
 *                  [id] => 8736
 *                  [slug] => backwpup
 *                  [new_version] => 2.1.12
 *                  [url] => http://wordpress.org/extend/plugins/backwpup/
 *                  [package] => http://downloads.wordpress.org/plugin/backwpup.2.1.12.zip
 *              )
 *      )
 *
 */
static function oik_lazy_altapi_check( $transient ) {
	bw_backtrace();
	static $checks = 0;
	static $responses = array();
	static $responses_built = false;
	$checks++;
	bw_trace2( $checks, "checks" );
	if ( !$responses_built ) {
		$checked = bw_array_get( $transient, "checked", null );
		if ( $checked ) {
			$responses = self::oik_check_checked_for_update( $checked );
			/*
			foreach ( $checked as $plugin => $version ) {
				$response = oik_check_for_update( $plugin, $version );
				if ( $response && !is_wp_error( $response ) ) {
					$responses[$plugin] = $response;
				}  
			} 
			*/
			$responses_built = true;
		}
	}
	bw_trace2( $responses, "responses", false );
	if ( $responses_built ) {
		foreach ( $responses as $plugin => $response ) {
			$transient->response[$plugin] = $response;
		}
	} 
	bw_trace2( $transient, "transient updated", false );
	 
	return( $transient );  
}

/**
 * Check the checked array for updates
 *
 * Attempted performance improvement
 * We only make one call to each server for all the plugins that it supports
 * 
 * 1. Find the servers to talk to
 * 2. Check with each server - passing the cut down checked array
 * 3. Update $responses with updated plugins and, if defined, the apikey for premium plugins
 * 
 * @param array $checked - the array of checked plugins
 * @return array - the responses
 */
static function oik_check_checked_for_update( $checked ) {
	$responses = array();
	bw_trace2();
	$servers = array();
	$apikeys = array();
	foreach ( $checked as $plugin => $version ) {
		$plugin_settings = oik_update::oik_query_plugins_server( oik_update::bw_get_slug( $plugin ) );
		//bw_trace2( $plugin_settings, "plugin_settings", false );
		if ( $plugin_settings ) {
			$server = bw_array_get( $plugin_settings, "server", null ); 
			$server = rtrim( $server, "/" );
			$servers[$server]['checked'][$plugin] = $version;
			$apikeys[$plugin] = bw_array_get( $plugin_settings, "apikey", null );
		}
	}
	bw_trace2( $servers, "servers", false );
	foreach ( $servers as $server => $check_these ) {
		//session_write_close();
		//bw_trace2( $_SESSION, "session", false );
		$server_response = self::oik_check_these_for_update( $server, $check_these );  
		bw_trace2( $server_response, "server_response" );
		if ( !is_wp_error( $server_response) && is_array( $server_response ) && count( $server_response ) ) {
			foreach ( $server_response as $plugin => $aresponse ) {
				if ( $apikeys[$plugin] ) {
					$package = $aresponse->package;
					$package .= "&apikey=" . $apikeys[$plugin];
					$aresponse->package = $package;  
				}
				$responses[$plugin] = $aresponse;
			}
		}  
	}
	//gobang();
	return( $responses );
}

/**
 * Check these plugins for updated versions
 *
 * Pass the array of plugins and their current version to the server and check for updates
 * The server is only expected to return responses for any plugin where there are updates.
 *
 * If the server does not support "check-these" then we'll get an error? 
 */
static function oik_check_these_for_update( $server, $check_these ) {
	$url = $server;
	//$url = "http://qw/wordpress";
	$url .= '/plugins/check-these/';
	$body = array( "action" => "check-these" 
							 //  , "plugin_name" => $plugin 
							 //  , "version" => $version
							 //  , "apikey" => $apikey
							 , "check" => serialize( $check_these )
							 );
	$args = array( "body" => $body, "timeout" => 20 );
	$result = self::bw_remote_post( $url, $args ); 
	if ( $result ) {
		bw_trace2( $result );
		if ( is_wp_error( $result ) ) {
			$response = $result;
		} else { 
			//bw_trace2( $result->new_version, "$version!", false ); 
			//$vc = version_compare( $result->new_version, $version, ">" );
			//bw_trace2( $result->new_version, "new version", false );
			//bw_trace2( $version, "old version", false );
			//bw_trace2( $vc, "vc result", false );
			//  if( isset( $result->new_version ) && version_compare( $result->new_version, $version, '>' ) ) {
			$response = $result;  
			//bw_trace2( $response, "response", );
			if ( !is_array( $response ) && false !== strpos( $response, "Invalid request" ) ) {
				$response = null;
			}
			//    }  
		}
	} else {
		$response = null;
	}
	return( $response );
}

/**
 * Check for theme updates
 * 
 * @param Object $transient 
 * @return Object updated transient Object 
 * 
 * `
option__site_transient_update_themes stdClass Object
(
    [last_checked] => 1367174636
    [checked] => Array
        (
            ...
            [designfolio] => 1.22
            ...
            [oik120206] => 1.0
            [oik120206c] => 
            [oik2012] => 0.2
            [oik20120] => 0.1
            ...
            [thematic] => 1.0.3.2
            ...
        )

    [response] => Array
        (
            [designfolio] => Array
                (
                    [new_version] => 1.23
                    [url] => http://wordpress.org/extend/themes/designfolio
                    [package] => http://wordpress.org/extend/themes/download/designfolio.1.23.zip
                )

            [thematic] => Array
                (
                    [new_version] => 1.0.4
                    [url] => http://wordpress.org/extend/themes/thematic
                    [package] => http://wordpress.org/extend/themes/download/thematic.1.0.4.zip
                )

        )
 `
*/
static function oik_lazy_alttheme_check( $transient ) {
	static $checks = 0;
	static $responses = array();
	static $responses_built = false;
	$checks++;
	bw_trace2( $checks, "checks" );
	if ( !$responses_built ) {
		$checked = bw_array_get( $transient, "checked", null );
		if ( $checked ) {
			foreach ( $checked as $theme => $version ) {
				$response = self::oik_check_for_theme_update( $theme, $version );
				if ( $response && !is_wp_error( $response ) ) {
					$responses[$theme] = $response;
				}  
			} 
			$responses_built = true;
		}
	}
	if ( $responses_built ) {
		foreach ( $responses as $theme => $response ) {      
			$transient->response[$theme] = $response;
		}
		bw_trace2( $responses, "transient updated" );
	} 
	return( $transient );  
}

/**
 * Check an oik-plugins server for an updated plugin 
 * 
 * @param string $plugin plugin name e.g. oik-edd/oik-edd.php
 * @param string $version currently installed version e.g 1.03
 * @return $response if there is a new version else null
 *
 * Note: We pass the API key even if it doesn't get used in the server
 *
 */
static function oik_check_for_update( $plugin, $version ) {
	bw_trace2();
	$response = null;
	$plugin_settings = oik_update::oik_query_plugins_server( oik_update::bw_get_slug( $plugin ) );
	if ( $plugin_settings ) {
		$url = bw_array_get( $plugin_settings, 'server', null );
		$url .= '/plugins/update-check/';
		$apikey = bw_array_get( $plugin_settings, 'apikey', null );
		$body = array( "action" => "update-check" 
								 , "plugin_name" => $plugin 
								 , "version" => $version
								 , "apikey" => $apikey
								 );
		$args = array( "body" => $body, "timeout" => 10 );
		$result = self::bw_remote_post( $url, $args ); 
		if ( $result ) {
			bw_trace2( $result );
			if ( is_wp_error( $result ) ) {
					$response = $result;
			} else { 
				//bw_trace2( $result->new_version, "$version!", false ); 
				//$vc = version_compare( $result->new_version, $version, ">" );
				//bw_trace2( $result->new_version, "new version", false );
				//bw_trace2( $version, "old version", false );
				//bw_trace2( $vc, "vc result", false );
				if( isset( $result->new_version ) && version_compare( $result->new_version, $version, '>' ) ) {
					$response = $result;  
					bw_trace2();
				}  
			}  
		} 
	}
	return( $response );    
}


/**
 * Check an oik-themes server for an updated theme
 * @param string $theme theme name e.g. oik-edd/oik-edd.php
 * @param string $version currently installed version e.g 1.03
 * @return $response if there is a new version else null
 *
 * Note: We pass the API key even if it doesn't get used in the server
 *
 */
static function oik_check_for_theme_update( $theme, $version ) {
	bw_trace2();
	$response = null;
	$theme_settings = oik_update::oik_query_themes_server( $theme );
	if ( $theme_settings ) {
		$url = bw_array_get( $theme_settings, 'server', null );
		$url .= '/themes/update-check/';
		$apikey = bw_array_get( $theme_settings, 'apikey', null );
		$body = array( "action" => "update-check" 
								 , "theme_name" => $theme 
								 , "version" => $version
								 , "apikey" => $apikey
								 );
		$args = array( "body" => $body, "timeout" => 10 );
		$result = self::bw_remote_post( $url, $args ); 
		if ( $result ) {
			bw_trace2( $result, "result-ocftu" );
			if ( is_wp_error( $result ) ) {
				$response = $result;
			} else { 
				if( isset( $result->new_version ) && version_compare( $result->new_version, $version, '>' ) ) {
					// WordPress will issue a Fatal if this is an object.
					$response = (array) $result;  
					bw_trace2( $response, "response=returned-result");
				} else {
					// These can produce info messages if $result->new_version is not set  
					// bw_trace2( $result->new_version, "$version!", false ); 
					// $vc = version_compare( $result->new_version, $version, ">" );
					// bw_trace2( $result->new_version, "new version", false );
					bw_trace2( $version, "old version", false );
					// bw_trace2( $vc, "vc result", false );
				}
			}  
		} else {
			//gobang();
		} 
	}
	return( $response );    
}

/**
 * Determine if this is one of "our" plugins
 *
 * @param mixed $args - object or array expected to contain "slug"
 * @return string $server - URL to connect with 
 *
 * Note: Can we trust slug? Could it be "plugin/plugin-file.php" or just "plugin"
 * It may depend upon the $action
 *
 */
static function oikp_our_plugin( $args ) {
	$args_slug = bw_array_get( $args, "slug", null );
	if ( $args_slug ) {
		$plugin_settings = oik_update::oik_query_plugins_server( $args_slug );
		$server = bw_array_get( $plugin_settings, 'server', null );
	}  
	else 
		$server = null;
	return( $server );
} 

/**
 * Implement "plugin_information" for our own plugins
 

    stdClass Object
        (
            [slug] => backwpup
            [per_page] => 24
        )
        
"http://api.wordpress.org/plugins/info/1.0/$plugin_slug.xml";


 */
static function oik_lazy_pluginsapi( $false, $action, $args ) {
	bw_trace2();
	$response = false; 
	if ( $action == "plugin_information" ) {
		$url = self::oikp_our_plugin( $args);      
		/* We have to exclude the version number (i.e. exclude 1.0 from plugins/info/1.0/ ) since the server end's permalink structure does not expect it */
		if ( $url ) { 
			$url .= "/plugins/info/";
			$post_args =  array( 'timeout' => 15
												 , 'body' => array('action' => $action, 'request' => serialize($args))
												 );
			$response = self::bw_remote_post( $url, $post_args ); 
		}  
	}
	bw_trace2( $response, "response", false );
	return( $response );
}

/**
 * Implement "themes_api" for our own themes 
 * 
 * @param bool $false - false 
 * @param string $action - one of a set of actions
 * - "query_themes" - see below
 * - "feature_list" 
 * - "theme_information" 
 * 
 * @param object $args - for "query_themes"
 * 

(
    [0] => 
    [1] => query_themes
    [2] => stdClass Object
        (
            [page] => 1
            [per_page] => 36
            [fields] => 
            [browse] => featured
        )

)
  where [browse] can be
  "featured" - list the Featured themes
  "new" - list the Newest themes
  "updated" - list the Recently Updated themes
  
  or [search] to search by keyword
  or [author] to search by author
  or [tag] which is an array of tags. e.g.
   
              [tag] => Array
                (
                    [0] => blue
                )

        )
        
  $args for "theme_information"
  array('slug' => $theme, 'fields' => array('sections' => false, 'tags' => false)
      

  
 */
static function oik_lazy_themes_api( $false, $action, $args ) {
		bw_backtrace();
		bw_trace2();
		$response = false;
		return( $response );
}

/**
 * Implement "themes_api_result" filter to add our own themes 
 

  @return unserialised object $result
  (
    [0] => stdClass Object
        (
            [info] => Array
                (
                    [page] => 1
                    [pages] => 1
                    [results] => 6
                )

            [themes] => Array
                (
                    [0] => stdClass Object
                        (
                            [name] => DailyPost
                            [slug] => dailypost
                            [version] => 1.0.5
                            [author] => valeriutihai
                            [preview_url] => http://wp-themes.com/dailypost
                            [screenshot_url] => http://wp-themes.com/wp-content/themes/dailypost/screenshot.png
                            [rating] => 83.4
                            [num_ratings] => 6
                            [homepage] => http://wordpress.org/themes/dailypost
                            [description] => DailyPost is intresting theme ideal for your everyday notes and thoughts, which supports post formats and several customisation options. The theme is a special one because of it's responsive design, thus you will get the pleasure to read the post with your mobile device.
                        )
                     ...
                     one per [results]

 */
static function oik_lazy_themes_api_result( $result, $action, $args ) {
		bw_backtrace();
		bw_trace2();
		/** 
		* We need to do this for EACH theme that is installed to find which servers to query.
		* http://api.wordpress.org/themes/info/1.0/', array( 'body' => array('action' => $action, 'request' => serialize($args)))
		*
		*/
		$url = oik_update::oik_get_themes_server();
		$url .= "/themes/info/";
		$post_args =  array( 'timeout' => 15
											 , 'body' => array('action' => $action, 'request' => serialize($args))
											 );
		$add_result = self::bw_remote_post( $url, $post_args );
		if ( $add_result ) {
			$response = oik_merge_result( $result, $add_result );
		} else {
			$response = $result; 
		}
		bw_trace2( $response, "response", false );
		return( $response );
} 


/**
 * Differences between responses for different requests
 *
 * [browse] = "featured" doesn't return much information in the "info" section. 
 * Perhaps this is because there is less than one page! We could test this by setting "per_page" to a low number.
 * OR have a look at what happens with "search". See below
 *
            [info] => Array
                (
                    [page] => 1
                    [pages] => 0
                    [results] => 
                )
 * [browse] = "new"  - same as featured
 * [browse] = "updated" - same as featured
 * 
 * [search] = keyword
 * [author] = author
             [info] => Array
                (
                    [page] => 1
                    [pages] => 1
                    [results] => 27
                    [author] => 
                )

 * [tag] =    tags
 * 
 * The answer is tricky since ajax takes over at the front end.  
 * So we probably need to see what happens during this processing
 (
    [action] => fetch-list
    [paged] => 3
    [s] => 
    [tab] => search
    [type] => term
    [_ajax_fetch_list_nonce] => 5962dafe3a
    [list_args] => Array
        (
            [class] => WP_Theme_Install_List_Table
            [screen] => Array
                (
                    [id] => theme-install
                    [base] => theme-install
                )

        )

)
*/ 


/**
 * Safer json_decode() 
 * 
 * @param string $json - what we believe to be JSON 
 * @param bool $assoc - set true to convert objects into associative arrays
 */
static function bw_json_decode( $json, $assoc=false ) {
	$decoded = null;
	if ( is_object( $json ) ) {
		bw_trace2( null, null, true, BW_TRACE_ERROR );
	} elseif ( $json ) {
		$pos = strpos( $json, '{' );
		if ( $pos ) {
			$unexpected = substr( $json, 0, $pos );
			bw_trace2( $unexpected, "Unexpected data", true, BW_TRACE_WARNING );
			$json = substr( $json, $pos );
		} 
		$decoded = json_decode( $json, $assoc );
	}
	return( $decoded );
}

	/**
	 * Adjust the args for the wp_remote_post
	 * 
	 * We're trying to avoid some common errors from cURL
	 * Error														  |	Workaround
	 * ---------------------------------- | -----------
	 * cURL error 60: SSL certificate problem: unable to get local issuer certificate	 |	 Set sslverify false for local requests
	 * cURL error 28: Operation timed out after 10000 milliseconds with 0 bytes received | Set timeout to 15 seconds
	 * 
	 * This static method is also implemented as a filter hook for 'http_request_args'
	 * as we need to cater for requests being performed for a plugin / theme download. 
	 * 
	 * @TODO Ensure we don't reduce the timeout time.
	 * 
	 * @param array $args
	 * @param string $url
	 * @return array adjusted args
	 */
	static function bw_adjust_args( $args, $url ) {
		if ( self::are_you_local( $url ) ) {
			$args['sslverify'] = false;
		}
		$args['timeout'] = 15000;
		return $args;
	}
	
	
	/**
	 * Determines if this is a local request
	 * 
	 * @param string $url
	 * @return bool - true if the host part of the $url is considered to be local
	 */
	static function are_you_local( $url ) {
		$local = self::are_you_local_IP( $url );
		if ( !$local ) {
			$local = self::are_you_private_IP( $url );
			if ( !$local ) {
				$local = self::are_you_local_computer( $url );
			}
		}
		return $local;
	}
	
	/**
	 * Determines if this is a local IP
	 *
	 * If the URL is the same as the server then it's a local request.
	 * If the IP for the URL is 127.0.0.1 then it's local.
	 * @TODO Determine whether or not to test for just 127.
	 *
	 * @param string $url e.g. https://qw/wordpress
	 * @return bool - true if a local IP
	 */
	static function are_you_local_IP( $url ) {
		$local_host = $_SERVER['SERVER_NAME'];
		$remote_host = parse_url( $url, PHP_URL_HOST );
		$local = ( $local_host == $remote_host ); 
		if ( !$local ) {
			$remote_ip = gethostbyname( $remote_host );
			$local = $remote_ip === "127.0.0.1";
		}	
		return $local;
	}
	
	/**
	 * Determines if this is a private IP
	 * 
	 * The following ranges are reserved for private networks.
	 *
   * - 192.168.0.0 - 192.168.255.255 (65,536 IP addresses)
	 * - 172.16.0.0 - 172.31.255.255 (1,048,576 IP addresses)
	 * - 10.0.0.0 - 10.255.255.255 (16,777,216 IP addresses)
	 *
	 * @TODO - Determine if we need to check all ranges. 
	 * 
	 * @param string $url e.g. https://qw/wordpress
	 * @return bool - true if a private IP
	 */
	static function are_you_private_ip( $url ) {
		$host = parse_url( $url, PHP_URL_HOST );
		$local_ip = gethostbyname( $host );
		$local = ( 0 === strpos( $local_ip, "192.168" ) );
		return $local;
	}
	
	/**
	 * Determines if the URL is on the local computer
	 * 
	 * @param string $url
	 * @return bool 
	 */
	static function are_you_local_computer( $url ) {
		$local_computer = self::get_computer_name();
		$remote_host = parse_url( $url, PHP_URL_HOST );
		$local = $local_computer == $remote_host;
		return $local;
	}
	
	/**
	 * Gets the computer name
	 * 
	 * @return string lower case version of computer name, if set
	 */
	static function get_computer_name() {
		$computer_name = bw_array_get( $_SERVER, "COMPUTERNAME", null );
		if ( $computer_name ) {
			$computer_name = strtolower( $computer_name );
		}
		//echo $computer_name;
		return $computer_name;
	}

}

} else {
	//echo __FILE__;
}
               







