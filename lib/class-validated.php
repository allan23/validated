<?php

/**
 * @name Validated Class
 */
class Validated {

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
    }

    /*
     * Enqueue the CSS, JavaScript and add some localization with a nonce and the ajax url.
     */

    function load_script() {
        wp_enqueue_style( 'validated-css', plugin_dir_url( __FILE__ ) . "../css/style.css" );
        wp_enqueue_script( 'validated-js', plugin_dir_url( __FILE__ ) . "../js/script.js", array( 'jquery' ) );
        wp_localize_script( 'validated-js', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'security' => wp_create_nonce( "validated_security" ) ) );
    }

    /**
     * Filter the columns on pages and posts.
     * @param array $columns
     * @return array
     */
    function post_columns( $columns ) {
        $columns['validated_is_valid'] = 'W3C Validation';
        $columns['validated_check']    = 'Check Validation';
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
                $this->show_results( $headers );
                echo '</div>';
                echo '<div id="validated_checking_' . esc_attr( $post_id ) . '" class="validated_loading"><img src="' . esc_url( plugin_dir_url( __FILE__ ) ) . '../images/load.gif" alt="Loading"><br>Checking Now...</div>';
                break;
            case 'validated_check':
                echo '<a href="#" class="button-primary validated_check" data-pid="' . esc_attr( $post_id ) . '"><span class="dashicons dashicons-search"></span> Check</a>';
                break;
        }
    }

    /**
     * Sends the post/page permalink URL to the W3C Validator, saves results into postmeta, and echos results.
     * AJAX response.
     */
    function validate_url() {
        check_ajax_referer( 'validated_security', 'security' );
        $post_id  = (int) sanitize_text_field( $_POST['post_id'] );
        $url      = get_permalink( $post_id );
        $checkurl = 'http://validator.w3.org/check?uri=' . $url;
        $request  = wp_remote_get( $checkurl );
        if ( is_wp_error( $request ) ) {
            echo '<span class="validated_not_valid"><span class="dashicons dashicons-dismiss"></span> Something Went Wrong.</span>';
        } else {
            $headers             = $request['headers'];
            $headers['checkurl'] = $checkurl;
            update_post_meta( $post_id, '__validated', $headers );
            $this->show_results( $headers );
        }
        die();
    }

    /**
     * Takes returned HTTP headers from W3C Validator request and parses data.
     * @param $headers[] $headers
     */
    function show_results( $headers ) {
        if ( !$headers ) {
            return;
        }
        if ( isset( $headers['x-w3c-validator-status'] ) ) {
            if ( 'Valid' === $headers['x-w3c-validator-status'] ) {
                echo '<span class="validated_is_valid"><span class="dashicons dashicons-yes"></span> Valid</span>';
            } elseif ( 'Abort' === $headers['x-w3c-validator-status'] ) {
                echo '<span class="validated_not_valid"><span class="dashicons dashicons-dismiss"></span> Something Went Wrong.</span>';
            } else {
                echo '<span class="validated_not_valid"><span class="dashicons dashicons-no"></span> <a href="' . esc_url($headers['checkurl']) . '" title="View Error Details" target="_blank">' . esc_html( $headers['x-w3c-validator-errors'] ) . ' Errors</a></span>';
            }
            echo '<br><small>Last checked: ' . esc_html( $headers['date'] ) . '</small>';
        } else {
            echo '<span class="validated_not_valid"><span class="dashicons dashicons-dismiss"></span> Something Went Wrong.</span>';
        }
    }

}
