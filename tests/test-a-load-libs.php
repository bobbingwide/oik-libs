<?php

/**
 * @package oik-libs
 * @copyright (C) Copyright Bobbing Wide 2023
 *
 * Unit tests to load all the shared library files for PHP 8.2
 */

class Tests_load_libs extends BW_UnitTestCase
{

    /**
     * set up logic
     *
     * - ensure any database updates are rolled back
     * - we need oik-googlemap to load the functions we're testing
     */
    function setUp(): void
    {
        parent::setUp();

    }

    function test_load_libs() {

        $files = glob( 'libs/*.php');
        //print_r( $files );
        foreach ( $files as $file ) {
            oik_require( $file, 'oik-libs');
        }
        $this->assertTrue( true );

    }

}