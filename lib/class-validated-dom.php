<?php

class Validated_DOM {

	/**
	 * Uses DOMDocument to parse returned HTML.
	 * @param string $html
	 * @return string
	 */
	static function get_html( $html, $status ) {
		if ( 'Valid' == $status ) {
			return '<li><span class="validated_is_valid"><span class="dashicons dashicons-yes"></span> Valid</span></li>';
		}
		if ( !class_exists( 'DOMDocument' ) ) {
			return false;
		}
		$doc	 = new DOMDocument();
		$doc->loadHTML( $html );
		$ol		 = $doc->getElementById( 'error_loop' );
		$errors	 = $ol->getElementsByTagName( 'li' );
		$return	 = '';
		for ( $c = 0; $c < $errors->length; $c++ ) {
			$item	 = $errors->item( $c );
			$item->removeChild( $item->getElementsByTagName( 'span' )->item( 0 ) );
			$the_p	 = $item->getElementsByTagName( 'p' );
			if ( 0 != $the_p->length ) {
				$item->removeChild( $the_p->item( 0 ) );
			}
			return $doc->saveHTML( $errors->item( $c ) );
		}
		return $return;
	}

}
