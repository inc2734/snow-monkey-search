<?php
/**
 * @package snow-monkey-search
 * @author inc2734
 * @license GPL-2.0+
 */

$control_type = $attributes['controlType'];
if ( 'text' === $control_type ) {
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
}

$options = array();
if ( 'checks' === $control_type || 'radios' === $control_type || 'select' === $control_type ) {
	if ( ! empty( $attributes['options'] ) ) {
		$_options = str_replace( array( "\r\n", "\r", "\n" ), "\n", $attributes['options'] );
		$_options = explode( "\n", $_options );

		foreach ( $_options as $value ) {
			$decoded                    = json_decode( sprintf( '{%1$s}', $value ), true );
			$decoded                    = is_array( $decoded ) ? $decoded : array( $value => $value );
			$decoded                    = is_array( $decoded ) && ! $decoded ? array( '' => '' ) : $decoded;
			$options[ key( $decoded ) ] = array(
				'value' => key( $decoded ),
				'label' => current( $decoded ),
			);
		}
	}
}

$http_this  = filter_input( INPUT_GET, 'sms-post-meta', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) ?? array();
$http_value = $http_this[ $attributes['key'] ]['value'] ?? false;
$http_value = 'checks' === $control_type && ! is_array( $http_value ) ? array() : $http_value;

$block_wrapper = get_block_wrapper_attributes( array( 'class' => 'sms-custom-field-search sms-form-control' ) );
?>

<div <?php echo $block_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $attributes['label'] ) : ?>
		<div class="sms-custom-field-search__header sms-form-control__header">
			<strong><?php echo wp_kses_post( $attributes['label'] ); ?></strong>
		</div>
	<?php endif; ?>

	<div class="sms-custom-field-search__content sms-form-control__content">
		<?php // @todo チェックボックスのときは複数選択ができるから、OR 検索になるようにする必要がある。checked の対応も必要。 ?>
		<?php if ( 'checks' === $control_type ) : ?>
			<div class="sms-checkboxes">
				<?php foreach ( $options as $option ) : ?>
					<label>
						<span class="c-checkbox">
							<input
								type="checkbox"
								class="c-checkbox__control"
								name="sms-post-meta[<?php echo esc_attr( $attributes['key'] ); ?>][value][]"
								value="<?php echo esc_attr( $option['value'] ); ?>"
								<?php if ( in_array( $option['value'], $http_value, true ) ) : ?>
									checked
								<?php endif; ?>
							/>
							<span class="c-checkbox__label"><?php echo esc_html( $option['label'] ); ?></span>
						</span>
					</label>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( 'radios' === $control_type ) : ?>
			<div class="sms-radios">
				<?php foreach ( $options as $option ) : ?>
					<label>
						<span class="c-radio">
							<input
								type="radio"
								class="c-radio__control"
								name="sms-post-meta[<?php echo esc_attr( $attributes['key'] ); ?>][value]"
								value="<?php echo esc_attr( $option['value'] ); ?>"
								<?php if ( $http_value === $option['value'] ) : ?>
									checked
								<?php endif; ?>
							/>
							<span class="c-radio__label"><?php echo esc_html( $option['label'] ); ?></span>
						</span>
					</label>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( 'select' === $control_type ) : ?>
			<div class="sms-select">
				<div class="c-select">
					<select
						name="sms-post-meta[<?php echo esc_attr( $attributes['key'] ); ?>][value]"
						class="c-select__control"
					>
						<option value=""></option>
						<?php foreach ( $options as $option ) : ?>
							<option
								value="<?php echo esc_attr( $option['value'] ); ?>"
								<?php if ( $http_value === $option['value'] ) : ?>
									selected
								<?php endif; ?>
							>
								<?php echo esc_html( $option['label'] ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<span class="c-select__toggle"></span>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( 'date' === $control_type || 'datetime-local' === $control_type || 'time' === $control_type ) : ?>
			<div class="sms-date-control">
				<input
					type="<?php echo esc_attr( $control_type ); ?>"
					class="c-form-control"
					name="sms-post-meta[<?php echo esc_attr( $attributes['key'] ); ?>][value]"
					value="<?php echo esc_attr( $http_value ); ?>"
				/>
			</div>
		<?php endif; ?>

		<?php if ( 'text' === $control_type || 'number' === $control_type ) : ?>
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
