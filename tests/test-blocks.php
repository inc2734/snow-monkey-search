<?php
/**
 * Regression tests for built search blocks.
 *
 * @package snow-monkey-search
 */

class Test_Search_Blocks extends WP_UnitTestCase {

	/**
	 * Register the plugin's blocks independently of the active theme.
	 */
	public function set_up() {
		parent::set_up();

		$register = new \Snow_Monkey\Plugin\Search\App\Register();
		$register->_register_post_types();
		if ( ! WP_Block_Type_Registry::get_instance()->is_registered( 'snow-monkey-search/search-box' ) ) {
			$register->_register_blocks();
		}
	}

	/**
	 * Verify that the production build contains every registered editor asset.
	 */
	public function test_built_blocks_register_editor_assets() {
		$names = array( 'custom-field-search', 'item', 'keyword-search', 'period-search', 'search-box', 'snow-monkey-search', 'taxonomy-search' );
		foreach ( $names as $name ) {
			$block = WP_Block_Type_Registry::get_instance()->get_registered( 'snow-monkey-search/' . $name );
			$this->assertInstanceOf( WP_Block_Type::class, $block, $name );
			$this->assertSame( 3, $block->api_version );
			$this->assertNotEmpty( $block->editor_script_handles );
			foreach ( $block->editor_script_handles as $handle ) {
				$this->assertTrue( wp_script_is( $handle, 'registered' ), $handle );
			}
			$this->assertFileExists( SNOW_MONKEY_SEARCH_PATH . '/dist/blocks/' . $name . '/index.js' );
			$this->assertFileExists( SNOW_MONKEY_SEARCH_PATH . '/dist/blocks/' . $name . '/index.asset.php' );
		}
	}

	/**
	 * Preserve nested block attributes when saving and rendering a search form.
	 */
	public function test_saved_search_form_round_trip() {
		$content = '<!-- wp:snow-monkey-search/search-box {"relatedPostType":"post"} -->'
			. '<!-- wp:snow-monkey-search/item --><div class="sms-item">'
			. '<!-- wp:snow-monkey-search/keyword-search {"label":"Find posts","placeholder":"Search here"} /-->'
			. '</div><!-- /wp:snow-monkey-search/item -->'
			. '<!-- /wp:snow-monkey-search/search-box -->';
		$post_id = self::factory()->post->create(
			array(
				'post_type'    => 'snow-monkey-search',
				'post_status'  => 'publish',
				'post_content' => $content,
			)
		);
		$saved   = get_post_field( 'post_content', $post_id );
		$blocks  = parse_blocks( $saved );

		$this->assertSame( $content, $saved );
		$this->assertSame( 'Search here', $blocks[0]['innerBlocks'][0]['innerBlocks'][0]['attrs']['placeholder'] );
		$this->assertSame( $saved, serialize_blocks( $blocks ) );

		$html = do_blocks( $saved );
		$this->assertStringContainsString( '<form', $html );
		$this->assertStringContainsString( 'method="get"', $html );
		$this->assertStringContainsString( 'name="post_type" value="post"', $html );
		$this->assertStringContainsString( 'name="snow-monkey-search"', $html );
		$this->assertStringContainsString( 'placeholder="Search here"', $html );
		$this->assertStringContainsString( 'type="submit"', $html );
	}

	/**
	 * Escape attribute values while retaining allowed label markup.
	 */
	public function test_keyword_search_escapes_placeholder() {
		$html = render_block(
			array(
				'blockName' => 'snow-monkey-search/keyword-search',
				'attrs'     => array(
					'label'       => '<em>Keyword</em>',
					'placeholder' => '" onfocus="alert(1)',
				),
			)
		);

		$this->assertStringContainsString( '<em>Keyword</em>', $html );
		$this->assertStringContainsString( 'placeholder="&quot; onfocus=&quot;alert(1)"', $html );
		$this->assertStringNotContainsString( 'placeholder="" onfocus=', $html );
	}

	/**
	 * Keep custom-field option values and query metadata in the rendered form.
	 */
	public function test_custom_field_search_renders_options() {
		$html = render_block(
			array(
				'blockName' => 'snow-monkey-search/custom-field-search',
				'attrs'     => array(
					'label'       => 'Color',
					'key'         => 'color',
					'controlType' => 'select',
					'options'     => "\"red\":\"Red\"\n\"blue\":\"Blue\"",
				),
			)
		);

		$this->assertStringContainsString( 'name="sms-post-meta[color][value]"', $html );
		$this->assertStringContainsString( 'value="red"', $html );
		$this->assertStringContainsString( 'value="blue"', $html );
		$this->assertStringContainsString( 'name="sms-post-meta[color][compare]" value="="', $html );
		$this->assertStringContainsString( 'name="sms-post-meta[color][type]" value="char"', $html );
	}

	/**
	 * Keep valid date bounds in both period controls.
	 */
	public function test_period_search_renders_date_bounds() {
		$html = render_block(
			array(
				'blockName' => 'snow-monkey-search/period-search',
				'attrs'     => array(
					'label' => 'Period',
					'min'   => '2026-01-01',
					'max'   => '2026-12-31',
				),
			)
		);

		$this->assertStringContainsString( 'name="sms-period-start"', $html );
		$this->assertStringContainsString( 'name="sms-period-end"', $html );
		$this->assertStringContainsString( 'min="2026-01-01"', $html );
		$this->assertStringContainsString( 'max="2026-12-31"', $html );
	}

	/**
	 * Render populated taxonomy terms as filter controls.
	 */
	public function test_taxonomy_search_renders_terms() {
		$term_id = self::factory()->term->create( array( 'taxonomy' => 'category', 'slug' => 'regression-category' ) );
		$post_id = self::factory()->post->create();
		wp_set_post_terms( $post_id, array( $term_id ), 'category' );

		$html = render_block(
			array(
				'blockName' => 'snow-monkey-search/taxonomy-search',
				'attrs'     => array(
					'label'    => 'Category',
					'taxonomy' => 'category',
				),
			)
		);

		$this->assertStringContainsString( 'name="sms-taxonomies[category][]"', $html );
		$this->assertStringContainsString( 'value="regression-category"', $html );
	}

	/**
	 * An unconfigured reusable form must not display unrelated content.
	 */
	public function test_unconfigured_form_renders_nothing() {
		$this->assertSame( '', do_blocks( '<!-- wp:snow-monkey-search/snow-monkey-search /-->' ) );
	}
}
