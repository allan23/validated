/*jslint browser:true */
/*global ajax_object:false, jQuery */
jQuery( document ).ready( function ( $ ) {
    $( document.body ).on( 'click', '.a_validated_check', function ( event ) {
        event.preventDefault();
        var post_id = $( this ).data( 'pid' );
        var checking_el = $( '#validated_checking_' + post_id );
        var validated_el = $( '#validated_' + post_id );

        checking_el.show();
        validated_el.hide();
        validated_el.html( '' );
        var data = {
            'action': 'validated',
            'security': ajax_object.security,
            'post_id': post_id
        };
        jQuery.post( ajax_object.ajax_url, data, function ( validated_html ) {
            checking_el.hide();
            validated_el.html( validated_html.data.result );
            validated_el.show();
        } );
    } );

    $( document.body ).on( 'click', '.validated_show_report', function ( event ) {
        event.preventDefault();
        var post_id = $( this ).data( 'pid' );

        var data = {
            'action': 'validated_results',
            'security': ajax_object.security,
            'post_id': post_id
        };
        jQuery.post( ajax_object.ajax_url, data, function ( validated_html ) {
            var validated_modal = $( '#TB_ajaxContent' );
            if ( false === validated_html.success ) {
                 validated_modal.html( '<h2>Oops!</h2><p>The data for this report is missing. Please try validating again.</p>' );
                 return;
            }
            if ( false !== validated_html.data.report ) {

                if ( typeof validated_html.data.report === undefined ) {
                    validated_modal.html( '<p>There was an issue between your server and W3C. Please try again.</p>' );
                } else {
                    validated_modal.html( '<p>' + validated_html.data + '</p>' );
                }

            }

        } );
    } );
} );