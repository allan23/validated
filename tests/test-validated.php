<?php

class ValidatedTests extends WP_UnitTestCase {

	/**
	 * Post ID
	 * @var int
	 */
	var $pid;
	var $mock_valid = '{"url":"http://www.example.org","messages":[{"type":"info","message":"The Content-Type was “text/html”. Using the HTML parser."},{"type":"info","message":"Using the schema for HTML5 + SVG 1.1 + MathML 3.0 + RDFa Lite 1.1."}]}';
	var $mock_invalid = '{"url":"http://www.example.org/","messages":[{"type":"info","message":"The Content-Type was “text/html”. Using the HTML parser."},{"type":"info","message":"Using the schema for HTML5 + SVG 1.1 + MathML 3.0 + RDFa Lite 1.1."},{"type":"error","lastLine":71,"firstLine":70,"lastColumn":34,"firstColumn":1521,"message":"Attribute “width” not allowed on element “blockquote” at this point.","extract":"tweet:</p><blockquote\nclass=\"twitter-tweet\" width=\"500\"><p>We&","hiliteStart":10,"hiliteLength":46}]}';

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		parent::setUp();
		$this->pid = $this->factory->post->create( array(
			'post_type'    => 'page',
			'post_title'   => 'Test Post',
			'post_content' => 'Some text'
		) );
		add_post_meta( $this->pid, '__validated', 'Some validation results.' );
	}

	function tearDown() {
		wp_delete_post( $this->pid );
		parent::tearDown();
	}

	/**
	 * Check to see of the post_meta gets removed when the post is updated.
	 */
	function test_post_meta_removal_on_save() {


		wp_update_post( array(
			'ID'           => $this->pid,
			'post_content' => 'Something different'
		) );
		$this->assertEquals( '', get_post_meta( $this->pid, '__validated', true ) );
	}

	function test_check_invalid() {

		$errors = Validated::get_instance()->check_errors( json_decode( $this->mock_invalid ) );

		$this->assertEquals( 1, $errors );
	}

	function test_check_valid() {

		$errors = Validated::get_instance()->check_errors( json_decode( $this->mock_valid ) );

		$this->assertEquals( 0, $errors );
	}

	function test_true(){
		$this->assertTrue(true);
	}
}
