<?php

class ValidatedTests extends WP_UnitTestCase {

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
			'ID'			 => $this->pid,
			'post_content'	 => 'Something different'
		) );
		$this->assertEquals( '', get_post_meta( $this->pid, '__validated', true ) );
	}

}
