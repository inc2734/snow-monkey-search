<?php
/**
 * Plugin name: Snow Monkey Search
 * Version: 0.1.0
 * Description: This plugin places a filtered search form in Snow Monkey's custom post archives.
 * Author: inc2734
 * Author URI: https://2inc.org
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: snow-monkey-search
 * Requires at least: 6.5
 * Requires PHP: 7.4
 *
 * @package snow-monkey-search
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\Search;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SNOW_MONKEY_SEARCH_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'SNOW_MONKEY_SEARCH_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

$autoloader_path = SNOW_MONKEY_SEARCH_PATH . '/vendor/autoload.php';
if ( file_exists( $autoloader_path ) ) {
	require_once $autoloader_path;
} else {
	exit;
}

class Bootstrap {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, '_plugins_loaded' ) );
	}

	/**
	 * Plugins loaded.
	 */
	public function _plugins_loaded() {
		add_filter( 'load_textdomain_mofile', array( $this, '_load_textdomain_mofile' ), 10, 2 );
		load_plugin_textdomain( 'snow-monkey-search', false, basename( SNOW_MONKEY_SEARCH_PATH ) . '/languages' );

		add_action( 'init', array( $this, '_register_blocks' ) );
		add_action( 'init', array( $this, '_register_post_types' ) );
		add_filter( 'block_categories_all', array( $this, '_block_categories' ) );

		add_action( 'snow_monkey_prepend_archive_entry_content', array( $this, '_display_search_box' ) );
		add_action( 'pre_get_posts', array( $this, '_pre_get_posts' ) );
		add_action( 'wp', array( $this, '_update_view' ) );
	}

	/**
	 * When local .mo file exists, load this.
	 *
	 * @param string $mofile Path to the MO file.
	 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
	 * @return string
	 */
	public function _load_textdomain_mofile( $mofile, $domain ) {
		if ( 'snow-monkey-search' !== $domain ) {
			return $mofile;
		}

		$mofilename   = basename( $mofile );
		$local_mofile = SNOW_MONKEY_SEARCH_PATH . '/languages/' . $mofilename;
		if ( ! file_exists( $local_mofile ) ) {
			return $mofile;
		}

		return $local_mofile;
	}

	/**
	 * Register blocks.
	 */
	public function _register_blocks() {
		register_block_type( SNOW_MONKEY_SEARCH_PATH . '/dist/blocks/search-box' );
		register_block_type( SNOW_MONKEY_SEARCH_PATH . '/dist/blocks/item' );
		register_block_type( SNOW_MONKEY_SEARCH_PATH . '/dist/blocks/keyword-search' );
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

	/**
	 * Display search box.
	 */
	public function _display_search_box() {
		if ( is_post_type_archive() ) {
			$post_type = get_query_var( 'post_type' );
		} elseif ( is_tax() || is_category() || is_tag() ) {
			$queried_object = get_queried_object();
			$the_taxonomy   = get_taxonomy( $queried_object->taxonomy );
			$post_type      = $the_taxonomy->object_type[0] ?? false;
		} elseif ( is_home() ) {
			$post_type = 'post';
		}

		if ( empty( $post_type ) ) {
			return;
		}

		$the_query = new \WP_Query(
			array(
				'post_type'        => 'snow-monkey-search',
				'posts_per_page'   => 1,
				'suppress_filters' => false,
				'no_found_rows'    => true,
				'meta_query'       => array(
					array(
						'key'     => 'sms_related_post_type',
						'value'   => $post_type,
						'compare' => 'IN',
					),
				),
			)
		);

		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			the_content();
		}
		wp_reset_postdata();
	}

	/**
	 * Set search query.
	 *
	 * @param WP_Query $query The query.
	 */
	public function _pre_get_posts( $query ) {
		if ( $query->is_main_query() && ! is_admin() && ! is_null( filter_input( INPUT_GET, 'snow-monkey-search' ) ) ) {
			$query->is_search = false;

			// Taxonomy query.
			$taxonomies = filter_input( INPUT_GET, 'sms-taxonomies', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			if ( $taxonomies ) {
				$taxonomies = array_unique( $taxonomies );
				$tax_query  = array();

				foreach ( $taxonomies as $taxonomy ) {
					$_tax_query = array();
					$terms      = filter_input( INPUT_GET, 'sms-taxonomy:' . $taxonomy, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) ?? array();
					$terms      = array_filter(
						$terms,
						function ( $value ) {
							return '' !== $value && false !== $value && ! is_null( $value );
						}
					);

					if ( $terms ) {
						$tax_query[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'slug',
							'terms'    => $terms,
							'operator' => 'IN',
						);
					}
				}

				if ( $tax_query ) {
					$tax_query['relation'] = 'AND';

					$query->set( 'tax_query', $tax_query );
				}
			}
		}
	}

	/**
	 * Update view template.
	 */
	public function _update_view() {
		if ( is_null( filter_input( INPUT_GET, 'snow-monkey-search' ) ) ) {
			return;
		}

		add_filter(
			'snow_monkey_get_template_part_args_template-parts/archive/entry/content/no-match',
			function ( $args ) {
				$args['vars']['_display_search_form'] = false;
				$args['vars']['_message']             = __( 'Sorry, but nothing matched your search terms.', 'snow-monkey-search' );
				return $args;
			}
		);

		add_filter(
			'snow_monkey_view',
			function () {
				if ( have_posts() ) {
					global $wp_query;

					$_post_type = $wp_query->get( 'post_type' );
					$_post_type = $_post_type ? $_post_type : 'any';
					$_post_type = ! is_array( $_post_type ) ? $_post_type : 'any';

					$archive_view = get_theme_mod( $_post_type . '-archive-view' );
					$archive_view = $archive_view ? $archive_view : $_post_type;

					return array(
						'slug' => 'templates/view/archive',
						'name' => $archive_view,
					);
				}

				return array(
					'slug' => 'templates/view/no-match',
					'name' => '',
				);
			}
		);
	}
}

require_once SNOW_MONKEY_SEARCH_PATH . '/vendor/autoload.php';
new Bootstrap();

/**
 * Uninstall
 */
function snow_monkey_search_uninstall() {
	$posts = get_posts(
		array(
			'post_type'      => 'snow-monkey-search',
			'posts_per_page' => -1,
		)
	);

	foreach ( $posts as $post ) {
		wp_delete_post( $post->ID, true );
	}

	try {
		Directory::do_empty( Directory::get(), true );
	} catch ( \Exception $e ) {
		error_log( $e->getMessage() );
	}
}

/**
 * Register uninstall hook
 */
function snow_monkey_search_activate() {
	register_uninstall_hook( __FILE__, '\Snow_Monkey\Plugin\Search\snow_monkey_search_uninstall' );
}
register_activation_hook( __FILE__, '\Snow_Monkey\Plugin\Search\snow_monkey_search_activate' );
