<?php

/**
 * @name Validated Class
 */
class Validated {

	/**
	 * Singleton instance
	 * @var Validated|Bool
	 */
	private static $instance = false;

	/**
	 * Grab instance of object.
	 * @return Validated
	 */
	public static function get_instance() {
		if ( !self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Actions and Filters
	 */
	function __construct() {
		add_filter( 'manage_posts_columns', array( $this, 'post_columns' ) );
		add_filter( 'manage_pages_columns', array( $this, 'post_columns' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'display_columns' ), 10, 2 );
		add_action( 'manage_pages_custom_column', array( $this, 'display_columns' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_script' ) );
		add_action( 'wp_ajax_validated', array( $this, 'validate_url' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
	}

	/*
	 * Enqueue the CSS, JavaScript and add some localization with a nonce and the ajax url.
	 */

	function load_script() {
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_style( 'validated-css', VA_URL . "assets/css/style.min.css" );
		wp_enqueue_script( 'validated-js', VA_URL . "assets/js/script.min.js" );
		wp_localize_script( 'validated-js', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'security' => wp_create_nonce( "validated_security" ) ) );
	}

	/**
	 * Filter the columns on pages and posts.
	 * @param array $columns
	 * @return array
	 */
	function post_columns( $columns ) {
		$columns[ 'validated_is_valid' ] = 'W3C Validation';
		$columns[ 'validated_check' ]	 = 'Check Validation';
		return $columns;
	}

	/**
	 * Populate the columns with post/site related data.
	 * @param string $column
	 * @param int $post_id
	 */
	function display_columns( $column, $post_id ) {

		switch ( $column ) {
			case 'validated_is_valid':
				$headers = get_post_meta( $post_id, '__validated', true );
				echo '<div id="validated_' . esc_attr( $post_id ) . '">';
				$this->show_results( $headers, false );
				echo '</div>';
				echo '<div id="validated_checking_' . esc_attr( $post_id ) . '" class="validated_loading"><img src="' . esc_url( VA_URL ) . '/assets/images/load.gif" alt="Loading"><br>Checking Now...</div>';
				break;
			case 'validated_check':
				echo '<a href="#" class="button-primary a_validated_check" data-pid="' . esc_attr( $post_id ) . '"><span class="dashicons dashicons-search"></span> Check</a>';
				break;
		}
	}

	/**
	 * AJAX response.
	 */
	function validate_url() {
		check_ajax_referer( 'validated_security', 'security' );
		if (isset($_POST['post_id'])){
			$post_id = filter_var( $_POST[ 'post_id' ], FILTER_SANITIZE_NUMBER_INT );
		}
		
		if ( !$post_id ) {
			return $this->process_error( 'Post ID not passed.' );
		}
		if ( defined( 'VALIDATED_LOCAL' ) && true === VALIDATED_LOCAL ) {
			return $this->process_post_local( $post_id );
		}
		return $this->process_post( $post_id );
	}

	/**
	 * Sends the post/page permalink URL to the W3C Validator, saves results into postmeta, and returns results.
	 * @param type $post_id
	 */
	private function process_post( $post_id ) {
		$url		 = get_permalink( $post_id );
		$checkurl	 = 'http://validator.w3.org/check?uri=' . $url;
		$request	 = wp_remote_get( $checkurl );
		if ( is_wp_error( $request ) ) {
			return $this->process_error();
		}
		$headers				 = $request[ 'headers' ];
		$headers[ 'checkurl' ]	 = $checkurl;
		update_post_meta( $post_id, '__validated', $headers );
		$result					 = $this->show_results( $headers );

		return wp_send_json_success( array( 'result' => $result, 'type' => 'Live', 'checkurl' => $checkurl ) );
	}

	/**
	 * Sends page HTML to W3C Validator.
	 * @param int $post_id
	 */
	private function process_post_local( $post_id ) {
		$url		 = get_permalink( $post_id );
		$checkurl	 = 'http://validator.w3.org/check';
		$args		 = $this->snag_local_code( $url );
		$request	 = wp_remote_post( $checkurl, $args );

		if ( is_wp_error( $request ) ) {
			return $this->process_error();
		}
		$headers				 = $request[ 'headers' ];
		$headers[ 'checkurl' ]	 = $checkurl . '?uri=' . $url;
		update_post_meta( $post_id, '__validated', $headers );
		$result					 = $this->show_results( $headers );

		return wp_send_json_success( array( 'result' => $result, 'type' => 'Local' ) );
	}

	/**
	 * Snags local HTML and returns wp_remote arguments.
	 * @param string $url Local URL
	 * @return array
	 */
	private function snag_local_code( $url ) {
		$args = array(
			'body' => array(
				'fragment' => wp_remote_retrieve_body( wp_remote_get( $url ) )
			)
		);
		return $args;
	}

	/**
	 * Send back response because of error.
	 */
	private function process_error( $msg = '' ) {
		$result = '<span class="validated_not_valid"><span class="dashicons dashicons-dismiss"></span> Something Went Wrong.</span>';
		return wp_send_json_error( array( 'result' => $result, 'msg' => $msg ) );
	}

	/**
	 * Takes returned HTTP headers from W3C Validator request and parses data.
	 * @param $headers[] $headers
	 * @param bool $return
	 */
	function show_results( $headers, $return = true ) {
		if ( !$headers ) {
			return;
		}
		$result = '';
		if ( isset( $headers[ 'x-w3c-validator-status' ] ) ) {
			if ( 'Valid' === $headers[ 'x-w3c-validator-status' ] ) {
				$result.= '<span class="validated_is_valid"><span class="dashicons dashicons-yes"></span> Valid</span>';
			} elseif ( 'Abort' === $headers[ 'x-w3c-validator-status' ] ) {
				$result.='<span class="validated_not_valid"><span class="dashicons dashicons-dismiss"></span> Something Went Wrong.</span>';
			} else {
				$result.='<span class="validated_not_valid"><span class="dashicons dashicons-no"></span> <a href="' . esc_url( $headers[ 'checkurl' ] ) . '&TB_iframe=true&width=600&height=550" title="Validation Results" target="_blank" class="thickbox">' . esc_html( $headers[ 'x-w3c-validator-errors' ] ) . ' Errors</a></span>';
			}
			$result.='<br><small>Last checked: ' . esc_html( $headers[ 'date' ] ) . '</small>';
		} else {
			$result.='<span class="validated_not_valid"><span class="dashicons dashicons-dismiss"></span> Something Went Wrong.</span>';
		}
		if ( $return ) {
			return $result;
		}
		echo $result; //XSS ok
	}

	/**
	 * Fires when a post is saved.
	 * Clears out the post_meta value for saved validation results.
	 * @param int $post_id
	 */
	function save_post( $post_id ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		delete_post_meta( $post_id, '__validated' );
	}

}
