<?php // (C) Copyright Bobbing Wide 2017-2019

/**
 * @package libs-class-oik-remote
 * 
 * Tests for logic libs/class-oik-remote.php
 */
class Tests_libs_class_oik_remote extends BW_UnitTestCase {



	function setUp() {
		oik_require_lib( "class-oik-remote" ); 
	}

	function get_computername() {
		$computername = getenv( "COMPUTERNAME");
		$computername = strtolower( $computername );
		return $computername;

	}

	function test_computername() {
		$computername = $this->get_computername();
		$this->assertEquals( "sb", $computername );
	}
	
	/**
	 * Tests this machine is called QW or SB
	 */
	function test_php_uname() {
		$uname = php_uname();
		$uname = str_replace( " SB ", " QW ", $uname );
		$this->assertContains( " QW ", $uname );
		//echo $uname;

	}
	
	/**
	 * We want to check that "qw" is the current machine? 
	 * 
	 * gethostbyname() returns the IP address associated with a particular host name.
	 * If you append a '.' to the domain name then you can get a different result.
	 * 
	 * On my local network, with a Windows machine called qw ( COMPUTERNAME=QW	) even though there is an entry in the hosts file for qw,
	 * the returned IP address is not the same as that for localhost. See test_gethostbynamel()
	 * 	 
	 */
	function test_gethostbyname() {
		$ip_localhost = gethostbyname( "localhost" );
		$this->assertEquals( "127.0.0.1", $ip_localhost );
		$ip_localhost = gethostbyname( "localhost." );
		$this->assertEquals( "127.0.0.1", $ip_localhost );

		$computername = $this->get_computername();
		$ip_qw        = gethostbyname( $computername );
		if ( $computername == "qw" ) {
			$this->assertEquals( "192.168.50.1", $ip_qw, "qw failed" );
			$ip_qw = gethostbyname( "qw." );
			$this->assertEquals( "192.168.50.1", $ip_qw, "qw. failed" );
		}
		if ( $computername == "sb" ) {
			$ip_qw = gethostbyname( "s.b" );
			$this->assertEquals( "127.0.0.1", $ip_qw, "s.b failed" );
			$ip_qw = gethostbyname( "s.b." );
			$this->assertEquals( "127.0.0.1", $ip_qw, "s.b. failed" );
		}
	}
	
	/**
	 * Tests gethostbynamel()
	 * 
	 * gethostbynamel() actually returns 3 addresses for my local computer name QW.
	 * These can be mapped to results from ipconfig 
	 * 
	 * gethostbynamel()      | ipconfig 
	 * -----------------     | ----------
   * [0] => 192.168.50.1	 | Ethernet adapter VirtualBox Host-Only Network #2:
   * [1] => 192.168.56.1	 | Ethernet adapter VirtualBox Host-Only Network:
   * [2] => 192.168.1.10	 | Wireless LAN adapter WiFi:
	 *
	 * @TODO These IP addresses may change after a reboot. Cater for this somehow.
	 */
	function test_gethostbynamel() {
		
		$hosts = gethostbynamel( "localhost" );
		$expected = array( "127.0.0.1" );
		$this->assertEquals( $expected, $hosts );
		
		$hosts = gethostbynamel( "localhost." );
		$expected = array( "127.0.0.1" );
		$this->assertEquals( $expected, $hosts );
		
		if ( oik_remote::get_computer_name() == "qw" ) {
			$hosts = gethostbynamel( "qw" );
			$expected = array( "192.168.50.1", "192.168.56.1", "192.168.1.16" );
			$this->assertEquals( $expected, $hosts );
			$hosts = gethostbynamel( "qw." );
			$this->assertEquals( $expected, $hosts );
			$hosts = gethostbynamel( "q.w" );
			$expected = array( "127.0.0.1" );
			$this->assertEquals( $expected, $hosts );
			$hosts = gethostbynamel( "q.w." );
			$this->assertEquals( $expected, $hosts );
		}
	}
	
	/** 
	 * This test will fail if the local machine is not called 'qw'
	 */
	function test_get_computer_name() {
		$computername = $this->get_computername();
		$this->assertEquals( $computername, oik_remote::get_computer_name() );
	}
	
	/**
	 * Tests are_you_local_ip
	 */
	function test_are_you_local_ip() {
		$local = oik_remote::are_you_local_ip( "https://localhost" );
		$this->assertTrue( $local );
		$local = oik_remote::are_you_local_ip( "http://localhost" );
		$this->assertTrue( $local );
		if ( $this->get_computername() == "sb") {
			$local = oik_remote::are_you_local_ip( "https://s.b" );
			$this->assertTrue( $local );
		}
		
	}
	
	/**
	 * Tests are_you_local_ip for qw 
	 */
	function test_are_you_local_ip_qw() {
		
		$local = oik_remote::are_you_local_ip( "http://qw" );
		if ( $_SERVER['SERVER_NAME'] == 'qw' ) {
			$this->assertTrue( $local, "qw is supposed to be local when it matches the SERVER_NAME ." );
		} else {
			$this->assertFalse( $local, "qw is not local when it doesn't match the SERVER_NAME" );
		}
	}
	
	
	/**
	 * Tests if this is a private IP
	 * 
	 * URL | Expected IP | Private?
	 * ----- | --------- | --------
	 * localhost | 127.0.0.1 | No
	 * q.w | 127.0.0.1 | No
	 * qw | 192.168.x.x | Yes
	 * 
	 */
	function test_are_you_private_ip() {
		$local = oik_remote::are_you_private_ip( "https://localhost" );
		$this->assertFalse( $local ); 
		
		$local = oik_remote::are_you_private_ip( "https://qw" );
		$this->assertTrue( $local ); 
		$local = oik_remote::are_you_private_ip( "https://q.w" );
		$this->assertFalse( $local ); 
	} 
	
	
	/**
	 * These tests will pass if the local machine is called qw
	 * and there is a host name of q.w
	 */
	function test_are_you_local() {
		if ( $this->get_computername() == "sb" ) {
			$local = oik_remote::are_you_local( "https://sb" );
			$this->assertTrue( $local, "sb is not local" );
			$local = oik_remote::are_you_local( "https://s.b" );
			$this->assertTrue( $local, "s.b is not local" );
		}
		$local = oik_remote::are_you_local( "https://localhost" );
		$this->assertTrue( $local, "localhost is not local" );
		
	}
	
	/**
	 * Simulate tests with multisite where the $_SERVER['SERVER_NAME'] is not localhost.
	 * 
	 */	
	function test_simulate_multisite() {
		$saved =  $_SERVER['SERVER_NAME'];
		$_SERVER['SERVER_NAME'] = "qw";
		$this->test_are_you_local_ip();
		$this->test_are_you_local_ip_qw();
		$this->test_are_you_private_ip();
    $this->test_are_you_local();
		$_SERVER['SERVER_NAME'] = $saved;
	}

	/**
	 * Tests [github bobbingwide oik issues 126]
	 */
	function test_are_you_local_ip_when_server_name_not_set() {
		$saved = $_SERVER['SERVER_NAME'];
		unset( $_SERVER[ 'SERVER_NAME'] );
		$local = oik_remote::are_you_local_ip( "https://localhost" );
		$this->assertFalse( $local );
		$_SERVER['SERVER_NAME'] = $saved;
	}
	
	
	
	
		
		

}		
