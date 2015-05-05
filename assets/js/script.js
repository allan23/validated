/*! Validated - v2.1.0
 * http://www.allancollins.net
 * Copyright (c) 2015; * Licensed GPLv2+ */
/*global ajax_object:false */
var val_check;
function validated_check_now( i ) {
    return function ( event ) {
        event.preventDefault();
        var post_id = val_check[i].getAttribute( 'data-pid' );
        var checking_el = document.getElementById( 'validated_checking_' + post_id );
        var validated_el = document.getElementById( 'validated_' + post_id );
        checking_el.style.display = 'block';
        validated_el.style.display = 'none';
        validated_el.innerHTML = '';


        var xhr = new XMLHttpRequest();
        var send_data = 'action=validated&security=' + ajax_object.security + '&post_id=' + post_id;
        xhr.open( 'POST', ajax_object.ajax_url, true );
        xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8' );
        xhr.onreadystatechange = function () {
            if ( 4 === xhr.readyState ) {
                checking_el.style.display = 'none';
                var validated_html = JSON.parse( xhr.responseText );
                validated_el.innerHTML = validated_html.result;
                validated_el.style.display = 'block';
            }
        };

        xhr.send( send_data );
    };
}


window.onload = function () {
    val_check = document.querySelectorAll( '.a_validated_check' );
    var i;
    for ( i = 0; i < val_check.length; i++ ) {
        val_check[i].addEventListener( 'click', validated_check_now( i ) );
    }
};

