<?php
/**
 * @package snow-monkey-search
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\Search\App;

class Register {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, '_register_blocks' ) );
		add_action( 'init', array( $this, '_register_post_types' ) );
		add_filter( 'block_categories_all', array( $this, '_block_categories' ) );
	}

	/**
	 * Register blocks.
	 */
	public function _register_blocks() {
		register_block_type( SNOW_MONKEY_SEARCH_PATH . '/dist/blocks/custom-field-search' );
		register_block_type( SNOW_MONKEY_SEARCH_PATH . '/dist/blocks/item' );
		register_block_type( SNOW_MONKEY_SEARCH_PATH . '/dist/blocks/keyword-search' );
		register_block_type( SNOW_MONKEY_SEARCH_PATH . '/dist/blocks/period-search' );
		register_block_type( SNOW_MONKEY_SEARCH_PATH . '/dist/blocks/search-box' );
		register_block_type( SNOW_MONKEY_SEARCH_PATH . '/dist/blocks/taxonomy-search' );

		foreach ( \WP_Block_Type_Registry::get_instance()->get_all_registered() as $block_type => $block ) {
			if ( 0 === strpos( $block_type, 'snow-monkey-search/' ) ) {
				$handle = str_replace( '/', '-', $block_type ) . '-editor-script';
				wp_set_script_translations( $handle, 'snow-monkey-search', SNOW_MONKEY_SEARCH_PATH . '/languages' );
			}
		}
	}

	/**
	 * Register post types.
	 */
	public function _register_post_types() {
		register_post_meta(
			'snow-monkey-search',
			'sms_related_post_type',
			array(
				'show_in_rest'      => true,
				'type'              => 'string',
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		register_post_type(
			'snow-monkey-search',
			array(
				'label'        => __( 'Snow Monkey Search', 'snow-monkey-search' ),
				'public'       => false,
				'show_ui'      => true,
				'show_in_rest' => true,
				'capabilities' => array(
					'edit_post'          => 'manage_options',
					'read_post'          => 'manage_options',
					'delete_post'        => 'manage_options',
					'edit_posts'         => 'manage_options',
					'delete_posts'       => 'manage_options',
					'publish_posts'      => 'manage_options',
					'read_private_posts' => 'manage_options',
				),
				'supports'     => array( 'title', 'editor', 'custom-fields' ),
				'template'     => array(
					array(
						'snow-monkey-search/search-box',
						array(
							'relatedPostType' => 'post',
							'style'           => array(
								'border' => array(
									'width' => '1px',
								),
							),
							'borderColor'     => 'sm-light-gray',
							'backgroundColor' => 'sm-lightest-gray',
							'lock'            => array(
								'move'   => true,
								'remove' => true,
							),
						),
						array(
							array(
								'snow-monkey-search/item',
								array(
									'flexBasis' => '100%',
								),
								array(
									array(
										'snow-monkey-search/keyword-search',
									),
								),
							),
							array(
								'snow-monkey-search/item',
								array(),
								array(
									array(
										'snow-monkey-search/taxonomy-search',
										array(
											'postType' => 'post',
											'taxonomy' => 'category',
										),
									),
								),
							),
							array(
								'snow-monkey-search/item',
								array(),
								array(
									array(
										'snow-monkey-search/taxonomy-search',
										array(
											'postType' => 'post',
											'taxonomy' => 'post_tag',
										),
									),
								),
							),
						),
					),
				),
			)
		);
	}

	/**
	 * Register block categories.
	 *
	 * @param array $categories array Array of block categories.
	 * @return array
	 */
	public function _block_categories( $categories ) {
		$categories[] = array(
			'slug'  => 'snow-monkey-search',
			'title' => __( 'Snow Monkey Search', 'snow-monkey-search' ),
		);

		return $categories;
	}
}
