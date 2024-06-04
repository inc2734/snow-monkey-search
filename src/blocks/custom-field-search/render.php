<?php
/**
 * @package snow-monkey-search
 * @author inc2734
 * @license GPL-2.0+
 */

$http_this  = filter_input( INPUT_GET, 'sms-post-meta', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) ?? array();
$http_value = $http_this[ $attributes['key'] ]['value'] ?? false;

$controlType = 'text';
switch ( $attributes['type'] ) {
	case 'numeric':
		$controlType = 'number';
		break;
	case 'date':
		$controlType = 'date';
		break;
	case 'datetime':
		$controlType = 'datetime-local';
		break;
	case 'time':
		$controlType = 'time';
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
		<?php if ( 'date' === $controlType || 'datetime-local' === $controlType || 'time' === $controlType ) : ?>
			<div class="sms-date-control">
				<input
					type="<?php echo esc_attr( $controlType ); ?>"
					class="c-form-control"
					name="sms-post-meta[<?php echo esc_attr( $attributes['key'] ); ?>][value]"
					value="<?php echo esc_attr( $http_value ); ?>"
				/>
			</div>
		<?php else : ?>
			<input
				type="<?php echo esc_attr( $controlType ); ?>"
				class="c-form-control"
				name="sms-post-meta[<?php echo esc_attr( $attributes['key'] ); ?>][value]"
				value="<?php echo esc_attr( $http_value ); ?>"
			/>
		<?php endif; ?>

		<input type="hidden" name="sms-post-meta[<?php echo esc_attr( $attributes['key'] ); ?>][compare]" value="<?php echo esc_attr( $attributes['compare'] ); ?>" />
		<input type="hidden" name="sms-post-meta[<?php echo esc_attr( $attributes['key'] ); ?>][type]" value="<?php echo esc_attr( $attributes['type'] ); ?>" />
	</div>
</div>
