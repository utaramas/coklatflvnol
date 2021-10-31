<?php
/**
 * The Video Schema.
 *
 * @since      1.0
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMathPro\Schema;

use RankMath\KB;
use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Schema\DB;
use MyThemeShop\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Video class.
 */
class Video {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			$this->action( 'save_post', 'save_post', 10, 2 );
			$this->filter( 'rank_math/admin/settings/others', 'add_media_rss_field' );

			if ( version_compare( rank_math()->version, '1.0.54', '>=' ) ) {
				foreach ( Helper::get_accessible_post_types() as $post_type ) {
					$this->filter( "rank_math/admin/settings/post-type-{$post_type}", 'add_video_schema_fields', 10, 2 );
				}
			}

			return;
		}

		$this->action( 'rank_math/opengraph/facebook', 'add_video_tags', 99 );
		new Media_RSS();
	}

	/**
	 * Add auto-generate video schema settings.
	 *
	 * @param object $cmb CMB2 instance.
	 * @param array  $tab Current settings tab.
	 */
	public function add_video_schema_fields( $cmb, $tab ) {
		if ( 'attachment' === $tab['post_type'] ) {
			return;
		}

		$field_ids      = wp_list_pluck( $cmb->prop( 'fields' ), 'id' );
		$field_position = array_search( "pt_{$tab['post_type']}_default_article_type", array_keys( $field_ids ), true ) + 1;

		$cmb->add_field(
			[
				'id'      => 'pt_' . $tab['post_type'] . '_autodetect_video',
				'type'    => 'toggle',
				'name'    => esc_html__( 'Autodetect Video', 'rank-math-pro' ),
				'desc'    => esc_html__( 'Populate automatic Video Schema by auto-detecting any video in the content.', 'rank-math-pro' ),
				'options' => [
					'off' => esc_html__( 'Default', 'rank-math-pro' ),
					'on'  => esc_html__( 'Custom', 'rank-math-pro' ),
				],
				'default' => 'on',
			],
			++$field_position
		);

		$cmb->add_field(
			[
				'id'      => 'pt_' . $tab['post_type'] . '_autogenerate_image',
				'type'    => 'toggle',
				'name'    => esc_html__( 'Autogenerate Image', 'rank-math-pro' ),
				'desc'    => esc_html__( 'Auto-generate image for the auto detected video.', 'rank-math-pro' ),
				'options' => [
					'off' => esc_html__( 'Default', 'rank-math-pro' ),
					'on'  => esc_html__( 'Custom', 'rank-math-pro' ),
				],
				'default' => 'on',
				'dep'     => [ [ 'pt_' . $tab['post_type'] . '_autodetect_video', 'on' ] ],
			],
			++$field_position
		);
	}

	/**
	 * Add new settings.
	 *
	 * @param object $cmb CMB2 instance.
	 */
	public function add_media_rss_field( $cmb ) {
		$field_ids      = wp_list_pluck( $cmb->prop( 'fields' ), 'id' );
		$field_position = array_search( 'rss_after_content', array_keys( $field_ids ), true ) + 1;

		$cmb->add_field(
			[
				'id'      => 'disable_media_rss',
				'type'    => 'toggle',
				'name'    => esc_html__( 'Remove Media Data from RSS feed', 'rank-math-pro' ),
				'desc'    => esc_html__( 'Remove Media Data from RSS feed', 'rank-math-pro' ),
				'options' => [
					'off' => esc_html__( 'Default', 'rank-math-pro' ),
					'on'  => esc_html__( 'Custom', 'rank-math-pro' ),
				],
				'default' => 'off',
			],
			++$field_position
		);
	}

	/**
	 * Output the video tags.
	 *
	 * @link https://yandex.com/support/video/partners/open-graph.html#player
	 *
	 * @param OpenGraph $opengraph The current opengraph network object.
	 */
	public function add_video_tags( $opengraph ) {
		if ( ! is_singular() ) {
			return;
		}

		global $post;
		$video_data = get_post_meta( $post->ID, 'rank_math_schema_VideoObject', true );
		if ( empty( $video_data ) ) {
			return;
		}

		$tags = [
			'og:video'           => ! empty( $video_data['contentUrl'] ) ? $video_data['contentUrl'] : ( ! empty( $video_data['embedUrl'] ) ? $video_data['embedUrl'] : '' ),
			'og:video:duration'  => ! empty( $video_data['duration'] ) ? Helper::duration_to_seconds( $video_data['duration'] ) : '',
			'ya:ovs:adult'       => ! empty( $video_data['isFamilyFriendly'] ) ? false : true,
			'ya:ovs:upload_date' => ! empty( $video_data['uploadDate'] ) ? Helper::replace_vars( $video_data['uploadDate'], $post ) : '',
			'ya:ovs:allow_embed' => ! empty( $video_data['embedUrl'] ) ? 'true' : 'false',
		];

		foreach ( $tags as $tag => $value ) {
			$opengraph->tag( $tag, $value );
		}
	}

	/**
	 * Automatically add Video Schema when post is updated.
	 *
	 * @param  int    $post_id Post id.
	 * @param  object $post    Post object.
	 */
	public function save_post( $post_id, $post ) {
		( new Video\Parser( $post ) )->save();
	}
}
