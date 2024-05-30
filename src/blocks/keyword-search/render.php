<?php
/**
 * @package snow-monkey-search
 * @author inc2734
 * @license GPL-2.0+
 */

$block_wrapper = get_block_wrapper_attributes( array( 'class' => 'sms-keyword-search sms-form-control' ) );
?>

<div <?php echo $block_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $attributes['label'] ) : ?>
		<div class="sms-keyword-search__header sms-form-control__header">
			<strong><?php echo wp_kses_post( $attributes['label'] ); ?></strong>
		</div>
	<?php endif; ?>

	<div class="sms-keyword-search__content sms-form-control__content">
		<input
			type="text"
			class="c-form-control"
			name="s"
			placeholder="<?php echo esc_attr( $attributes['placeholder'] ); ?>"
			value="<?php echo esc_attr( get_search_query() ); ?>"
		/>
	</div>
</div>
