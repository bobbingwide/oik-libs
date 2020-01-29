<?php // (C) Copyright Bobbing Wide 2017

/**
 * @package libs-class-oik-plugin-update.php
 * 
 * Tests for logic libs/class-oik-plugin-update.php
 */
class Tests_libs_class_oik_plugin_update extends BW_UnitTestCase {

	function setup(): void {
		oik_require_lib( "class-oik-plugin-update" ); 
	}
	
	/**
	 * 
	 */
	function test_query_menu_does_not_issue_warning() {
		$oik_update = OIK_Plugin_Update::instance();
		unset( $GLOBALS['submenu'] );
		$exists = $oik_update->query_menu( "oik-issue-79" );
		$this->assertFalse( $exists );
	}
		

}		
