<?php

/**
 * Admin ajax functions to be tested
 */
require_once( ABSPATH . 'wp-admin/includes/ajax-actions.php' );

class ValidatedAjax extends WP_Ajax_UnitTestCase {

	/**
	 * Post ID
	 * @var int 
	 */
	var $pid;

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		parent::setUp();
		$this->pid = $this->factory->post->create( array(
			'post_type'		 => 'page',
			'post_title'	 => 'Test Post',
			'post_content'	 => 'Some text'
		) );

		// Become an administrator
		$this->_setRole( 'administrator' );

		// Set up a default request
		$_POST[ 'security' ] = wp_create_nonce( 'validated_security' );
		$_POST[ 'action' ]	 = 'validated';
		$_POST[ 'post_id' ]	 = $this->pid;
	}

	function tearDown() {
		wp_delete_post( $this->pid );
		parent::tearDown();
	}

	function switch_url( $url ) {
		remove_filter( 'post_link', array( $this, 'switch_url' ) );
		return 'http://www.google.com/';
	}

	/**
	 * Test out validating http://www.google.com as a public URL.
	 */
	function test_ajax() {
		add_filter( 'post_link', array( $this, 'switch_url' ) );

		// Make the request
		try {
			$this->_handleAjax( 'validated' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}
		$response = json_decode( $this->_last_response );

		$this->assertTrue( $response->success );
	}

	/**
	 * Test out validating http://www.google.com/ as a private URL.
	 */
	function test_ajax_local() {
		add_filter( 'post_link', array( $this, 'switch_url' ) );
		// Activate Local Dev Testing
		if ( !defined( 'VALIDATED_LOCAL' ) ) {
			define( 'VALIDATED_LOCAL', true );
		}

		// Make the request
		try {
			$this->_handleAjax( 'validated' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$response = json_decode( $this->_last_response );
		$this->assertTrue( $response->success );
	}

}
