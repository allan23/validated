<?php
/**
 * PHPUnit Bootstrap
 *
 * @package validated
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

/**
 * This function will manually load the plugin so it can be tested against.
 */
function _manually_load_plugin() {
	require dirname( __FILE__ ) . '/../validated.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';

