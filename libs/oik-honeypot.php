<?php // (C) Copyright Bobbing Wide 2016
if ( !defined( "OIK_HONEYPOT_INCLUDED" ) ) {
define( "OIK_HONEYPOT_INCLUDED", "3.0.1" );

/**
 * Honeypot APIs
 * 
 * Library: oik-honeypot
 * Provides: oik-honeypot
 * Depends: 
 *
 * Implements action and filter hooks to enable honeypots to be created and validated
 * to prevent spam submissions of contact forms, spam subscriptions to newsletters etc.
 *
 */

/**
 * Function to invoked when oik-honeypot library is loaded
 *
 */ 
function oik_honeypot_loaded() {
	add_action( "oik_add_honeypot", "oik_honeypot_add_honeypot" );
	add_action( "oik_check_honeypot", "oik_honeypot_check_honeypot" );
}

oik_honeypot_loaded(); 

/**
 * Add a honeypot field to a form 
 *
 * We need to display an input field that is hidden from the normal user by display: none;
 * This may be completed by the spam bot since it thinks it's a field to complete.
 
		<p class="th_rh_name_field">
			<label for="th_rh_name"><?php _e( 'Only fill in if you are not human', 'registration-honeypot' ); ?></label><br />
			<input type="text" name="th_rh_name" id="th_rh_name" class="input" value="" size="25" autocomplete="off" /></label>
		</p>
 */
function oik_honeypot_add_honeypot() {
	$field_name = oik_honeypot_field_name();
	//span( "label $field_name" );
	//e( "Humans leave this blank" );
	//epan();
	stag( "div", null, null, kv( "style", "display:none" ));
	BW_::bw_textfield( $field_name, 10, "Humans leave this blank", "" );
	etag( "div" );
	

}

/**
 * Ensure the honeypot field is empty 
 *
 * If this function detects the honeypot field after some of the page has been displayed then
 * the message will be the last thing that appears on the page.
 * The UI doesn't need to be that fancy; we do believe this is a spammer/ spam bot after all.
 * 
 * @param string $message Message to display when dying
 */
function oik_honeypot_check_honeypot( $message=null ) {
	$field_name = oik_honeypot_field_name();
	$value = bw_array_get( $_REQUEST, $field_name, null );
	if ( $value ) {
		if ( !$message ) {
			add_filter( "oik_honeypot_message", "oik_honeypot_message", 9, 2 ); 
			$message = apply_filters( "oik_honeypot_message", "Request denied.", $value );
		}
		wp_die( $message );
	}
}

/**
 * Return the honeypot field name
 *
 * @param string $name preferred field name
 * @return string selected field name
 */
function oik_honeypot_field_name( $name=null ) {
	static $field_name;
	if ( $name || is_null( $field_name ) ) {
		if ( null === $name ) {
			$name = "oik_saccharin_bowl";
		}
		$field_name = $name;
	}
	return( $field_name );
}

/**
 * Return some pseudo random messages to display
 *
 * @return array Associative array of messages keyed by single digit - not uppercase nor a blank
 */
function oik_honeypot_messages() {
	$messages = array( "s" => "You're not a sentient being are you?"
									 , "b" =>	"Unexpected response received."
									 , "j" => "You filled out a form field that was created to stop spammers. Please go back and try again or contact the site administrator if you feel this was in error."
                   );
	return( $messages );									
}

/**
 * Implement "oik_honeypot_message" filter for oik-honeypot
 *
 * Randomise the message that's displayed based on the value entered in the honeypot field
 * We look at the first character and lower case it.
 * Anything that doesn't match returns the default message.
 * 
 * @param string $message - Current message
 * @param string $value - the unexpected value of the honey pot field
 * @return string Potentially changed message 
 */
function oik_honeypot_message( $message, $value ) {
	$value = trim( $value ); 
	$char = substr( $value, 0, 1 ); 
	$char = strtolower( $char );
	$messages = oik_honeypot_messages();
	$message = bw_array_get( $messages, $char, $message );  
	return( $message );
}

} /* end !defined */
