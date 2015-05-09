<?php

/**
 * Admin ajax functions to be tested
 */
require_once( ABSPATH . 'wp-admin/includes/ajax-actions.php' );

class ValidatedAjax extends WP_Ajax_UnitTestCase {

	/**
	 * JSON encoded mock HTTP response.
	 * @var string 
	 */
	var $mock_data_good	 = '{"headers":{"date":"Fri, 08 May 2015 20:59:06 GMT","server":"Apache\/2.2.22 (Debian)","content-language":"en","x-w3c-validator-recursion":"1","x-w3c-validator-status":"Invalid","x-w3c-validator-errors":"1","x-w3c-validator-warnings":"2","vary":"Accept-Encoding","content-encoding":"gzip","content-length":"8560","connection":"close","content-type":"text\/html; charset=UTF-8"},"body":"","response":{"code":200,"message":"OK"},"cookies":[],"filename":null}';
	var $mock_data_bad	 = '{"headers":{"date":"Fri, 08 May 2015 21:03:47 GMT","server":"Apache\/2.2.22 (Debian)","content-language":"en","x-w3c-validator-recursion":"1","x-w3c-validator-status":"Abort","connection":"close","content-type":"text\/html; charset=UTF-8"},"body":"","response":{"code":200,"message":"OK"},"cookies":[],"filename":null}';
	var $mock_data_valid	 = '{"headers":{"date":"Fri, 08 May 2015 21:03:47 GMT","server":"Apache\/2.2.22 (Debian)","content-language":"en","x-w3c-validator-recursion":"1","x-w3c-validator-status":"Valid","connection":"close","content-type":"text\/html; charset=UTF-8"},"body":"","response":{"code":200,"message":"OK"},"cookies":[],"filename":null}';

	
	/**
	 * Post ID
	 * @var int 
	 */
	var $pid;

	/**
	 * Post ID of a draft.
	 * @var int 
	 */
	var $draft_id;

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

		$this->draft_id = $this->factory->post->create( array(
			'post_type'		 => 'page',
			'post_title'	 => 'Test Draft Post',
			'post_content'	 => 'Some text',
			'post_status'	 => 'draft'
		) );

		// Become an administrator
		$this->_setRole( 'administrator' );

		// Set up a default request
		$_POST[ 'security' ] = wp_create_nonce( 'validated_security' );
		$_POST[ 'action' ]	 = 'validated';
	}

	function tearDown() {
		wp_delete_post( $this->pid );
		parent::tearDown();
	}

	/**
	 * Hijack HTTP request with mock data.
	 * @param bool|array $return
	 * @param array $request
	 * @param string $url
	 * @return array
	 */
	function mock_data( $return = false, $request = array(), $url = '' ) {

		if ( !stristr( $url, 'example.org' ) ) {
			return false;
		}
		remove_filter( 'pre_http_request', array( $this, 'mock_data' ) );
		return json_decode( $this->mock_data_good, true );
	}

	/**
	 * Hijack HTTP request with mock data.
	 * @param bool|array $return
	 * @param array $request
	 * @param string $url
	 * @return array
	 */
	function mock_data_bad( $return = false, $request = array(), $url = '' ) {

		if ( !stristr( $url, 'example.org' ) ) {
			return false;
		}
		remove_filter( 'pre_http_request', array( $this, 'mock_data_bad' ) );
		return json_decode( $this->mock_data_bad, true );
	}
	
		/**
	 * Hijack HTTP request with mock data.
	 * @param bool|array $return
	 * @param array $request
	 * @param string $url
	 * @return array
	 */
	function mock_data_good( $return = false, $request = array(), $url = '' ) {

		if ( !stristr( $url, 'example.org' ) ) {
			return false;
		}
		remove_filter( 'pre_http_request', array( $this, 'mock_data_good' ) );
		return json_decode( $this->mock_data_valid, true );
	}

	function test_ajax_wo_post_id() {
		add_filter( 'pre_http_request', array( $this, 'mock_data' ), 1, 3 ); // Hijack HTTP requests for unit tests.

		try {
			$this->_handleAjax( 'validated' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$response = json_decode( $this->_last_response );

		$this->assertFalse( $response->success );
	}

	function test_ajax_w_string_post_id() {
		add_filter( 'pre_http_request', array( $this, 'mock_data' ), 1, 3 ); // Hijack HTTP requests for unit tests.

		$_POST[ 'post_id' ] = 'My jumbled string';
		try {
			$this->_handleAjax( 'validated' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$response = json_decode( $this->_last_response );

		$this->assertFalse( $response->success );
	}

	/**
	 * If a post is a draft, it shouldn't be checked.
	 */
	function test_ajax_w_draft() {
		add_filter( 'pre_http_request', array( $this, 'mock_data' ), 1, 3 ); // Hijack HTTP requests for unit tests.

		$_POST[ 'post_id' ] = $this->draft_id;
		try {
			$this->_handleAjax( 'validated' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$response = json_decode( $this->_last_response );

		$this->assertFalse( $response->success );
	}

	/**
	 * Test AJAX call with public-facing URL. (Good mock data HTTP response)
	 */
	function test_ajax() {
		add_filter( 'pre_http_request', array( $this, 'mock_data' ), 1, 3 ); // Hijack HTTP requests for unit tests.

		$_POST[ 'post_id' ] = $this->pid;

		// Make the request
		try {
			$this->_handleAjax( 'validated' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$response = json_decode( $this->_last_response );

		$this->assertTrue( $response->success );
		$this->assertFalse( $response->data->report );
	}

	/**
	 * Testing response from non-public URL.
	 */
	function test_ajax_w_bad_url() {
		add_filter( 'pre_http_request', array( $this, 'mock_data_bad' ), 1, 3 ); // Hijack HTTP requests for unit tests.

		$_POST[ 'post_id' ] = $this->pid;

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
	 * Test AJAX call with non-public URL / local development. (Good mock data HTTP response)
	 */
	function test_ajax_local() {
		add_filter( 'pre_http_request', array( $this, 'mock_data' ), 1, 3 ); // Hijack HTTP requests for unit tests.

		$_POST[ 'post_id' ] = $this->pid;
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

		$response	 = json_decode( $this->_last_response );
		$this->assertTrue( $response->success );

	}
	


}
