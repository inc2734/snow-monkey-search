<?php
/**
 * @package snow-monkey-search
 * @author inc2734
 * @license GPL-2.0+
 */

// For plain permalink.
$http_post_type = filter_input( INPUT_GET, 'post_type' );

// For taxonomy archive.
if ( is_tax() || is_category() || is_tag() ) {
	$queried_object = get_queried_object();
	$http_taxonomy  = $queried_object->taxonomy;
	if ( $http_taxonomy ) {
		$the_taxonomy                = get_taxonomy( $http_taxonomy );
		$post_type_for_http_taxonomy = $the_taxonomy->object_type[0] ?? false;
	}
}

$block_wrapper = get_block_wrapper_attributes( array( 'class' => 'sms-search-box' ) );

$action_to   = '';
$request_uri = wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
if ( $request_uri ) {
	$home_url      = home_url();
	$sub_directory = parse_url( $home_url, PHP_URL_PATH ) ?? '';
	$absolute_path = preg_replace( '|^' . preg_quote( $sub_directory ) . '|', '', $request_uri );
	$action_to     = untrailingslashit( $home_url ) . $absolute_path;
	$action_to     = preg_replace( '|/page/\d+|', '', $action_to );
	$action_to     = preg_replace( '|paged=\d+|', '', $action_to );
}
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
		<?php if ( ! empty( $http_post_type ) ) : ?>
			<input type="hidden" name="post_type" value="<?php echo esc_attr( $http_post_type ); ?>" />
		<?php endif; ?>

		<?php if ( ! empty( $post_type_for_http_taxonomy ) ) : ?>
			<input type="hidden" name="post_type" value="<?php echo esc_attr( $post_type_for_http_taxonomy ); ?>" />
		<?php endif; ?>

		<input type="hidden" name="snow-monkey-search" value="<?php the_ID(); ?>" />

		<button type="button" id="sms-clear" class="sms-search-box__clear">
			<?php esc_html_e( 'Clear Filter', 'snow-monkey-search' ); ?>
		</button>

		<button type="submit" class="c-btn sms-search-box__submit">
			<?php esc_html_e( 'Apply Filter', 'snow-monkey-search' ); ?>
		</button>
	</div>
</form>
