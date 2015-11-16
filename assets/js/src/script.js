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

            if ( false !== validated_html.data.report ) {
                var validated_modal = $( '#TB_ajaxContent' );
                if ( typeof validated_html.data.report === undefined ) {
                    validated_modal.html( '<p>There was an issue between your server and W3C. Please try again.</p>' );
                } else {
                    validated_modal.html( '<p>' + validated_html.data + '</p>' );
                }

            }
        } );
    } );
    $( document.body ).on( 'submit', '#validate_bulk', function ( event ) {
        event.preventDefault();
        jQuery( '#validated_progress_bar' ).slideDown();
        bulk_validation( 0, 1,0 );
    } );
    var bulk_validation = function ( offset, limit,error_count ) {
        var data = {
            'action': 'validated_bulk',
            'security': ajax_object.security,
            'offset': offset,
            'limit': limit,
            'error_count': error_count
        };
        jQuery.post( ajax_object.ajax_url, data, function ( response ) {
            jQuery( '#validated_progress' ).css( 'width', response.progress + '%' );
            jQuery('#validated_bulk_results').html('Checked: ' + response.offset + '/' + response.total + ' | ' + response.error_count + ' with errors');
            if ( response.progress < 100 ) {
                bulk_validation( response.offset, response.limit, response.error_count );
            }
        } );
    };
} );