<?php
/**
 * Plugin Name:         Yoast Duplicate Post - Preserve Date
 * Description:         Stops Yoast Duplicate Post plugin from overwriting the original publish date.
 * Plugin URI:          https://github.com/momsdish-corp/public-yoast-duplicate-preserve-date
 * Author:              Momsdish
 * Author URI:          https://github.com/momsdish-corp
 * Requires PHP:        8.1
 * Requires at least:   6.5
 * Text Domain:         duplicate-post-preserve-date
 * Domain Path:         /languages
 * Version:             0.2.2
 *
 * @package             Momsdish
 */

define( 'DUPLICATE_POST_PRESERVE_DATE_DIR', __DIR__ );
define( 'DUPLICATE_POST_PRESERVE_DATE_FILE', __FILE__ );
define( 'DUPLICATE_POST_PRESERVE_DATE_URL', plugin_dir_url( __FILE__ ) );
define( 'DUPLICATE_POST_PRESERVE_DATE_TEXT_DOMAIN', 'duplicate-post-preserve-date' );

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

require_once DUPLICATE_POST_PRESERVE_DATE_DIR . '/includes/Original_Post.php';
require_once DUPLICATE_POST_PRESERVE_DATE_DIR . '/includes/Rewrite_Republish_Post.php';

// Enqueue the plugin's assets.
add_action( 'enqueue_block_editor_assets', function () {
	wp_enqueue_script(
		'duplicate-post-preserve-date-script',
		plugin_dir_url( DUPLICATE_POST_PRESERVE_DATE_FILE ) . 'build/index.js',
		array( 'wp-plugins', 'wp-edit-post', 'wp-element' ),
		filemtime( plugin_dir_path( DUPLICATE_POST_PRESERVE_DATE_FILE ) . 'build/index.js' ),
		true
	);
} );

// On save_post, if the post is being rewritten/republished by Duplicate Post, add the original post date to the post meta.
add_action( 'save_post', function ( $post_id, $post, $update ) {
	$Rewrite_Republish_Post = new Momsdish\Duplicate_Post_Preserve_Date\Rewrite_Republish_Post( $post_id );
	if ( $Rewrite_Republish_Post->is_rewrite_republish() ) {
		// If Post Meta doesn't have the original post date, add it.
		// We use this meta value to restore the original date on the post when Duplicate Post overrides it.
		$Rewrite_Republish_Post->insert_original_post_date_to_meta();
	}
}, 10, 3 );

// On publish_post, if the post is being rewritten/republished, restore the original post date & delete the copied post meta.
add_action( 'publish_post', function ( $post_id, $post ) {

	$Original_Post = new Momsdish\Duplicate_Post_Preserve_Date\Original_Post( $post_id );
	// If this post is being rewritten/republished by Duplicate Post & the rewrite/republish post has the original post
	// date in the meta, restore the original post date.
	if ( $Original_Post->has_rewrite_republish() && $Original_Post->get_original_post_date() && $Original_Post->get_original_post_date_gmt() ) {
		$original_post_date = $Original_Post->get_original_post_date();
		$original_post_date_gmt = $Original_Post->get_original_post_date_gmt();
		// Delete the copied post meta.
		$Original_Post->delete_copied_post_meta();
		// If the original post date is different from the current post date, update the post date.
		if ( ( $original_post_date !== $post->post_date ) || ( $original_post_date_gmt !== $post->post_date_gmt ) ) {
			remove_action( 'publish_post', __FUNCTION__ );
			wp_update_post( array(
				'ID'            => $post_id,
				'post_date'     => $original_post_date,
				'post_date_gmt' => $original_post_date_gmt,
			) );
			add_action( 'publish_post', __FUNCTION__, 10, 2 );
		}
	}
}, 10, 2 );

// Show _dp_original_post_date in the REST API.
add_action( 'init', function () {
	register_post_meta( 'post', '_dp_original_post_date', array(
		'show_in_rest' => true,
		'single'       => true,
		'type'         => 'string',
	) );
} );
