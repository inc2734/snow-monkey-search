<?php
/**
 * @package snow-monkey-search
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\Search\App;

class Query {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'pre_get_posts', array( $this, '_pre_get_posts_for_archive' ) );
		add_filter( 'get_post_status', array( $this, '_publish_future_posts' ), 10, 2 );
		add_action( 'pre_get_posts', array( $this, '_pre_get_posts_for_search' ) );
	}

	/**
	 * Future postings will also be included in the search.
	 *
	 * @param WP_Query $query The query.
	 */
	public function _pre_get_posts_for_archive( $query ) {
		if ( $query->is_main_query() && ! is_admin() ) {
			$post_type = static::is_archive( $query );
			if ( ! $post_type ) {
				return;
			}

			$includes_future_posts = $this->_includes_future_posts( $post_type );
			if ( ! $includes_future_posts ) {
				return false;
			}

			$post_status              = array( 'publish', 'future' );
			$queried_post_type_object = get_post_type_object( $post_type );

			// Add private states that are visible to current user.
			if ( is_user_logged_in() && $queried_post_type_object instanceof \WP_Post_Type ) {
				$read_private_cap = $queried_post_type_object->cap->read_private_posts;
				$private_statuses = get_post_stati( array( 'private' => true ) );
				foreach ( $private_statuses as $private_status ) {
					// @todo We want to display the private posts of a user even if that user does not have permissions to do so.
					if ( current_user_can( $read_private_cap ) ) {
						$post_status = array_merge(
							$post_status,
							array( $private_status ),
						);
					}
				}
			}

			$query->set( 'post_status', $post_status );
		}
	}

	/**
	 * Publish future posts of any post type.
	 *
	 * @param string $post_status The post status.
	 * @param WP_Post $post The post object.
	 * @return string
	 */
	public function _publish_future_posts( $post_status, $post ) {
		if ( ! is_singular() ) {
			return $post_status;
		}

		$includes_future_posts = $this->_includes_future_posts( $post->post_type );
		if ( ! $includes_future_posts ) {
			return $post_status;
		}

		if ( 'future' === $post_status ) {
			return 'publish';
		}

		return $post_status;
	}

	/**
	 * Set search query.
	 *
	 * @param WP_Query $query The query.
	 */
	public function _pre_get_posts_for_search( $query ) {
		if ( $query->is_main_query() && ! is_admin() && ! is_null( filter_input( INPUT_GET, 'snow-monkey-search' ) ) ) {
			$query->is_search = false;

			// Taxonomy query.
			$taxonomies = filter_input( INPUT_GET, 'sms-taxonomies', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			if ( $taxonomies ) {
				$tax_query = array();

				foreach ( $taxonomies as $taxonomy_name => $terms ) {
					if ( ! is_array( $terms ) ) {
						$terms = array();
					}

					$terms = array_filter(
						$terms,
						function ( $value ) {
							return '' !== $value && false !== $value && ! is_null( $value );
						}
					);

					if ( $terms ) {
						$tax_query[] = array(
							'taxonomy' => $taxonomy_name,
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

			// Meta query.
			$post_metas = filter_input( INPUT_GET, 'sms-post-meta', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			if ( $post_metas ) {
				$meta_query = array();

				foreach ( $post_metas as $meta_key => $meta_set ) {
					$meta_value = false;
					if ( isset( $meta_set['value'] ) && '' !== $meta_set['value'] && false !== $meta_set['value'] ) {
						$meta_value = $meta_set['value'];
					}
					if ( false === $meta_value ) {
						continue;
					}

					$meta_compare             = false;
					$available_compare_values = array(
						'=',
						'!=',
						'>',
						'>=',
						'<',
						'<=',
						'LIKE',
						'NOT LIKE',
					);
					if ( isset( $meta_set['compare'] ) && in_array( $meta_set['compare'], $available_compare_values, true ) ) {
						$meta_compare = $meta_set['compare'];
					}
					if ( false === $meta_compare ) {
						continue;
					}

					$meta_type             = false;
					$available_type_values = array(
						'numeric',
						'char',
						'date',
						'datetime',
						'time',
					);
					if ( isset( $meta_set['type'] ) && in_array( $meta_set['type'], $available_type_values, true ) ) {
						$meta_type = $meta_set['type'];
					}
					if ( false === $meta_type ) {
						continue;
					}

					if ( 'date' === $meta_type ) {
						$meta_value = gmdate( 'Y-m-d', strtotime( $meta_value ) );
					} elseif ( 'datetime' === $meta_type ) {
						$meta_value = gmdate( 'Y-m-d H:i:s', strtotime( $meta_value ) );
					} elseif ( 'time' === $meta_type ) {
						$meta_value = gmdate( 'H:i:s', strtotime( $meta_value ) );
					}

					if ( false !== $meta_value && $meta_compare && $meta_type ) {
						$meta_query[] = array(
							'key'     => $meta_key,
							'value'   => $meta_value,
							'compare' => $meta_compare,
							'type'    => $meta_type,
						);
					}
				}

				if ( $meta_query ) {
					$meta_query['relation'] = 'AND';

					$query->set( 'meta_query', $meta_query );
				}
			}

			// Period query.
			$period_start = filter_input( INPUT_GET, 'sms-period-start' );
			$period_end   = filter_input( INPUT_GET, 'sms-period-end' );
			if ( $period_start || $period_end ) {
				if ( preg_match( '|^\d{4}-\d{2}$|', $period_start ) ) {
					$date_time_immutable = new \DateTimeImmutable();
					$period_start        = $date_time_immutable->modify( 'first day of ' . $period_start )->format( 'Y-m-d' );
				}

				if ( preg_match( '|^\d{4}-\d{2}$|', $period_end ) ) {
					$date_time_immutable = new \DateTimeImmutable();
					$period_end          = $date_time_immutable->modify( 'last day of ' . $period_end )->format( 'Y-m-d' );
				}

				$valid_period_start = ! $period_start || false !== strtotime( $period_start );
				$valid_period_end   = ! $period_end || false !== strtotime( $period_end );

				if ( $valid_period_start && $valid_period_end ) {
					$date_query = array();

					if ( $period_start && ! $period_end ) {
						// Start only.
						$date_query = array(
							array(
								'inclusive' => true,
								'after'     => $period_start,
							),
						);
					} elseif ( ! $period_start && $period_end ) {
						// End only.
						$date_query = array(
							array(
								'inclusive' => true,
								'before'    => $period_end,
							),
						);
					} elseif ( $period_start && $period_end ) {
						// Both.
						$date_query = array(
							array(
								'compare'   => 'BETWEEN',
								'inclusive' => true,
								'after'     => $period_start,
								'before'    => $period_end,
							),
						);
					}

					if ( $date_query ) {
						$query->set( 'date_query', $date_query );
					}
				}
			}
		}
	}

	/**
	 * Returns true if future posts are also included.
	 *
	 * @param string $post_type Post type.
	 * @return boolean.
	 */
	protected function _includes_future_posts( $post_type ) {
		return apply_filters( 'sms_includes_future_posts', false, $post_type );
	}

	/**
	 * Return post type name if post type archive, tax, category, tag and home.
	 *
	 * @param WP_Query $query WP_Query.
	 * @return string|false.
	 */
	public static function is_archive( $query ) {
		$is_post_type_archive = $query->is_post_type_archive();
		$is_tax_ex            = $query->is_tax() || $query->is_category() || $query->is_tag();
		$is_home              = $query->is_home();

		if ( ! $is_post_type_archive && ! $is_tax_ex && ! $is_home ) {
			return false;
		}

		if ( $is_post_type_archive ) {
			return $query->get( 'post_type' );
		}

		if ( $is_home ) {
			return 'post';
		}

		if ( $is_tax_ex ) {
			$queried_object = $query->get_queried_object();
			$the_taxonomy   = get_taxonomy( $queried_object->taxonomy );

			return $the_taxonomy->object_type[0] ?? false;
		}

		return false;
	}
}
