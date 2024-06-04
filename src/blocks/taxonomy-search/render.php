<?php
/**
 * @package snow-monkey-search
 * @author inc2734
 * @license GPL-2.0+
 */

$wp_taxonomy = get_taxonomy( $attributes['taxonomy'] );
if ( ! $wp_taxonomy ) {
	return;
}

$get_taxonomy_terms_with_depth = function ( $args ) {
	$terms = get_terms( $args );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return array();
	}

	$terms_by_id = array();
	foreach ( $terms as $term ) {
		$terms_by_id[ $term->term_id ] = $term;
	}

	if ( ! function_exists( 'get_terms_with_depth_recursive' ) ) {
		/**
		 * Helper function to recursively retrieve a list of terms with hierarchy.
		 *
		 * @param array $terms_by_id Array of terms keyed by term id.
		 * @param int $parent_id Parent term id.
		 * @param int $depth Depth level.
		 */
		function get_terms_with_depth_recursive( $terms_by_id, $parent_id = 0, $depth = 0 ) {
			$result = array();

			foreach ( $terms_by_id as $term_id => $term ) {
				if ( $term->parent === $parent_id ) {
					$term->depth = $depth;
					$result[]    = $term;
					$result      = array_merge( $result, get_terms_with_depth_recursive( $terms_by_id, $term_id, $depth + 1 ) );
				}
			}
			return $result;
		}
	}

	$sorted_terms = get_terms_with_depth_recursive( $terms_by_id );

	return $sorted_terms;
};

$terms = $wp_taxonomy->hierarchical
	? $get_taxonomy_terms_with_depth(
		array(
			'taxonomy' => $wp_taxonomy->name,
		)
	)
	: get_terms(
		array(
			'taxonomy' => $wp_taxonomy->name,
		)
	);
if ( ! $terms ) {
	return;
}

$http_this = filter_input( INPUT_GET, 'sms-taxonomies', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) ?? array();
$http_this = $http_this[ $wp_taxonomy->name ] ?? array();
$http_this = is_array( $http_this ) ? $http_this : array();

$block_wrapper = get_block_wrapper_attributes( array( 'class' => 'sms-taxonomy-search sms-form-control' ) );
?>

<div <?php echo $block_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $attributes['label'] ) : ?>
		<div class="sms-taxonomy-search__header sms-form-control__header">
			<strong><?php echo wp_kses_post( $attributes['label'] ); ?></strong>
		</div>
	<?php endif; ?>

	<div class="sms-taxonomy-search__content sms-form-control__content">
		<?php if ( 'checks' === $attributes['controlType'] ) : ?>
			<div class="sms-checkboxes">
				<?php foreach ( $terms as $_term ) : ?>
					<label>
						<span class="c-checkbox">
							<input
								type="checkbox"
								class="c-checkbox__control"
								name="sms-taxonomies[<?php echo esc_attr( $wp_taxonomy->name ); ?>][]"
								value="<?php echo esc_attr( $_term->slug ); ?>"
								<?php if ( $http_this && in_array( $_term->slug, $http_this, true ) ) : ?>
									checked
								<?php endif; ?>
							/>
							<span class="c-checkbox__label"><?php echo esc_html( $_term->name ); ?></span>
						</span>
					</label>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( 'radios' === $attributes['controlType'] ) : ?>
			<div class="sms-radios">
				<?php foreach ( $terms as $_term ) : ?>
					<label>
						<span class="c-radio">
							<input
								type="radio"
								class="c-radio__control"
								name="sms-taxonomies[<?php echo esc_attr( $wp_taxonomy->name ); ?>][]"
								value="<?php echo esc_attr( $_term->slug ); ?>"
								<?php if ( $http_this && in_array( $_term->slug, $http_this, true ) ) : ?>
									checked
								<?php endif; ?>
							/>
							<span class="c-radio__label"><?php echo esc_html( $_term->name ); ?></span>
						</span>
					</label>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( 'select' === $attributes['controlType'] ) : ?>
			<div class="sms-select">
				<div class="c-select">
					<select
						name="sms-taxonomies[<?php echo esc_attr( $wp_taxonomy->name ); ?>][]"
						class="c-select__control"
					>
						<option value=""></option>
						<?php foreach ( $terms as $_term ) : ?>
							<option
								value="<?php echo esc_attr( $_term->slug ); ?>"
								<?php if ( $http_this && in_array( $_term->slug, $http_this, true ) ) : ?>
									selected
								<?php endif; ?>
							>
								<?php echo esc_html( str_repeat( '&#160;&#160;', $_term->depth ) . ' ' . $_term->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<span class="c-select__toggle"></span>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
