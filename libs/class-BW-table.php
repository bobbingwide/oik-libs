<?php 

/**
 * @copyright (C) Bobbing Wide 2018
 * @package oik-block
 */
 
class BW_table {

	static $format = 'html';
	static $cols;
	
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
	
	public function __construct() {
    BW_table::$format = 'html';
	}
	
	static function set_format( $format ) {
		switch ( $format ) {
			case 'html':
			case 'csv':
			case 'md':
			case 'cli':
				BW_table::$format = $format;
				break;
			default:
				BW_table::$format = 'html';
		}
	}
	
	/**
	 * Outputs a table row
	 */
	static function row( $row ) {
		switch ( BW_table::$format ) {		
			case 'html':
				bw_tablerow( $row );
				break;
			default:
				$line = implode( ' | ', $row );
				echo $line . PHP_EOL;
			
		}
	}
	
	/**
	 * Output the header for the table
	 */
	static function header( $row ) {
		switch ( BW_table::$format ) {
			case 'html':
			
				stag( "table" );
				BW_table::set_cols( $row );
				BW_table_header( $row );
				stag( "tbody" );
				break;
				
			default:
			
				BW_table::row( $row );
				BW_table::set_cols( $row );
				BW_table::row( BW_table::$cols );
				///
		}
		
	}
	
	/**
	 * Sets the "cols" based on the table header
	 * 
	 *  
	 */
	static function set_cols( $row ) {
		BW_table::$cols = array();
		foreach ( $row as $col ) {
			BW_table::$cols[] = str_repeat( '-', strlen( $col ) );
		}
	}
	
	/**
	 * Outputs the footer for the table 
	 */
	static function footer() {
		switch ( BW_table::$format ) {	
			case 'html':
				etag( 'tbody' );
				etag( 'table' );
				break;
				
			case 'csv':
			case 'md':
				break;
				
			case 'cli':
				echo implode( "+", BW_table::$cols ) . PHP_EOL;
		}
	}


}
