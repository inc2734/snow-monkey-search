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
		add_action( 'init', array( $this, '_register_post_type' ) );
		add_filter( 'block_categories_all', array( $this, '_block_categories' ) );
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
	}

	/**
	 * Register post type.
	 */
	public function _register_post_type() {
		register_post_type(
			'snow-monkey-search',
			array(
				'label'        => __( 'Snow Monkey Search', 'snow-monkey-search' ),
				'public'       => false,
				'show_ui'      => true,
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor', 'custom-fields' ),
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
