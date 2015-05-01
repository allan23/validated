jQuery( document ).ready( function() {
    jQuery( "a.validated_check" ).click( function( e ) {
        e.preventDefault();
        var post_id = jQuery( this ).attr( 'data-pid' );
        jQuery( '#validated_checking_' + post_id ).show();
        jQuery( '#validated_' + post_id ).hide().html( '' );
        jQuery.post( ajax_object.ajax_url, { action: 'validated', security: ajax_object.security, post_id: post_id }, function( response ) {
            jQuery( '#validated_checking_' + post_id ).hide();
            jQuery( '#validated_' + post_id ).html( response ).show();
        } );
    } );
} );