<?php
/**
 * @package snow-monkey-search
 * @author inc2734
 * @license GPL-2.0+
 */

$related_post_type = $attributes['relatedPostType'] ?? false;
if ( ! $related_post_type ) {
	return;
}

$action_to = get_post_type_archive_link( $related_post_type );
if ( ! $action_to ) {
	return;
}

$block_wrapper = get_block_wrapper_attributes( array( 'class' => 'sms-search-box' ) );
?>

<form
	<?php echo $block_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	method="get"
	<?php if ( $action_to ) : ?>
		action="<?php echo esc_url( $action_to ); ?>"
	<?php endif; ?>
>
	<div class="sms-search-box__content">
		<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>

	<div class="sms-search-box__action">
		<input type="hidden" name="post_type" value="<?php echo esc_attr( $related_post_type ); ?>" />
		<input type="hidden" name="snow-monkey-search" value="<?php the_ID(); ?>" />

		<button type="button" id="sms-clear" class="sms-search-box__clear">
			<?php esc_html_e( 'Clear Filter', 'snow-monkey-search' ); ?>
		</button>

		<button type="submit" class="c-btn sms-search-box__submit">
			<?php esc_html_e( 'Apply Filter', 'snow-monkey-search' ); ?>
		</button>
	</div>
</form>
