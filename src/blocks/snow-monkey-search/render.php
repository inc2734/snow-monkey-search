<?php
/**
 * @package snow-monkey-search
 * @author inc2734
 * @license GPL-2.0+
 */

if ( empty( $attributes['formId'] ) ) {
	return;
}

$form_id = $attributes['formId'];

$the_query = new \WP_Query(
	array(
		'post_type'        => 'snow-monkey-search',
		'posts_per_page'   => 1,
		'suppress_filters' => false,
		'no_found_rows'    => true,
		'p'                => $form_id,
	)
);

while ( $the_query->have_posts() ) {
	$the_query->the_post();
	the_content();
}
wp_reset_postdata();
