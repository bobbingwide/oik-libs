<?php // (C) Copyright Bobbing Wide 2017

/**
 * @package libs-class-oik-remote
 * 
 * Tests for logic libs/class-oik-remote.php
 */
class Tests_libs_class_oik_remote extends BW_UnitTestCase {

	function setUp() {
		oik_require_lib( "class-oik-remote" ); 
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
		$ip_qw = gethostbyname( "qw" );
		$this->assertEquals( "192.168.50.1", $ip_qw, "qw failed" );
		$ip_qw = gethostbyname( "qw." );
		$this->assertEquals( "192.168.50.1", $ip_qw, "qw. failed" );
		$ip_qw = gethostbyname( "q.w" );
		$this->assertEquals( "127.0.0.1", $ip_qw, "q.w failed" );
		$ip_qw = gethostbyname( "q.w." );
		$this->assertEquals( "127.0.0.1", $ip_qw, "q.w. failed" );
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
			$expected = array( "192.168.50.1", "192.168.56.1", "192.168.1.10" );
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
		$this->assertEquals( "qw", oik_remote::get_computer_name() );
	}
	
	/**
	 * These tests will pass if the local machine is called qw
	 * and there is a host name of q.w
	 */
	function test_are_you_local() { 
		$local = oik_remote::are_you_local( "https://qw" );
		$this->assertTrue( $local, "qw is not local" );
		$local = oik_remote::are_you_local( "https://q.w" );
		$this->assertTrue( $local, "q.w is not local" );
		$local = oik_remote::are_you_local( "https://localhost" );
		$this->assertTrue( $local, "localhost is not local" );
	}
	
	function test_are_you_local_ip() {
		$local = oik_remote::are_you_local_ip( "https://localhost" );
		$this->assertTrue( $local );
		$local = oik_remote::are_you_local_ip( "http://localhost" );
		$this->assertTrue( $local );
		$local = oik_remote::are_you_local_ip( "http://q.w" );
		$this->assertTrue( $local );
		
		$local = oik_remote::are_you_local_ip( "http://qw" );
		$this->assertFalse( $local, "qw is not local" );
	}
	
	function test_php_uname() {
		$uname = php_uname();
		$this->assertContains( " QW ", $uname );
	}
		
		

}		
