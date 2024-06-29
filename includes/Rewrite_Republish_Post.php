<?php

namespace Momsdish\Duplicate_Post_Preserve_Date;

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

class Rewrite_Republish_Post {
	private int $post_id;

	private false|int $original_post_id = false;

	private false|string $original_post_date = false;
	private false|string $original_post_date_gmt = false;

	public function __construct( int $post_id ) {
		$this->post_id = $post_id;
	}


	/**
	 * Returns the original Post ID if the given post is a Rewrite & Republish. Otherwise returns false.
	 *
	 * @return false|int The parent Post ID or '' if not found.
	 */
	public function get_original_post_id(): false|int {
		if ( $this->original_post_id ) {
			return $this->original_post_id;
		}

		$original_entity_id = get_post_meta( $this->post_id, '_dp_original', true );

		// If the post has just been created, parent post id will exist in the URL query
		if ( ! $original_entity_id && isset( $_GET['action'], $_GET['post'] ) && 'duplicate_post_rewrite' === $_GET['action'] && is_numeric( $_GET['post'] ) ) {
			$original_entity_id = $_GET['post'];
		}

		$this->original_post_id = (int) $original_entity_id ?: false;

		return $this->original_post_id;
	}

	/**
	 * Get the original post date meta.
	 */
	public function get_original_post_date(): false|string {
		if ( $this->original_post_date ) {
			return $this->original_post_date;
		}

		$this->original_post_date = get_post_meta( $this->post_id, '_dp_original_post_date', true );

		return $this->original_post_date;
	}

	/**
	 * Get the original post date gmt meta.
	 */
	public function get_original_post_date_gmt(): false|string {
		if ( $this->original_post_date_gmt ) {
			return $this->original_post_date_gmt;
		}

		$this->original_post_date_gmt = get_post_meta( $this->post_id, '_dp_original_post_date_gmt', true );

		return $this->original_post_date_gmt;
	}


	/**
	 * Add the original post date to the post meta.
	 */
	public function insert_original_post_date_to_meta(): void {
		// If Post Meta doesn't have the original post date, add it.
		if ( ! $this->get_original_post_date() && $this->get_original_post_id() ) {
			$original_post = get_post( $this->get_original_post_id() );
			if ( $original_post ) {
				$this->original_post_date = $original_post->post_date;
				add_post_meta( $this->post_id, '_dp_original_post_date', $original_post->post_date );
			}
		}

		// If Post Meta doesn't have the original post date gmt, add it.
		if ( ! $this->get_original_post_date_gmt() && $this->get_original_post_id() ) {
			$original_post = get_post( $this->get_original_post_id() );
			if ( $original_post ) {
				$this->original_post_date_gmt = $original_post->post_date_gmt;
				add_post_meta( $this->post_id, '_dp_original_post_date_gmt', $original_post->post_date_gmt );
			}
		}
	}

	/**
	 * Check if this is the Rewrite & Republish Post (not the original post).
	 *
	 * @return bool
	 */
	public function is_rewrite_republish(): bool {
		return false !== $this->get_original_post_id();
	}


}
