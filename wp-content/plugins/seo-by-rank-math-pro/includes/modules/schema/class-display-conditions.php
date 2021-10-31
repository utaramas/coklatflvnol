<?php
/**
 *  Outputs specific schema code from Schema Template
 *
 * @since      2.0.7
 * @package    RankMath
 * @subpackage RankMathPro
 * @author     MyThemeShop <admin@mythemeshop.com>
 */

namespace RankMathPro\Schema;

use RankMath\Traits\Hooker;
use RankMath\Schema\DB;

defined( 'ABSPATH' ) || exit;

/**
 * Display Conditions class.
 */
class Display_Conditions {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'rank_math/json_ld', 'add_template_schema', 11, 2 );
	}

	/**
	 * Get Default Schema Data.
	 *
	 * @param array  $data   Array of json-ld data.
	 * @param JsonLD $jsonld Instance of jsonld.
	 *
	 * @return array
	 */
	public function add_template_schema( $data, $jsonld ) {
		$schemas = $this->get_schema_templates( $data, $jsonld );
		if ( empty( $schemas ) ) {
			return $data;
		}

		foreach ( $schemas as $schema ) {
			$data = array_merge( $data, $schema );
		}

		return $data;
	}

	/**
	 * Get Schema data from Schema Templates post type.
	 *
	 * @param array  $data   Array of json-ld data.
	 * @param JsonLD $jsonld Instance of jsonld.
	 *
	 * @return array
	 */
	private function get_schema_templates( $data, $jsonld ) {
		$templates = get_posts(
			[
				'post_type'   => 'rank_math_schema',
				'numberposts' => -1,
				'fields'      => 'ids',
			]
		);

		if ( empty( $templates ) ) {
			return;
		}

		$newdata = [];
		foreach ( $templates as $template ) {
			$this->conditions = [];
			$schema           = DB::get_schemas( $template );
			if ( ! $this->can_add( current( $schema ) ) ) {
				continue;
			}

			$schema = $jsonld->replace_variables( $schema );
			$schema = $jsonld->filter( $schema, $jsonld, $data );

			$newdata[] = $schema;
		}

		return $newdata;
	}

	/**
	 * Whether schema can be added to current page
	 *
	 * @param array $schema Schema Data.
	 *
	 * @return boolean
	 */
	private function can_add( $schema ) {
		if ( empty( $schema ) || empty( $schema['metadata']['displayConditions'] ) ) {
			return false;
		}

		foreach ( $schema['metadata']['displayConditions'] as $condition ) {
			$operator = $condition['condition'];
			$category = $condition['category'];
			$type     = $condition['type'];
			$value    = $condition['value'];

			$method = "can_add_{$category}";

			$this->conditions[ $category ] = $this->$method( $operator, $type, $value );
		}

		if ( is_singular() && isset( $this->conditions['singular'] ) ) {
			return $this->conditions['singular'];
		}

		if ( ( is_archive() || is_search() ) && isset( $this->conditions['archive'] ) ) {
			return $this->conditions['archive'];
		}

		return ! empty( $this->conditions['general'] );
	}

	/**
	 * Whether schema can be added to current page
	 *
	 * @param string $operator Comparision Operator.
	 *
	 * @return boolean
	 */
	private function can_add_general( $operator ) {
		return 'include' === $operator;
	}

	/**
	 * Whether schema can be added on archive page
	 *
	 * @param string $operator Comparision Operator.
	 * @param string $type     Post/Taxonoy type.
	 * @param string $value    Post/Term ID.
	 *
	 * @return boolean
	 */
	private function can_add_archive( $operator, $type, $value ) {
		if ( 'search' === $type ) {
			return 'include' === $operator && is_search();
		}

		if ( ! is_archive() ) {
			return false;
		}

		if ( 'all' === $type ) {
			return 'include' === $operator;
		}

		if ( 'author' === $type ) {
			return is_author() && 'include' === $operator && is_author( $value );
		}

		if ( 'category' === $type ) {
			return ! is_category() ? $this->conditions['archive'] : 'include' === $operator && is_category( $value );
		}

		if ( 'post_tag' === $type ) {
			return ! is_tag() ? $this->conditions['archive'] : 'include' === $operator && is_tag( $value );
		}

		return 'include' === $operator && is_tax( $type, $value );
	}

	/**
	 * Whether schema can be added on single page
	 *
	 * @param string $operator Comparision Operator.
	 * @param string $type     Post/Taxonoy type.
	 * @param string $value    Post/Term ID.
	 *
	 * @return boolean
	 */
	private function can_add_singular( $operator, $type, $value ) {
		if ( ! is_singular() ) {
			return false;
		}

		if ( 'all' === $type ) {
			return 'include' === $operator;
		}

		if ( ! is_singular( $type ) ) {
			return false;
		}

		if ( ! $value ) {
			return 'include' === $operator;
		}

		if ( ! is_single( $value ) && ! is_page( $value ) ) {
			return ! empty( $this->conditions['singular'] );
		}

		return 'include' === $operator;
	}
}
