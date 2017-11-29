<?php
/**
 * Validation results report.
 *
 * @package validated
 */

?>
	There were <strong><?php echo esc_html( $results['errors'] ); ?></strong> errors found on
	<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a>.
	<br><br>
<?php
foreach ( $results['results']->messages as $item ) :
	if ( 'error' !== $item->type ) {
		continue;
	}
	?>
	<strong>
		<em>Line <?php echo esc_html( $item->lastLine ); ?></em>:</strong> <?php echo esc_html( $item->message ); ?>
	<br><br>
	<code>
		<?php echo esc_html( $item->extract ); ?>
	</code>
	<br><br>
	<hr>
<?php
endforeach;
