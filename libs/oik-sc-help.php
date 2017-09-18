<?php // (C) Copyright Bobbing Wide 2012-2017
if ( !defined( "OIK_SC_HELP_INCLUDED" ) ) {
define( "OIK_SC_HELP_INCLUDED", "3.2.0" );

/**
 * Shortcode help 
 * 
 * Library: oik-sc-help
 * Provides: oik-sc-help
 * Depends: on the oik plugin @TODO,	class-BW-
 *
 * Implements low level functions for displaying shortcode help.
 * This includes the help functions for some base shortcodes.
 * Parts of this library file is now deprecated in favour of "class-oik-sc-help"
 *
 */

//oik_require( "includes/oik-sc-help.inc" );

/**
 * Get first parm name
 *
 * Return the first name for the parameter. This is used to generate the link for the shortcode parameter.
 *
 * Some shortcodes have parameters which can have multiple keys for the basically the same content e.g. src,mp3,m4a,ogg... for [audio]
 * other shortcode accepts positional parameters - e.g. api|0 for the [api] shortcode
 * 
 * @param string $parameter
 * @return string - returned value
 */
function bw_form_sc_get_first_parm_name( $parameter ) {
  $parm = str_replace( array("|", ",") , " ", $parameter );
  $parms = explode(" ", $parm ); 
  return( $parms[0] );
}

/**
 * Return link to shortcode parameter help
 *
 * Links are of the form
 *
 * `http://oik-plugins.co.uk/oik_sc_param/$shortcode-$parameter-parameter/`
 *
 * where the URL is determined by oik_get_plugins_server()
 *
 * @param string $parameter	- may be a comma separated list of parameter name aliases e.g. "src,mp3,m4a,ogg,wav,wma"
 * @param string $shortcode	- e.g. "audio"
 * @return string the required link
 */
function bw_form_sc_parm_help( $parameter, $shortcode ) {
	$parm = $parameter;
	oik_require( "admin/oik-admin.inc" );
	$url = oik_get_plugins_server();
	$url .= "/oik_sc_param/$shortcode-$parm-parameter";
	$ret = retlink( null, $url, $parameter, "$shortcode $parameter parameter" );
	return( $ret );
}

/**
 * Format the shortcode syntax for returning in HTML
 *
 * @TODO - create links to the oik shortcode parameter help
 * for the specific shortcode and parameter name
 * 
 * 
 * @param array $syntax - array of key => shortcode key values
 * @param string $shortcode - the shortcode
 */
function bw_form_sc_syntax( $syntax, $shortcode ) {
  if ( count( $syntax ) ) { 
    foreach ( $syntax as $key => $value ) {
      br();
      span( "key" );
      //e( $key );
      e( bw_form_sc_parm_help( $key, $shortcode ) );
      epan();
      e( "=" );
      span( "value");
      e( '"' );     
      e( bw_format_skv( $value ));  
      e( '"' );
      epan();
    }  
  }
}

/**
 * Attempt to find the function that will handle the additional processing for the shortcode
 *
 * Some shortcodes contain hyphens rather than underscores. e.g. wp-members
 * We need to translate these to underscores before searching for the function. 
 * And some contain periods. e.g. blip.tv. These need translating too.
 * 
 * If the function does not exist we see if it's in the file that implements the shortcode.
 * If it's not then we return the default function name,
 * which is:
 * 
 * - _sc__help: for shortcode help
 * - _sc__example: for shortcode example
 * - _sc__syntax: for shortcode syntax 
 *   
 * @param string $shortcode - the name of the shortcode - NOT the shortcode function
 * @param string $suffix - the additional processing required: e.g. __help, __example, __syntax
 * @return string $funcname - the function that exists to handle this request
 */
function bw_load_shortcode_suffix( $shortcode, $suffix ) {
  $_shortcode = str_replace( "-", "_", $shortcode );
	$_shortcode = str_replace( ".", "_", $_shortcode );
  //  bw_trace2( $_shortcode, "underscored shortcode" );
  $testfunc = $_shortcode . $suffix; 
  if ( function_exists( $testfunc ) ) {
    $funcname = $testfunc;
  } else {
    $funcname = '_sc'. $suffix;
    if ( bw_load_shortcodefile( $shortcode ) && function_exists( $testfunc ) ) {
      $funcname = $testfunc; 
    }   
  }   
  return( $funcname );
}

/**
 * Display shortcode help
 *
 * @param string $shortcode - the shortcode e.g. bw_codes
 */   
function bw_lazy_sc_help( $shortcode ) {
  e( _bw_lazy_sc_help( $shortcode )) ;
}

/**
 * Return shortcode help
 *
 * @param string $shortcode - the shortcode e.g. bw_codes
 * @return string - the shortcode help
 */
function _bw_lazy_sc_help( $shortcode ) { 
  $funcname = bw_load_shortcode_suffix( $shortcode, "__help" ); 
  $help = $funcname( $shortcode );
  return( $help );
}

/**
 * Display a shortcode example
 * 
 * @param string $shortcode
 * @param array $atts - shortcode example parameters
 */
function bw_lazy_sc_example( $shortcode, $atts=null ) {
  $funcname = bw_load_shortcode_suffix( $shortcode, "__example" ); 
  $funcname( $shortcode, $atts ); 
}

/**
 * Return shortcode syntax
 *
 * @param string $shortcode
 * @return array - shortcode syntax as an associative array 
 */
function _bw_lazy_sc_syntax( $shortcode ) {
	$funcname = bw_load_shortcode_suffix( $shortcode, "__syntax" );
	$syntax = $funcname( $shortcode );
	//bw_trace2( $syntax, "Syntax for $funcname" ); 
	return $syntax ;
} 

/**
 * Display shortcode syntax
 *
 * @param string $shortcode - the shortcode name
 *
 */ 
function bw_lazy_sc_syntax( $shortcode, $callback=null ) {
  stag("code");
  e( '[' );
  bw_sc_link( $shortcode, $callback );
  $syntax = _bw_lazy_sc_syntax( $shortcode );
  bw_form_sc_syntax( $syntax, $shortcode );
  e( ']' );
  etag( "code" );
}

/**
 * Save original dependencies
 * 
 * We need to do the same for styles: wp_styles
 * We initialise $wp_scripts to ensure that $wp_scripts->registered is an array
 */
function bw_save_scripts() {
	$dependencies_cache = oik_require_lib( "class-dependencies-cache" );
	$dependencies_cache = dependencies_cache::instance();
	$dependencies_cache->save_dependencies();
}


/**
 * Report queued scripts
 * 
 * @TODO - decide how we're going to display the dependencies
 *  
 * 
 */ 
function bw_report_scripts( $verbose=true ) {
	$dependencies_cache = dependencies_cache::instance();
	$dependencies_cache->query_dependencies_changes();
	$dependencies = $dependencies_cache->serialize_dependencies();
	$serialized = serialize( $dependencies );
	//p( $serialized );
}

/** 
 * Produce the HTML snippet of the shortcode output
 *
 * @param string $shortcode - name of the shortcode
 * @param string $atts - shortcode parameters e.g class="bw_code"
 */
function _sc__snippet( $shortcode="bw_code", $atts=null ) {
  $example = "[$shortcode";
  if ( $atts ) 
    $example .= " $atts";
  $example .= ']';
  bw_save_scripts(); 
  bw_push();
  $formatted_example = apply_filters( 'the_content', $example ); 
  bw_pop();
  bw_trace2( $formatted_example, "formatted example" );
  $escaped_example = esc_html( $formatted_example );
  stag( 'p', null, null, 'lang="HTML" escaped="true"' );
  e( $escaped_example );
  etag( "p" );
  bw_report_scripts();
}

/**
 * Display the HTML snippet for a shortcode example
 *
 * @param string $shortcode - shortcode name
 * @param string $example - shortcode parameter string
 */
function bw_lazy_sc_snippet( $shortcode=null, $example=null ) {
	$saved = bw_global_post();
  $funcname = bw_load_shortcode_suffix( $shortcode, "__snippet" ); 
  bw_trace2( $funcname, "funcname" );
  $funcname( $shortcode, $example ); 
	bw_global_post( $saved );
}

/**
 * Returns the help for a shortcode
 * 
 * Returns the default help for a shortcode if not provided by the "shortcode__help" function
 *
 * @param string $shortcode
 * @return string translated shortcode help
 */
function _sc__help( $shortcode="bw_code" ) {
	$default_help = array();
  $default_help = apply_filters( "_sc__help", $default_help, $shortcode );
  $help = bw_array_get( $default_help, $shortcode, __( "?", null ) );
  return $help ;
}

/**
 * Return default syntax for this shortcode
 *
 * Originally this returned "oik doesn't know about this shortcode's syntax: ". $shortcode 
 * but this was changed to support the AJAX interface
 * 
 * It has now been extended to invoke filters which enable plugins to return the
 * syntax IF they don't support the $shortcode__syntax() dynamic hook method.
 * 
 * @param string $shortcode 
 * @return array $syntax array
 */ 
function _sc__syntax( $shortcode="bw_code" ) {
  $syntax = null;
  $syntax = apply_filters( "_sc__syntax", $syntax, $shortcode );
  return( $syntax );
}


/**
 * Produces default example for this shortcode
 * 
 * @param string $shortcode
 * @param array $atts
 */
function _sc__example( $shortcode="bw_code", $atts=null ) {
  //p( "oik doesn't know how to give an example for this shortcode: ". $shortcode );
  bw_invoke_shortcode( $shortcode, $atts, __( "oik generated example.", null ) );
}

/**
 * Helper functions for shortcodes that support these atts
 * 
 * @return array with "class" and "id" keys
 */
function _sc_classes() {
 return( array( "class"=> BW_::bw_skv( "", "<i>" . __( "class names", null ) . "</i>", __( "CSS class names", null ) )
              , "id" => BW_::bw_skv( "", "<i>" . __( "id", null ) . "</i>", __( "unique identifier", null ) )
              ));
              
}

 
/**
 * Helper function for shortcodes that use bw_get_posts() 
 *
 * Note: post_mime_type= can have sub values. eg.
 * - application/pdf
 * - image/gif
 * - image/jpeg
 * - image/png
 * - text/css
 * - video/mp4
 *
 * 'posts_per_page' added in oik v2.3
 *
 * @return array - associative array of bw_skv()'s  
 */
function _sc_posts() {
  return( array( 'numberposts'  => BW_::bw_skv( "5", __( "numeric", null ), __( "number to return", null ) )
               , 'offset'          => BW_::bw_skv( 0, __( "numeric", null ), __( "offset from which to start", null ) )
               , 'category'        => BW_::bw_skv( null, "<i>" . __( "category ID", null ) . "</i>", __( "numeric ID of the post category", null ) )
               , 'category_name'        => BW_::bw_skv( null, "<i>" . __( "category-slug", null ) . "</i>", __( "category slugs (comma separated)", null ) )
               , 'customcategoryname' => BW_::bw_skv( null, __( "category-slug", null ), __( "custom category slug", null ) )
               , 'orderby'         => BW_::bw_skv( 'date', "ID|title|parent|rand|menu_order", __( "Sort sequence", null ) )
               , 'order'           => BW_::bw_skv( 'DESC', "ASC", __( "Sort order.", null ) )
               , 'include'         => BW_::bw_skv( null, "<i>" . __( "id1,id2", null ) . "</i>", __( "IDs to include", null ) )
               , 'exclude'         => BW_::bw_skv( null, "<i>" . __( "id1,id2", null ) . "</i>", __( "IDs to exclude", null ) )
               , 'meta_key'        => BW_::bw_skv( null, __( "meta key", null ), __( "post metadata key", null ) )
               , 'meta_value'      => BW_::bw_skv( null, __( "meta value", null ), __( "post metadata value", null ) )
               , 'post_type'       => BW_::bw_skv( null, "page|post|attachment|" . __( "custom post type", null ), __( "Content type to display", null ) )
               , 'post_mime_type'  => BW_::bw_skv( null, "image|application|text|video|" . __( "mime type", null ), __( "Attached media MIME type", null ) )
               , 'post_parent'     => BW_::bw_skv( null, __( "ID" , null ) , __( "Parent ID to use if not current post", null ) )
               , 'post_status'     => BW_::bw_skv( null,  "publish|inherit|pending|draft|auto-draft|future|private|trash|any", __( "Post status", null ) )
               , 'id'              => BW_::bw_skv( null, "<i>" . __( "id1,id2", null ) . "</i>", __( "IDs of posts to display"  ) )
               , 'posts_per_page'  => BW_::bw_skv( null, __( "numeric", null ) . "|.", sprintf( __( 'Number of posts per page. Use \'.\' for current value %1$s', null ) , get_option( "posts_per_page", null ) ) )
               ));
} 

/**
 * Helper function for shortcodes that display images
 * 
 * @return array - associative array of bw_skv()'s
 */
function _sc_thumbnail() {   
  return( array( 'thumbnail'       => BW_::bw_skv( "thumbnail", "medium|large|full|" . __( "nnn", null ) . "|" . __( "wxh", null ), __( "image size", null ) )   
               ));
}        


function caption__help() {
  return( __( "Display the caption for an image. Standard WordPress shortcode", null ) );
}

function caption__example() {    
  BW_::br( __( "e.g:", null ) );
  // **?** The caption should surround an image - so we need to include an image in this
  $ics = img_caption_shortcode( array( 'width' => 70, 'caption' => __( 'This is a caption', null ) ) );
  e( $ics );
}

function wp_caption__help() {
  return( caption__help() );
}

function caption__syntax() {
  $syntax = array( 'id' => BW_::bw_skv( null, __( "text", null ), __( "value for CSS id= keyword", null ) )
                 , 'class' => BW_::bw_skv( null, __( "classname", null) , __( "custom class", null ) )
                 , 'align' => BW_::bw_skv( "alignnone", "aligncenter|alignright|alignleft", __( "CSS alignment class", null ) )
                 , 'width' => BW_::bw_skv( null, __( "numeric", null ), __( "width in pixels (Note: 10 is added to this number)", null ) )
                 );
  return( $syntax );               
}

function wp_caption__syntax() {
  return( caption__syntax() );
} 

function gallery__help() {
  return( __( "Display the attached images in a gallery", null ) );
}

/**
 * Syntax help for the gallery shortcode
 *
 * `
  $atts = shortcode_atts( array(
    'order'      => 'ASC',
    'orderby'    => 'menu_order ID', 
                 , "orderby" => bw_skv( "post__in", "<i>menu_order|ID|title|rand</i>", "Order by field" )
    'id'         => $post ? $post->ID : 0,
    'itemtag'    => $html5 ? 'figure'     : 'dl',
    'icontag'    => $html5 ? 'div'        : 'dt',
    'captiontag' => $html5 ? 'figcaption' : 'dd',
    'columns'    => 3,
    'size'       => 'thumbnail',
    'include'    => '',
    'exclude'    => '',
    'link'       => ''
 * `
 */
function gallery__syntax() {
	$syntax = array( 'order'   => BW_::bw_skv( 'ASC', "DESC", __( "Order", null ) )
		, 'orderby'    => BW_::bw_skv( 'menu_order ID', "ID|title|rand", __( "Order by field", null ) )
		, 'ids'         => BW_::bw_skv( "",  __( "ID" , null ), __( 'Post ID. Default: Current post ID', null ) )
		, 'itemtag'    => BW_::bw_skv( 'dl', "<i>" . __( "tag", null ) . "</i>", __( "Item tag", null ) )
		, 'icontag'    => BW_::bw_skv( 'dt', "<i>" . __( "tag", null ) . "</i>", __( "Icon tag", null ) )
		, 'captiontag' => BW_::bw_skv( 'dd', "<i>" . __( "tag", null ) . "</i>", __( "Caption tag", null ) )
		, 'columns'    => BW_::bw_skv( 3, __( "numeric", null ), __( "Columns", null ) )
		, 'size'       => BW_::bw_skv( 'thumbnail', "medium|full", __( "Thumbnail image size", null ) ) 
		, 'include'    => BW_::bw_skv( null, "<i>" . __( "id1,id2", null ) . "</i>", __( "IDs to include", null ) )
		, 'exclude'    => BW_::bw_skv( null, "<i>" . __( "id1,id2", null ) . "</i>", __( "IDs to exclude", null ) )
		);
	return $syntax ;
} 
 
function embed__help( $shortcode='embed' ) {
  return( "Embed media" );
}

function embed__example( $shortcode='embed' ) {
  p( "Embed example" );
}

function embed__syntax( $shortcode='embed' ) {
  $syntax = array( "width" => bw_skv( "width", "500|embed_size_w|content_width", "Required width" )
                 , "height" => bw_skv( "height", "700|embed_size_h", "Required height" )
                 );
  return( $syntax );
}

/**
 * Syntax for [audio] shortcode
 * @link http://codex.wordpress.org/Audio_Shortcode
 */
function audio__syntax( $shortcode='audio' ) {
  $syntax = array( "src,mp3,m4a,ogg,wav,wma" => bw_skv( null, "<i>source_url</i>", "If omitted uses first attachment" )
                 , "loop" => bw_skv( "off", "on", "Allow looping of media" )
                 , "autoplay" => bw_skv( "off", "on", "Causes media to autoplay" )
                 , "preload" => bw_skv( "none", "auto|metadata", "How the audio should be loaded" )
                 );
  return( $syntax ); 
}

/**
 * Syntax for [video] shortcode
 *
 * @link http://codex.wordpress.org/Video_Shortcode
 */
function video__syntax( $shortcode='video' ) {
  $syntax = array( "src,mp4,m4v,webm,ogv,wmv,flv" => bw_skv( null, "<i>source_url</i>", "If omitted uses first attachment" )
                 , "poster" => bw_skv( null, "<i>image_url</i>", "Placeholder image" )
                 , "loop" => bw_skv( "off", "on", "Allow looping of media" )
                 , "autoplay" => bw_skv( "off", "on", "Causes media to autoplay" )
                 , "preload" => bw_skv( "none", "auto|metadata", "How the audio should be loaded" )
                 , "height" => bw_skv( "height", "<i>integer</i>", "Required height" )
                 , "width" => bw_skv( "width", "<i>integer</i>", "Required width" )
                 );
  return( $syntax ); 
}

function wp__help() {
  return( "Display a styled form of WordPress. " );
}

function wp__example() {  
  oik_require( "shortcodes/oik-bob-bing-wide.php" );
  e( "e.g.". bw_wp());
}

function bw_code__help() {
  return( "Display the help and syntax for a shortcode" );
}

function bw_code__syntax() {
  $syntax = array( "shortcode" => bw_skv( "bw_code", "<i>shortcode</i>", "The shortcode you want explained" )  
                 , "help" => bw_skv( "Y", "N", "Display help for the shortcode" )
                 , "syntax" => bw_skv( "Y", "N", "Display the syntax" )
                 , "example" => bw_skv( "Y", "N", "Display an example, if possible" )
                 , "live" => bw_skv( "N", "Y", "Display the live results using default values" )
                 , "snippet" => bw_skv( "N", "Y", "Display the generated HTML" )
                 );
  return( $syntax );
}

function bw_code__example() {
  br();
  e( "e.g. [bw_code shortcode=\"oik\"]" );
  br();
  e( "Display information about the [oik] shortcode" );
  br();
  oik__help();
  br();
  bw_lazy_sc_syntax( "oik" );
  oik__example();
}

function bw_codes__syntax() {
  $syntax = array( "ordered" => bw_skv( "N", "Y", "Sort the shortcode codes by name." ) 
                 ); 
  return( $syntax );
}

/** 
 * Return the default, values and notes for a shortcode parameter
 *
 * - bw_skv is an abbreviation of bw_sc_key_values
 * - Here we call bw_translate() to help identify code that should be changed to use BW_::bw_skv()
 * 
 * @param string|null $default default value
 * @param string|null $values vertical bar separated possible values
 * @param string $notes translatable text
 * @return array
 */
function bw_skv( $default, $values, $notes ) {
  return( array( "default" => $default
               , "values" => $values
               , "notes" => bw_translate( $notes, "oik" )
               )  );
}

/**
 * Return the choices and notes for a keywords values
 *
 * @param mixed $value - array or string containing attribute values 
 * @return string - HTML showing the default value, other values and notes
 */
function bw_format_skv( $value ) {  
  if ( is_array( $value ) ) {    
    $default = bw_array_get( $value, "default", null );
    $values = bw_array_get( $value, "values", null );
    $values = str_replace( "|", "| ", $values );
    $notes = bw_array_get( $value, "notes", null );
    if ( is_array( $values ) ) { bw_trace2(); }
    if ( is_array( $notes ) ) {bw_trace2(); }
    
    return( "<b>$default</b>| $values - $notes" );
  } else {
    return( $value );
  }    
}

function bw_codes__help() {
  return( "Display the currently available shortcodes" );
}

function bw_codes__example() {
  e( "The currently available shortcodes are displayed in a table with a brief description, the known syntax and a link to further help. " );
  alink( null, "http://www.oik-plugins.com/oik-shortcodes/bw_codes/bw_codes", "[bw_codes] - list shortcodes" );
  
  // br( "Note: The default display lists the order in which the shortcodes are <i>evaluated</i>" );
  // e( "If you have a problem with hyphenated shortcodes not being chosen then it could be due to the order in which the shortcodes were registered using add_shortcode();" );
  // because the shortest shortcode has been added before the longer ones. 
  // See wp-1, wp-2, wp and wp-3 ");
}

/**
 * Common parameters for bw_email, bw_mailto, etc. 
 */ 
function _sc_email() {
  $syntax = array( "prefix" => bw_skv( "Email", "<i>string</i>", "Prefix string" )
                 , "sep" => bw_skv( ": ", "<i>string</i>", "Separator string" )
                 , "alt" => bw_skv( null, "1", "Use alternative value" )
                 , "title" => bw_skv( "Send email to: ", "<i>title string</i>", "Tool tip text" )
                 );
  return( $syntax );
}                   

function bw_email__syntax( $shortcode="bw_email" ) {
  $syntax = _sc_email();
  $syntax_email = array( "tag" => bw_skv( "span", "<i>tag</i>", "enclosing HTML tag" )
                 , "index" => bw_skv( "email", "<i>field</i>", "oik options field to use" )
                 );
  $syntax += $syntax_email;                 
  return( $syntax );
}

function bw_mailto__syntax( $shortcode="bw_mailto" ) {
  $syntax = _sc_email();
  $syntax_mailto = array( "tag" => bw_skv( "p", "<i>tag</i>", "enclosing HTML tag" )
                 , "index" => bw_skv( "email", "<i>field</i>", "oik options field to use" )
                 );
  $syntax += $syntax_mailto;                
  return( $syntax );
}

function _sc_telephone() {
  $syntax = array( "sep" => bw_skv( ": ", "<i>string</i>", "Separator string" )
                 , "alt" => bw_skv( null, "1", "Use alternative value" )
                 , "number" => bw_skv( null, "<i>number</i>", "Number override" )  
                 , "tag" => bw_skv( "div", "<i>tag</i>", "enclosing HTML tag" )
                 , "class" => bw_skv( "", "<i>class names</i>", "CSS class names" )
                 , "link" => bw_skv( null, "n|y|t|s|<i>other</i>:", "tel: or sms: link" )
                 );
  return( $syntax );
}

function bw_telephone__syntax( $shortcode="bw_telephone" ) {
  $syntax = _sc_telephone();
  $syntax_telephone = array( "prefix" => bw_skv( "Tel", "<i>string</i>", "Prefix string" ) );
  $syntax_after = array( "index" => bw_skv( "telephone", "<i>field</i>", "oik options field to use" ) );
  $syntax_telephone += $syntax;
  $syntax_telephone += $syntax_after; 
  return( $syntax_telephone );
} 

function bw_tel__syntax( $shortcode="bw_tel" ) {
  $syntax = bw_telephone__syntax();
  $syntax['tag'] = bw_skv( "span", "<i>tag</i>", "enclosing HTML tag" );
  return( $syntax );
}

function bw_fax__syntax( $shortcode="bw_fax" ) {
  $syntax = _sc_telephone();
  $syntax_fax = array( "prefix" => bw_skv( "Fax", "<i>string</i>", "Prefix string" ) );
  $syntax_after = array( "index" => bw_skv( "fax", "<i>field</i>", "oik options field to use" ) );
  $syntax_fax += $syntax;
  $syntax_fax += $syntax_after; 
  return( $syntax_fax );
}

function bw_mobile__syntax( $shortcode="bw_mobile" ) {
  $syntax = _sc_telephone();
  $syntax_mobile = array( "prefix" => bw_skv( "Mobile", "<i>string</i>", "Prefix string" ) );
  $syntax_after = array( "index" => bw_skv( "mobile", "<i>field</i>", "oik options field to use" ) );
  $syntax_mobile += $syntax;
  $syntax_mobile += $syntax_after; 
  return( $syntax_mobile );
}

function bw_mob__syntax( $shortcode="bw_mob" ) {
  $syntax = bw_mobile__syntax();
  $syntax['tag'] = bw_skv( "span", "<i>tag</i>", "enclosing HTML tag" );
  return( $syntax );
}

function bw_emergency__syntax( $shortcode="bw_emergency" ) {
  $syntax = _sc_telephone();
  $syntax_emergency = array( "prefix" => bw_skv( "Emergency", "<i>string</i>", "Prefix string" ) );
  $syntax_after = array( "index" => bw_skv( "emergency", "<i>field</i>", "oik options field to use" ) );
  $syntax_emergency += $syntax;
  $syntax_emergency += $syntax_after; 
  $syntax_emergency['class'] = bw_skv( "bw_emergency", "<i>class names</i>", "additional CSS class names" ); 
  return( $syntax_emergency );
}

function bw_skype__syntax( $shortcode="bw_skype" ) {
  $syntax = _sc_telephone();
  $syntax_skype = array( "prefix" => bw_skv( "Skype name", "<i>string</i>", "Prefix string" ) );
  $syntax_after = array( "index" => bw_skv( "skype", "<i>field</i>", "oik options field to use" ) );
  $syntax_skype += $syntax;
  $syntax_skype += $syntax_after; 
  $syntax_skype['class'] = bw_skv( "bw_skype", "<i>class names</i>", "additional CSS class names" ); 
  return( $syntax_skype );
}

function bw_copyright__syntax( $shortcode="bw_copyright" ) {
  $syntax = array( "prefix" => bw_skv( "&copy; Copyright", "<i>string</i>", "Copyright text prefix" )
                 , "company" => bw_skv( bw_get_option( "company" ), "<i>company name</i>", "from oik options - company" )
                 , "suffix" => bw_skv( ". All rights reserved.", "<i>string</i>", "copyright suffix text" )
                 , "from" => bw_skv( bw_get_option( "yearfrom" ), "<i>year</i>", "from oik options - yearfrom" )
                 , "sep" => bw_skv( "-", ",|<i>string</i>", "default: ',' if one year difference '-' otherwise" )
                 );
  return( $syntax );
} 

function bw_qrcode__syntax( $shortcode="bw_qrcode" ) {
  $syntax = array( "link" => bw_skv( null, "<i>URL</i>", "Link URL for QR code image" )
                 , "text" => bw_skv( bw_get_option( "company" ), "<i>string</i>", "from oik options - company" )
                 , "width" => bw_skv( null, "<i>width</i>", "width of QR code image, if required" )
                 , "height" => bw_skv( null, "<i>height</i>", "height of QR code image, if required" )
                 );
  return( $syntax );
}
function bw_contact__syntax( $shortcode="bw_contact" ) {
  $syntax = array( "alt" => bw_skv( null, "1", "Use alternative value" ) );
  return( $syntax );
}
  
function bw_directions__syntax( $shortcode="bw_directions" ) {
  $syntax = array( "alt" => bw_skv( null, "1", "Use alternative value" ) );
  return( $syntax );
}
  
function bw_geo__syntax( $shortcode="bw_geo" ) {
  $syntax = array( "alt" => bw_skv( null, "1", "Use alternative value" ) );
  return( $syntax );
}
                  
/**
 * Call a shortcode for a simple example
 * 
 * @param string $shortcode - name of shortcode
 * @param string $atts - attribute parameters to the shortcode
 * @param string $text - translated brief description of the shortcode
 *
 * @uses apply_filters() rather than do_shortcode() since the shortcodes that get invoked
 * may not support the current_filter() - which on an admin page could be oik-options_page_oik_options-1
 * Nearly ALL shortcodes support 'the_content' so we will apply that filter
 */
function bw_invoke_shortcode( $shortcode, $atts=null, $text=null ) {
  BW_::p( $text );
  $example = "[$shortcode";
  if ($atts ) 
    $example .= " $atts";
  $example .= ']';
  sp();
  stag( "code" );
  e( $example ); 
  etag( "code" ); 
  ep();
  //p( $example );
	$saved = bw_global_post();
  bw_push();
  $expanded = apply_filters( 'the_content', $example );
	bw_pop();
	bw_global_post( $saved );
  e( $expanded );
  bw_trace2( $expanded, "expanded", true, BW_TRACE_DEBUG );
  //bw_backtrace();
}

/**
 * Syntax for [ad] shortcode - Artisteer themes
 */  
function ad__syntax( $shortcode='ad' ) {
  $syntax = array( "code" => bw_skv( 1, "2|3|4|5", "Advertisement selection - Artisteer theme options" )
                 , "align" => bw_skv( "left", "center|right", "Alignment" )
                 , "inline" => bw_skv( 0, "1", "0 if inline, 1 for block" )
                 );
  return( $syntax );
}

/**
 * Syntax for [post_link] shortcode 
 * 
 * If the name= parameter starts with "/Blog%20Posts/" then the post name is considered to be a post, otherwise it's a page
 * A bit of a crappy shortcode if you ask me Herb 2014/05/10
 *
 */
function post_link__syntax( $shortcode='post_link' ) {
  $syntax = array( "name" => bw_skv( "/", "<i>page_name</i>", "Page to link to" ) );
  return( $syntax  );
}

/**
 * Syntax for [collage] shortcode
 */
function collage__syntax( $shortcode="collage" ) {
  $syntax = array( "id" => bw_skv( null, "<i>collage ID</i>", "Index of the theme_collages post meta to display" ) );
  return( $syntax  );
}

/**
 * Syntax for [playlist] shortcode
 */
function playlist__syntax( $shortcode="playlist" ) {
  $syntax = array( "ids" => bw_skv( null, "<i>id1,id2,</i", "IDs for the playlist" )
                 , "id" => bw_skv( "current post", "<i>id</i>", "Post from which the playlist is chosen" )
                 , "type" => bw_skv( "audio", "video", "Content type" )
                 , "order" => bw_skv( "ASC", "DESC|RAND", "Ordering" )
                 , "orderby" => bw_skv( "post__in", "<i>menu_order|ID|title|rand</i>", "Order by field" )
                 , "include" => bw_skv( null, "<i>id1,id2</i>", "IDs to include" )
                 , "exclude" => bw_skv( null, "<i>id1,id2</i>", "IDs to exclude" )
                 , "style" => bw_skv( "light", "dark", "Playlist style" )
                 , "tracklist" => bw_skv( "true", "false", "Display track list?" )
                 , "tracknumbers" => bw_skv( "true", "false", "Display track numbers?" )
                 , "images" => bw_skv( "true", "false", "Display images?" )
                 , "artists" => bw_skv( "true", "false", "Display artists?" )
                 );
  return( $syntax );
}                


} /* end !defined */

