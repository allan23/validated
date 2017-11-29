<?php
/**
 * Unit tests for AJAX.
 *
 * @package validated
 */

/**
 * Unit tests class for AJAX-related tests.
 *
 * @group ajax
 */
class ValidatedAjaxTests extends WP_Ajax_UnitTestCase {

	/**
	 * Mock data results.
	 *
	 * @var string
	 */
	public $mock_invalid = '{"url":"http://www.example.org/","messages":[{"type":"info","message":"The Content-Type was “text/html”. Using the HTML parser."},{"type":"info","message":"Using the schema for HTML5 + SVG 1.1 + MathML 3.0 + RDFa Lite 1.1."},{"type":"error","lastLine":71,"firstLine":70,"lastColumn":34,"firstColumn":1521,"message":"Attribute “width” not allowed on element “blockquote” at this point.","extract":"tweet:</p><blockquote\nclass=\"twitter-tweet\" width=\"500\"><p>We&","hiliteStart":10,"hiliteLength":46}]}';

	/**
	 * Tests to see if a good report is handled correctly.
	 *
	 * @link https://github.com/allan23/validated/issues/7
	 */
	public function test_good_report() {

		// Become an administrator.
		$this->_setRole( 'administrator' );
		$post_id = wp_insert_post( array(
			'post_title'  => 'test',
			'post_type'   => 'post',
			'post_status' => 'publish',
		) );
		$errors  = Validated::get_instance()->check_errors( json_decode( $this->mock_invalid ) );
		$results = array(
			'errors'  => $errors,
			'results' => json_decode( $this->mock_invalid ),
		);
		update_post_meta( (int) $post_id, '__validated', $results );
		$_POST['_wpnonce'] = wp_create_nonce( 'validated_security' );
		$_POST['post_id']  = $post_id;
		$_POST['action']   = 'validated_results';
		try {
			$this->_handleAjax( 'validated_results' );
		} catch ( WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
			unset( $e );
		}

		$response = json_decode( $this->_last_response );
		$this->assertTrue( $response->success );
	}

	/**
	 * Tests to see if a bad report is handled correctly.
	 *
	 * @link https://github.com/allan23/validated/issues/7
	 */
	public function test_bad_report() {

		// Become an administrator.
		$this->_setRole( 'administrator' );
		$post_id = wp_insert_post( array(
			'post_title'  => 'test',
			'post_type'   => 'post',
			'post_status' => 'publish',
		) );
		$errors  = Validated::get_instance()->check_errors( json_decode( $this->mock_invalid ) );
		$results = array(
			'errors'  => $errors,
			'results' => json_decode( array() ),
		);
		update_post_meta( (int) $post_id, '__validated', $results );
		$_POST['_wpnonce'] = wp_create_nonce( 'validated_security' );
		$_POST['post_id']  = $post_id;
		$_POST['action']   = 'validated_results';
		try {
			$this->_handleAjax( 'validated_results' );
		} catch ( WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
			unset( $e );
		}

		$response = json_decode( $this->_last_response );
		$this->assertFalse( $response->success );
	}
}
