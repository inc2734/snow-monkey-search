<?php
/**
 * @package snow-monkey-search
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\Search\App;

class Rest {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, '_rest_api_init' ) );
	}

	/**
	 * Add an endpoint that returns an array of post meta keys associated with the specified post type.
	 */
	public function _rest_api_init() {
		register_rest_route(
			'snow-monkey-search/v1',
			'/post-meta-keys/(?P<post_type>[a-zA-Z0-9_-]+)',
			array(
				'methods'             => 'GET',
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'callback'            => function ( $request ) {
					$post_type = $request['post_type'];

					global $wp_meta_keys;

					$post_meta_keys = array_keys( $wp_meta_keys['post'][ $post_type ] ?? array() );
					if ( empty( $post_meta_keys ) ) {
						return array();
					}

					return $post_meta_keys;
				},
			)
		);
	}
}
