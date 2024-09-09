<?php
/**
 * Plugin name: Snow Monkey Search
 * Version: 0.2.0
 * Description: This plugin places a filtered search form in Snow Monkey's custom post archives.
 * Author: inc2734
 * Author URI: https://2inc.org
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: snow-monkey-search
 * Requires at least: 6.6
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * Requires Snow Monkey: 26.0.0
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

		new App\Updater();

		$theme = wp_get_theme( get_template() );
		if ( 'snow-monkey' !== $theme->template ) {
			add_action(
				'admin_notices',
				function () {
					?>
					<div class="notice notice-warning is-dismissible">
						<p>
							<?php esc_html_e( '[Snow Monkey Search] Needs the Snow Monkey.', 'snow-monkey-search' ); ?>
						</p>
					</div>
					<?php
				}
			);
			return;
		}

		$data = get_file_data(
			__FILE__,
			array(
				'RequiresSnowMonkey' => 'Requires Snow Monkey',
			)
		);

		if (
			isset( $data['RequiresSnowMonkey'] ) &&
			version_compare( $theme->get( 'Version' ), $data['RequiresSnowMonkey'], '<' )
		) {
			add_action(
				'admin_notices',
				function () use ( $data ) {
					?>
					<div class="notice notice-warning is-dismissible">
						<p>
							<?php
							echo esc_html(
								sprintf(
									// translators: %1$s: version.
									__(
										'[Snow Monkey Search] Needs the Snow Monkey %1$s or more.',
										'snow-monkey-search'
									),
									'v' . $data['RequiresSnowMonkey']
								)
							);
							?>
						</p>
					</div>
					<?php
				}
			);
			return;
		}

		add_action( 'snow_monkey_prepend_archive_entry_content', array( $this, '_display_search_box_to_main' ) );
		add_action( 'snow_monkey_prepend_sidebar', array( $this, '_display_search_box_to_sidebar' ) );
		add_action( 'wp', array( $this, '_update_view' ) );
		add_action( 'template_redirect', array( $this, '_template_redirect' ) );

		new App\Rest();
		new App\Register();
		new App\Query();
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
	 * Display search box to main area.
	 */
	public function _display_search_box_to_main() {
		global $wp_query;

		$post_type = App\Query::is_archive( $wp_query );
		$is_search = App\Query::is_search( $wp_query );
		if ( ! $post_type && ! $is_search ) {
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
						'value'   => $post_type ? $post_type : 'post',
						'compare' => 'IN',
					),
					array(
						'key'     => 'sms_display_area',
						'value'   => 'main',
						'compare' => '=',
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
	 * Display search box to sidebar area.
	 */
	public function _display_search_box_to_sidebar() {
		global $wp_query;

		$post_type = App\Query::is_archive( $wp_query );
		$is_search = App\Query::is_search( $wp_query );
		if ( ! $post_type && ! $is_search ) {
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
						'value'   => $post_type ? $post_type : 'post',
						'compare' => 'IN',
					),
					array(
						'key'     => 'sms_display_area',
						'value'   => 'sidebar',
						'compare' => '=',
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
			function ( $view ) {
				if ( is_home() ) {
					return $view;
				}

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

				return $view;
			}
		);
	}

	/**
	 * If the paging destination does not exist, redirect to the first page.
	 */
	public function _template_redirect() {
		global $wp_query;

		if ( is_null( filter_input( INPUT_GET, 'snow-monkey-search' ) ) ) {
			return;
		}

		if ( is_404() && 1 < get_query_var( 'paged' ) ) {
			$request_uri = wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( $request_uri ) {
				$sub_directory = parse_url( $home_url, PHP_URL_PATH ) ?? '';
				$absolute_path = preg_replace( '|^' . preg_quote( $sub_directory ) . '|', '', $request_uri );
				$redirect      = untrailingslashit( $home_url ) . $absolute_path;
				$redirect      = preg_replace( '|/page/\d+|', '', $redirect );
				$redirect      = preg_replace( '|paged=\d+|', '', $redirect );

				wp_safe_redirect( $redirect );
				exit;
			}
		}
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
}

/**
 * Register uninstall hook
 */
function snow_monkey_search_activate() {
	register_uninstall_hook( __FILE__, '\Snow_Monkey\Plugin\Search\snow_monkey_search_uninstall' );
}
register_activation_hook( __FILE__, '\Snow_Monkey\Plugin\Search\snow_monkey_search_activate' );
