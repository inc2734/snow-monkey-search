<?php
/**
 * @package snow-monkey-search
 * @author inc2734
 * @license GPL-2.0+
 */

$http_this  = filter_input( INPUT_GET, 'sms-post-meta', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) ?? array();
$http_value = $http_this[ $attributes['key'] ]['value'] ?? false;

$control_type = 'text';
switch ( $attributes['type'] ) {
	case 'numeric':
		$control_type = 'number';
		break;
	case 'date':
		$control_type = 'date';
		break;
	case 'datetime':
		$control_type = 'datetime-local';
		break;
	case 'time':
		$control_type = 'time';
		break;
}

$block_wrapper = get_block_wrapper_attributes( array( 'class' => 'sms-custom-field-search sms-form-control' ) );
?>

<div <?php echo $block_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $attributes['label'] ) : ?>
		<div class="sms-custom-field-search__header sms-form-control__header">
			<strong><?php echo wp_kses_post( $attributes['label'] ); ?></strong>
		</div>
	<?php endif; ?>

	<div class="sms-custom-field-search__content sms-form-control__content">
		<?php if ( 'date' === $control_type || 'datetime-local' === $control_type || 'time' === $control_type ) : ?>
			<div class="sms-date-control">
				<input
					type="<?php echo esc_attr( $control_type ); ?>"
					class="c-form-control"
					name="sms-post-meta[<?php echo esc_attr( $attributes['key'] ); ?>][value]"
					value="<?php echo esc_attr( $http_value ); ?>"
				/>
			</div>
		<?php else : ?>
			<input
				type="<?php echo esc_attr( $control_type ); ?>"
				class="c-form-control"
				name="sms-post-meta[<?php echo esc_attr( $attributes['key'] ); ?>][value]"
				value="<?php echo esc_attr( $http_value ); ?>"
			/>
		<?php endif; ?>

		<input type="hidden" name="sms-post-meta[<?php echo esc_attr( $attributes['key'] ); ?>][compare]" value="<?php echo esc_attr( $attributes['compare'] ); ?>" />
		<input type="hidden" name="sms-post-meta[<?php echo esc_attr( $attributes['key'] ); ?>][type]" value="<?php echo esc_attr( $attributes['type'] ); ?>" />
	</div>
</div>
