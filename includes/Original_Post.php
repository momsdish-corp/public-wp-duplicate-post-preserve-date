<?php

namespace Momsdish\Duplicate_Post_Preserve_Date;

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

class Original_Post {
	private int $post_id;

	private false|int $rewrite_republish_post_id = false;

	private false|string $original_post_date = false;
	private false|string $original_post_date_gmt = false;

	public function __construct( int $post_id ) {
		$this->post_id = $post_id;
	}

	/**
	 * Get the original post date meta.
	 */
	public function get_original_post_date(): false|string {
		if ( $this->original_post_date ) {
			return $this->original_post_date;
		}

		// Get the meta value from the Rewrite & Republish Post if it exists.
		if ( $this->rewrite_republish_post_id ) {
			$this->original_post_date = get_post_meta( $this->rewrite_republish_post_id, '_dp_original_post_date', true );
		}

		return $this->original_post_date;
	}

	/**
	 * Get the original post date gmt meta.
	 */
	public function get_original_post_date_gmt(): false|string {
		if ( $this->original_post_date_gmt ) {
			return $this->original_post_date_gmt;
		}

		// Get the meta value from the Rewrite & Republish Post if it exists.
		if ( $this->rewrite_republish_post_id ) {
			$this->original_post_date_gmt = get_post_meta( $this->rewrite_republish_post_id, '_dp_original_post_date_gmt', true );
		}

		return $this->original_post_date_gmt;
	}

	/**
	 * Returns the Rewrite & Republish Post ID if the given Post is being rewritten.
	 *
	 * @return false|int The parent Post ID or '' if not found.
	 */
	public function get_rewrite_republish_post_id(): false|int {
		if ( $this->rewrite_republish_post_id ) {
			return $this->rewrite_republish_post_id;
		}

		// Support: Duplicate Post plugin
		$republish_post_id = get_post_meta( $this->post_id, '_dp_has_rewrite_republish_copy', true );

		$this->rewrite_republish_post_id = (int) $republish_post_id ?: false;

		return $this->rewrite_republish_post_id;
	}

	/**
	 * Check if this Post has a Rewrite & Republish Post.
	 *
	 * @return bool
	 */
	public function has_rewrite_republish(): bool {
		return false !== $this->get_rewrite_republish_post_id();
	}

}
