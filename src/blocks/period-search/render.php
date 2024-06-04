<?php
/**
 * @package snow-monkey-search
 * @author inc2734
 * @license GPL-2.0+
 */


$http_start = filter_input( INPUT_GET, 'sms-period-start' );
$http_end   = filter_input( INPUT_GET, 'sms-period-end' );

switch ( $attributes['controlType'] ) {
	case 'month':
		$pattern = '\d{4}-\d{2}';
		break;
	case 'date':
	default:
		$pattern = '\d{4}-\d{2}-\d{2}';
		break;
}

$min = $attributes['min'] && preg_match( '|^' . $pattern . '$|', $attributes['min'] )
	? $attributes['min']
	: false;

$max = $attributes['max'] && preg_match( '|^' . $pattern . '$|', $attributes['max'] )
	? $attributes['max']
	: false;

$block_wrapper = get_block_wrapper_attributes( array( 'class' => 'sms-period-search sms-form-control' ) );
?>

<div <?php echo $block_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $attributes['label'] ) : ?>
		<div class="sms-period-search__header sms-form-control__header">
			<strong><?php echo wp_kses_post( $attributes['label'] ); ?></strong>
		</div>
	<?php endif; ?>

	<div class="sms-period-search__content sms-form-control__content">
		<div class="sms-period-search__start">
			<input
				type="<?php echo esc_attr( $attributes['controlType'] ); ?>"
				class="c-form-control"
				name="sms-period-start"
				pattern="<?php echo esc_attr( $pattern ); ?>"
				value="<?php echo esc_attr( $http_start ); ?>"
				<?php if ( $min ) : ?>
					min="<?php echo esc_attr( $min ); ?>"
				<?php endif; ?>
			/>
		</div>
		<div class="sms-period-search__delimiter">
			<?php esc_html_e( '〜', 'snow-monkey-search' ); ?>
		</div>
		<div class="sms-period-search__end">
			<input
				type="<?php echo esc_attr( $attributes['controlType'] ); ?>"
				class="c-form-control"
				name="sms-period-end"
				pattern="<?php echo esc_attr( $pattern ); ?>"
				value="<?php echo esc_attr( $http_end ); ?>"
				<?php if ( $max ) : ?>
					max="<?php echo esc_attr( $max ); ?>"
				<?php endif; ?>
			/>
		</div>
	</div>
</div>
