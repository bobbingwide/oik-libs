<?php
if ( !defined( 'OIK_L10N_INCLUDED' ) ) {
define( 'OIK_L10N_INCLUDED', '3.2.2' );
/**
 * @copyright (C) Copyright Bobbing Wide 2017-2023
 * Shared library for localization.
 *
 * Library: oik-l10n
 * Provides: oik-l10n
 * Type:
 *
 * The theory is something like this:
 *
 * - makepot's job is to find strings that can be translated
 * - WordPress.org expects each plugin and theme to use a unique domain
 * - though it doesn't enforce it
 * - So makepot outputs any translatable string regardless of the value of $domain
 * - Prove this.
 *
 * - which means translators end up translating the same string over and over
 * - especially if we use shared libraries ( a la oik ) or Composer dependency
 *
 * - Users don't really care who did the translation
 * - They just want it in their language
 *
 * - If we use oik_require_lib()
 * - AND the library file knows that translation will be required
 * - then we can hook into `gettext` to provide Just In Time - Just Translate It logic
 *
 * @TODO - Determine if we really need to decide which domains we need to cater for.
 * If there are a lot of existing shared libraries then we might need to handle a set of
 * domains against which to match.
 */
 
 /**
  * Enables/disables "Just Translate It".
	* 
	* This is a lazy implementation that defers loading of the domain until it's actually needed.
	* 
	* @param string|bool|null $domain
	*/
	function oik_l10n_enable_jti( $domain=true ) {
		switch ( $domain ) {
			case true:
			case null:
				$priority = has_filter( "gettext", "oik_l10n_gettext" );
				if ( false === $priority  ) {
					add_filter( "gettext", "oik_l10n_gettext", 10, 3 );
					$priority = has_filter( "gettext", "oik_l10n_gettext" );
				}
				break;
				
			case false:
				remove_filter( "gettext", "oik_l10n_gettext", 10 );
				break;
				
			default:
				// Support for specifying domain names not yet implemented.
		}
			
	}
	
	/**
	 * Implements gettext for Just In Time - Just Translate It logic
	 * 
	 * We only perform translation when the domain is null and the null domain has not 
	 * already been loaded. 
	 * 
	 * @param string $translation the translated string
	 * @param string $text the original string
	 * @param string|null $domain 
	 */
	function oik_l10n_gettext( $translation, $text, $domain ) {
		if ( null === $domain ) {
			//bw_trace2();
			$try_again = !is_textdomain_loaded( $domain );
			bw_trace2( $try_again, "try again", false, BW_TRACE_VERBOSE );
			if ( $try_again ) {
				oik_l10n_load_domain( $domain );
				if ( is_textdomain_loaded( $domain ) ) {
					$translation = translate( $text, $domain );
				}
			}
		}
		return $translation;
	}
	
	/**
	 * Tests if the domain is loaded
	 *
	 * From WordPress 6.3 this just calls is_textdomain_loaded()
	 * 
	 * @param string|null $domain
	 * @return bool
	 */
	function oik_l10n_domain_loaded( $domain ) {
		return is_textdomain_loaded( $domain );
	}
	
	/**
	 * Loads the merged domain
	 * 
	 * @param string|null $domain Unique domain identifier
	 */
	function oik_l10n_load_domain( $domain ) {
		global $l10n;
		$merged_domain = new MO();
		if ( is_array( $l10n ) && count( $l10n ) ) {
			foreach (  $l10n as $loaded_domain => $MO ) {
				if ( $domain !== $loaded_domain ) {
					$merged_domain->merge_with( $MO );
				}
			}
		}
		$l10n[ $domain ] = &$merged_domain;
		//oik_l10n_trace( true );
	}
	
	/**
	 * Traces parts of $l10n.
	 *
	 * For debugging.
	 */
	function oik_l10n_trace( $details=false, $domain=null ) {
		global $l10n;
		
		if ( is_array( $l10n ) && count( $l10n ) ) {
			bw_trace2( array_keys( $l10n) , "array keys" );
		}
		if ( $details  ) {
			if ( isset( $l10n[$domain]) ) {
				$count = count( $l10n[ $domain]->entries );
				bw_trace2( $count, "l10n after null load", false );
			} else {
				bw_trace2( $domain, "Domain not loaded", false );
			}
		}
	}
 
 
} /* end if !defined */
