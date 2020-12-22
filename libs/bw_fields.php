<?php // (C) Copyright Bobbing Wide 2013-2018, 2020
if ( !defined( "BW_FIELDS_INCLUDED" ) ) {
define( "BW_FIELDS_INCLUDED", "4.1.1" );

/**
 * Library: bw_fields
 * Provides: bw_fields
 * Depends: on certain oik base functions
 * 
 */

/**
 * Query the field type given the field name
 *
 * Returns the field type of the registered field or null
 *
 * @param string $name - the field name e.g. _date
 * @param string $field_type - the field type e.g. date or null
 */
if ( !function_exists( "bw_query_field_type" ) ) {  
function bw_query_field_type( $name ) {
  global $bw_fields;
  $field = bw_array_get( $bw_fields, $name, null );
  if ( $field ) {
    $field_type = bw_array_get( $field, "#field_type", null );
  } else {
    bw_trace2( $name, "Invalid/unrecognised field name" );
    //bw_backtrace();
    $field_type = null;
  }
  return( $field_type );
}
} 

/** 
 * Format a custom column on admin pages 
 *
 * Implements "manage_${post_type}_posts_custom_column" action for oik/bw custom post types
 * 
 * @param string $column - the field being formatted
 * @param ID $post_id - the ID of the post being displayed
 *
 * @uses bw_custom_column to format the data
 * @uses bw_flush to flush the output 
 */
if ( !function_exists( "bw_custom_column" ) ) {
function bw_custom_column_admin( $column, $post_id ) {
	$post_type = bw_determine_post_type_from_hook( current_filter() );
	if ( $post_type && bw_field_registered_for_object_type( $column, $post_type ) ) {
		bw_custom_column( $column, $post_id );
		bw_flush();
	}
}

/**
 * Formats custom column data for a taxonomy
 *
 * The bw_custom_column_taxonomy filter allows you to change the separator between terms.
 *
 * When using the [bw_field] or [bw_fields] shortcode an alternative approach is to hook into the 'oik_shortcode_result' filter
 * 
 * @param string $column
 * @param integer $post_id
 */
function bw_custom_column_taxonomy( $column, $post_id ) {
  $terms = get_the_term_list( $post_id, $column, "", ",", "" );
	$terms = apply_filters( "bw_custom_column_taxonomy", $terms, $column, $post_id );
  e( $terms );
  return $terms;
}


/**
 * Format custom column data for post_meta data
 */
function bw_custom_column_post_meta( $column, $post_id ) {
  $data = get_post_meta( $post_id, $column );
  if ( $data && is_array( $data) && count( $data ) ) {
    /* At least one value to format */
      bw_format_custom_column( $column, $data );
  } else {
    $data = bw_format_custom_column( $column );

  }
  return $data;
}

/**
 * Display a custom column for a post
 *
 * @param string $column - the name of the field being formatted
 * @param ID $post_id - the ID of the post being displayed  
 *
 * @uses get_post_meta to obtain the data to be displayed
 * @uses bw_format_custom_column to format it
 */
function bw_custom_column( $column, $post_id ) {
  $type = bw_query_field_type( $column );
  if ( $type === "taxonomy" ) {
    $value = bw_custom_column_taxonomy( $column, $post_id );
  } else { 
    $value = bw_custom_column_post_meta( $column, $post_id );
  }
  return $value;
}  

/** 
 * format a custom column on the admin page IF the column is defined in bw_fields
 *
 * @param string $column - the column name - e.g. _pp_url
 * @param string $data - the column's data value e.g. http://www.oik-plugins.com/oik-plugins/oik-fields
 * 
 (
    [0] => _cookie_category
    [1] => Array
        (
            [0] => 1
        )

    [2] => Array
        (
            [#field_type] => select
            [#title] => Cookie category
            [#args] => Array
                (
                    [#options] => Array
                        (
                            [0] => None
                            [1] => Strictly necessary
                            [2] => Performance
                            [3] => Functionality
                            [4] => Targeting/Advertising
                        )

                )

        )

)
*/
function bw_format_custom_column( $column=null, $data=null ) {
  // @TODO - this code can be replaced by bw_query_field_type(), perhaps
  $value = null;
  global $bw_fields; 
  $field = bw_array_get( $bw_fields, $column, null );
  if ( $field ) {
    $value = bw_theme_field( $column, $data, $field );
  }
  return $value;
} 
    


/**
 * Request plugins to load their field theming functions
 *
 * oik-fields is expected to respond to this action to load all the theming functions
 * not supported by the oik base plugin.
 *
 * 
 * The "oik_pre_theme_field" action is only called once.
 */
function bw_pre_theme_field() {
  static $bw_pre_theme_field = 0;
  if ( 0 === $bw_pre_theme_field ) { 
    do_action( "oik_pre_theme_field" );
  }
  $bw_pre_theme_field++;
}

/**
 * Themes a custom field  
 * 
 * @param string $key - field name e.g. _txn_amount
 * @param mixed $value - post metadata value
 * @param array $field - the field structure if defined using `bw_register_field()`
 */
function bw_theme_field( $key, $value, $field=null ) {
	$field_value = null;
  $type = bw_array_get( $field, "#field_type", null );
  //bw_trace2( $type, "Type", true, BW_TRACE_DEBUG );
	bw_pre_theme_field();
  // Try for a theming function named "bw_theme_field_$type_$key 
  $funcname = bw_funcname( "bw_theme_field_${type}", $key );
	
  // If there isn't a generic one for the type 
  // nor a specific one just try for the field
  if ( $funcname == "bw_theme_field_" && $type ) { 
    $funcname = bw_funcname( "bw_theme_field_", $key );
  }  
  
  if ( is_callable( $funcname ) ) {
    //bw_trace2( $funcname, "funcname chosen", false );
    $field_value = call_user_func( $funcname,  $key, $value, $field );
  } else {
    bw_trace2( $funcname, "funcname chosen not callable, using default _bw_theme_field_default", false );
    $field_value = _bw_theme_field_default( $key, $value, $field );
  }
  return $field_value;
} 

/**
 * Theme the "post_title" field
 *
 * @uses bw_theme_field__title
 */
function bw_theme_field__post_title( $key, $value, $field ) {
  bw_theme_field__title( $key, $value, $field );
}

/**
 * Themes the "post_date" field.
 *
 * Note: This doesn't set the label.
 * @param $key
 * @param $value
 * @param $field
 */
function bw_theme_field__post_date( $key, $value, $field ) {
	bw_theme_field__date( $key, $value, $field );
}

/**
 * Themes the "post_modified" field
 *
 * Note: This doesn't set the label.
 * @param $key
 * @param $value
 * @param $field
 */
function bw_theme_field__post_modified( $key, $value, $field ) {
	bw_theme_field__date( $key, $value, $field );
}

/**
 * Themes a date field.
 *
 * @param $key
 * @param $value
 * @param $field
 *
 */
function bw_theme_field__date( $key, $value, $field ) {
	span( $key );
	bw_theme_field_date( $key, $value, $field );
	epan();
}

/**
 * Theme a 'title' field
 * 
 * @param string $key - currently unused
 * @param string $value - the title text
 * @param array $field - should contain a key value pair of "post" => post object
 *
 */
function bw_theme_field__title( $key, $value, $field ) {
  //bw_trace2();
  $post = bw_array_get( $field, "post", null );
  if ( $post ) { 
    $link = get_permalink( $post->ID );
    BW_::alink( "title", $link, $value );
  } else {
    sepan( $key, $value );
  }  
}
 
/**
 * Theme the "excerpt" 
 *
 * @param string $key - the field name
 * @param string $value - the field value 
 * @param array $field - array of key value pairs
 */ 
function bw_theme_field__excerpt( $key, $value, $field ) {
  sepan( $key, $value );
}


/** 
 * Default theming of metadata based on field name ( $key ) or content? ( $value )
 * 
 * Rather than using an action to determine the function to invoke this routine looks for a function called _bw_theme_field_default_$key
 * If found it calls it passing the $key and $value else it performs some very basic default processing
 * 
 * @param string $key - the field name e.g. _type_field
 * @param string $value - the field value 
 *
 * Note: The '_' prefix to the meta data field name ensures the field is NOT displayed by the Custom fields meta box
 *
 */   
function _bw_theme_field_default( $key, $value ) {
  $funcname = "_bw_theme_field_default_" . $key;
  if ( function_exists( $funcname ) ) {
    $funcname( $key, $value );
  } else {
    // this could be a function called _bw_theme_field_default__unknown_key
    //span( "metadata $key" );
    //span( $key ); 
    //e( $key );
    //epan( $key );
    span( "value $key" );
    // e( $value );
    if ( !empty( $value ) ) {
      e( bw_array_get( $value, 0, $value ) );
    }  
    epan( "value" ); 
    //epan( "metadata $key" );
  }  
}

/** 
 * Default function to display a field of name "bw_header_image"
 *
 * @param string $key - field name
 * @param string $value - full file name of the image
 * 
 * A 'bw_header image' field contains the full file name of the image to be used as the header image
 * It's not exactly related to "custom header image" but could be.
 */
function _bw_theme_field_default_bw_header_image( $key, $value ) {
  e( retimage( $key, $value ) );
} 

/** 
 * Create a field label given the key name
 * 
 * Similar to bw_titleify()
 * 
 * @param string $key - the field name e.g. yoast_wpseo_title
 * @return string - the invented label e.g. Yoast Wpseo Title
 */
function bw_label_from_key( $key ) {
  $label = str_replace( "_", " ", $key );
  $label = trim( $label );
  $label = ucwords( $label );
  return( $label );
}

/**
 * Format the label for a field
 * 
 * @TODO Decide if this should really be e.g. <label for="_oikp_type">Plugin type</label> rather than <span>
 *
 * @param array $customfield - a simple array of $key => $value
 * @return bool - true if the label was required
 *
 */
function bw_format_label( $customfield ) {
  //bw_trace2();
  foreach ( $customfield as $key => $value ) {
    $label = bw_query_field_label( $key );
    if ( $label ) {
      span( "label $key" ); 
      e( $label );
      epan( $key );
    }  
  }
  return( $label );
}

/** 
 * Get the label for a field
 * 
 * 
 */
function bw_query_field_label( $key ) {
  global $bw_fields; 
  $label = null;
  $field = bw_array_get( $bw_fields, $key, null );
  if ( $field ) { 
    //bw_trace2( $field );
    //bw_theme_label( $key, $value, $field );
    $do_label = bw_array_get( $field['#args'], "#label", true );
    if ( $do_label ) {
      $label = bw_array_get( $field, "#title", $key );
    }
  } else {  
    $label = bw_label_from_key( $key );  
    $do_label = true;
  }
  //e("label:$label" );
  return( $label );
}
 

/**
 * Theme an array of custom fields
 * 
 * @param array $customfield - array of custom field data - an element from $bw_fields
 *
 */
function bw_format_field( $customfield ) {
  // bw_trace2();
  foreach ( $customfield as $key => $value ) {
    global $bw_fields; 
    $field = bw_array_get( $bw_fields, $key, null );
    span( "value" );
    bw_theme_field( $key, $value, $field );
    epan( "value" ); 
  }
}
} // end if ( !function_exists bw_custom_column

/**
 * Return the default separator between the field label and field value
 * @return string - separator - with spaces if required
 */
if ( !function_exists( "bw_default_sep" ) ) {
function bw_default_sep() {
  $sep = bw_get_option( "bw_separator", "bw_fields" );
  if ( !$sep ) {
    $sep = __( ": ", null );
  }  
  return( $sep);
} 
}

/**
 *
 */
if ( !function_exists( "bw_format_sep" ) ) {
function bw_format_sep( $type="field" ) {
  static $sep = null;
  if ( !$sep ) {
    $sep = bw_default_sep( $type );
  }
  span( "sep" );
  e( $sep );
  epan();
}
}

/**
 * Format the meta data for the 'post'
 * 
 * @param array $customfields - array of meta data for a post
 * The $customfield data may itself be an array. e.g. For multiple select noderef's 
 * Each field formatter should be able to cater for multiple values.
 * Most of them simply extract array[0] 
 *
 * @uses bw_format_field
 * @param array $customfields - array of fields to display
 */
if ( !function_exists( "bw_format_meta" ) ) {
function bw_format_meta( $customfields ) {
  //bw_backtrace();
  foreach  ( $customfields as $key => $customfield ) {
    $value = $customfield;
    // $value = bw_array_get( $customfield, 0, $customfield ); **?** is it OK to pass an array everywhere?   Herb 2013/10/24
    $cf = array( $key => $value );
    //bw_trace2( $cf, "cf" );
    sdiv( "bw_metadata $key" );
    $do_label = bw_format_label( $cf );
    if ( $do_label ) {
      bw_format_sep( $cf );
    }  
    bw_format_field( $cf );
    ediv( "metadata $key" );
  }  
}
}

/**
 * Format a taxonomy for the 'post'
 * 
 * @TODO - is this function required? Does the parameter to format_label need to be how it is now?
 */ 
if ( !function_exists( "bw_format_taxonomy" ) ) {
function bw_format_taxonomy( $field, $post_id ) {
  // We need to query the taxonomy name 
  sdiv( "bw_taxonomy $field" );
  bw_format_label( array( $field => $field ) );
  bw_format_sep( $field );
  bw_custom_column_taxonomy( $field, $post_id );
  ediv();
}  
}

/** 
 * If the field type is not defined then it's probably a post object's property
 */ 
if ( !function_exists( "bw_theme_object_property" ) ) {  
function bw_theme_object_property( $post_id, $value, $atts=null ) {
  $post = get_post( $post_id ); 
	if ( $post ) { 
			if ( property_exists( $post, $value ) ) {
			$field_value = $post->$value ;
			bw_theme_field( $value, $field_value, $atts ); 
		} elseif ( property_exists( $post, "post_$value" ) ) {       
			$field_name = "post_" . $value;  
		 $field_value = $post->$field_name;
			bw_theme_field( $field_name, $field_value, $atts );
		}
	}	 
}
}    

/**
 * Return the field data
 *
 * @param string $field
 * @return array field data
 */
function bw_get_field_data( $field ) {
  global $bw_fields;
  $data = bw_array_get( $bw_fields, $field, null );
  return( $data );
}

/**
 * Determines post type from hook
 *
 * - Quite a few hook names are constructed with the $post_type variable
 * - This function performs a simple extraction of the $post_type
 * - It should only be called when the current hook name is believed to be  $prefix . $post_type . $suffix
 *
 * @param string $hook the current hook
 * @param string $prefix="manage_"
 * @param string $suffix="_posts_custom_column"
 * @return string|null the post type determined from the hook
 */
function bw_determine_post_type_from_hook( $hook, $prefix="manage_", $suffix="_posts_custom_column" ) {
	$post_type = null;
	if ( 0 === strpos( $hook, $prefix ) ) {
		$filter = substr( $hook, strlen( $prefix ) );
		$suffix_start = strpos( $filter, $suffix );
		if ( $suffix_start ) {
			$post_type = substr( $filter, 0, $suffix_start );
		}
	}
	return $post_type;
}

/**
 * Returns true if the field name is registered for the object type
 * 
 * @param string $field_name field name
 * @param string $object_type object type name
 * @return bool true if the association has been registered
 */
function bw_field_registered_for_object_type( $field_name, $object_type ) {
	global $bw_mapping;
	$registered = isset( $bw_mapping['field'][$object_type][$field_name] );
  return $registered;
}

} /* end !defined */
