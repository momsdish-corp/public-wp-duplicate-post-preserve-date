<?php
/**
 * Plugin Name:     Yoast Duplicate Post - Preserve Date
 * Description:     Stops Yoast Duplicate Post plugin from overwriting the original publish date.
 * Plugin URI:      https://github.com/momsdish-corp/public-yoast-duplicate-preserve-date
 * Author:          Momsdish
 * Author URI:      https://github.com/momsdish-corp
 * Text Domain:     duplicate-post-preserve-date
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Momsdish
 */

define( 'DUPLICATE_POST_PRESERVE_DATE_DIR', __DIR__ );
define( 'DUPLICATE_POST_PRESERVE_DATE_FILE', __FILE__ );
define( 'DUPLICATE_POST_PRESERVE_DATE_URL', plugin_dir_url( __FILE__ ) );
define( 'DUPLICATE_POST_PRESERVE_DATE_TEXT_DOMAIN', 'x-alpha' );

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

require_once DUPLICATE_POST_PRESERVE_DATE_DIR . '/includes/Original_Post.php';
require_once DUPLICATE_POST_PRESERVE_DATE_DIR . '/includes/Rewrite_Republish_Post.php';

add_action( 'save_post', function ( $post_id, $post, $update ) {
	$Rewrite_Republish_Post = new Momsdish\Duplicate_Post_Preserve_Date\Rewrite_Republish_Post( $post_id );
	if ( $Rewrite_Republish_Post->is_rewrite_republish() ) {
		// If Post Meta doesn't have the original post date, add it.
		// We use this meta value to restore the original date on the post when Duplicate Post overrides it.
		$Rewrite_Republish_Post->insert_original_post_date_to_meta();
	}
}, 10, 3 );

add_action( 'publish_post', function ( $post_id, $post ) {
	$Original_Post = new Momsdish\Duplicate_Post_Preserve_Date\Original_Post( $post_id );
	// If this post is being rewritten/republished by Duplicate Post & the rewrite/republish post has the original post
	// date in the meta, restore the original post date.
	if ( $Original_Post->has_rewrite_republish() && $Original_Post->get_original_post_date_gmt() ) {
		$original_post_date_gmt = $Original_Post->get_original_post_date_gmt();
		// If the original post date is different from the current post date, update the post date.
		if ( $original_post_date_gmt !== $post->post_date_gmt ) {
			remove_action( 'publish_post', __FUNCTION__ );
			wp_update_post( array(
				'ID' => $post_id,
				'post_date'     => get_date_from_gmt( $original_post_date_gmt, 'Y-m-d H:i:s' ),
				'post_date_gmt' => $original_post_date_gmt,
			) );
			add_action( 'publish_post', __FUNCTION__, 10, 2 );
		}
	}
}, 10, 2 );



