<?php
/**
 * Extension for BuddyStream
 *
 * This file extends Achievements to support actions from BuddyStream
 *
 * @package Achievements
 * @subpackage ExtensionBuddyStream
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extends Achievements to support actions from BuddyStream.
 *
 * @since 3.0
 */
function dpa_init_buddystream_extension() {
	achievements()->extensions->buddystream = new DPA_BuddyStream_Extension;

	// Run an action for other plugins
	do_action( 'dpa_init_buddystream_extension' );
}
add_action( 'dpa_ready', 'dpa_init_buddystream_extension' );

/**
 * Extension to add BuddyStream support to Achievements
 *
 * @since 3.0
 */
class DPA_BuddyStream_Extension extends DPA_Extension {
	/**
	 * Constructor
	 *
	 * Sets up extension properties. See class phpdoc for details.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		$this->actions = array(
			'buddystream_facebook_activated' => __( 'The user connects their Facebook account to BuddyStream.', 'dpa' ),
			'buddystream_flickr_activated'   => __( 'The user connects their Flickr account to BuddyStream.', 'dpa' ),
			'buddystream_lastfm_activated'   => __( 'The user connects their Last.fm account to BuddyStream.', 'dpa' ),
			'buddystream_twitter_activated'  => __( 'The user connects their Twitter account to BuddyStream.', 'dpa' ),
			'buddystream_youtube_activated'  => __( 'The user connects their YouTube account to BuddyStream.', 'dpa' ),
		);

		$this->contributors = array(
			array(
				'name'         => 'Peter Hofman',
				'gravatar_url' => 'http://www.gravatar.com/avatar/fa62da3fa8b3997be04448e7280dad29',
				'profile_url'  => 'http://profiles.wordpress.org/blackphantom/',
			)
		);

		$this->description = __( 'BuddyStream is a BuddyPress plugin that will synchronize all of your favorite Social Networks to the BuddyPress activity stream.', 'dpa' );
		$this->id          = 'BuddyStream';
		$this->image_url   = 'http://placekitten.com/772/250';
		$this->name        = __( 'BuddyStream', 'dpa' );
		$this->rss_url     = 'http://buddystream.net/blog/feed/';
		$this->version     = 1;
		$this->wporg_url   = 'http://wordpress.org/extend/plugins/buddystream/';
	}
}