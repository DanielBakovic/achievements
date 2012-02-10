<?php
/**
 * Achievements Admin screen functions
 *
 * Common or post type/taxonomy-specific customisations.
 *
 * @package Achievements
 * @subpackage Administration
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Filter sample permalinks so that certain languages display properly.
 *
 * @param string $post_link Custom post type permalink
 * @param object $post Post data object
 * @param bool $leavename Optional, defaults to false. Whether to keep post name or page name.
 * @param bool $sample Optional, defaults to false. Is it a sample permalink.
 * @return string The custom post type permalink
 * @since 3.0
 */
function dpa_filter_sample_permalink( $post_link, $post, $leavename, $sample ) {
	// Bail if not on an admin page and not getting a sample permalink
	if ( ! empty( $sample ) && is_admin() && dpa_is_custom_post_type() )
		return urldecode( $post_link );

	// Return post link
	return $post_link;
}

/**
 * Placeholder function to return a list of 3rd party plugins that are
 * supported by Achievements. Likely to change as plugin structure is
 * developed.
 *
 * Returned array consists of nested arrays and objects, one for each
 * supported plugin. Structure is:
 *
 * ['plugin_ID']->contributors *        Array of key/value pairs for each contributor (User name gravatar URL)
 * ['plugin_ID']->name         *        Plugin name
 * ['plugin_ID']->description  *        Plugin description
 * ['plugin_ID']->image->large          URL to plugin image (large size)
 * ['plugin_ID']->rating       *        1.0-5.0 plugin rating from wporg
 * ['plugin_ID']->wporg_url             wporg/extends URL page for this plugin
 *
 * Properties marked with an asterisk are auto-updated periodically from wporg.
 *
 * @global achievements $achievements Main Achievements object
 * @return array See function description for structure of returned array
 * @since 1.0
 * @todo Figure out how to handle 3rd party plugins adding their own support for Achievements.
 */
function dpa_get_supported_plugins() {
	global $achievements;

	// If we can't communicate with wporg, fall back to this set of data for the plugins
	$plugins_fallback = array(
		'bbpress'               => array( 'name' => __( 'bbPress', 'dpa' ), 'description' => __( "bbPress is forum software with a twist from the creators of WordPress", 'dpa' ) ),
		'bp-gtm-system'         => array( 'name' => __( 'BP GTM System', 'dpa' ), 'description' => __( "BP GTM System will turn your site into a developer center, where tasks, projects, discussions, categories and tags will help you maintain products.", 'dpa' ) ),
		'buddypress'            => array( 'name' => __( 'BuddyPress', 'dpa' ), 'description' => __( "Social networking in a box. Build a social network for your company, school, sports team or niche community.", 'dpa' ) ),
		'buddypress-courseware' => array( 'name' => __( 'BuddyPress ScholarPress Courseware', 'dpa' ), 'description' => __( "A Learning Management System for BuddyPress", 'dpa' ) ),
		'buddypress-docs'       => array( 'name' => __( 'BuddyPress Docs', 'dpa' ), 'description' => __( "Adds collaborative Docs to BuddyPress.", 'dpa' ) ),
		'buddystream'           => array( 'name' => __( 'BuddyStream', 'dpa' ), 'description' => __( "BuddyStream is a BuddyPress plugin that will synchronize all of your favorite Social Networks to the BuddyPress activity stream.", 'dpa' ) ),
		'invite-anyone'         => array( 'name' => __( 'Invite Anyone', 'dpa' ), 'description' => __( "Makes BuddyPress's invitation features more powerful.", 'dpa' ) ),
		'wp-e-commerce'         => array( 'name' => __( 'WP e-Commerce', 'dpa' ), 'description' => __( "WP e-Commerce is a free WordPress Shopping Cart Plugin that lets customers buy your products, services and digital downloads online.", 'dpa' ) ),
	);

	$plugins = array();
	require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

	// Fetch information for each plugin
	foreach ( $plugins_fallback as $slug => $plugin_data ) {
		// See if we have this data in cache
		$data = get_transient( 'dpa_plugin_' . $slug );

		// Not cached, so get data
		if ( ! $data ) {
			// Build fallback before querying wporg
			$plugin               = new stdClass;
			$plugin->contributors = array();
			$plugin->name         = $plugin_data['name'];
			$plugin->description  = $plugin_data['description'];
			$plugin->image->large = esc_url( $achievements->plugin_url . 'images/' . $slug . '.png' );
			$plugin->rating       = 0.0;
			$plugin->wporg_url    = esc_url( 'http://wordpress.org/extend/plugins/' . $slug );

			// Query wporg
			$wporg = plugins_api( 'plugin_information', array( 'slug' => $slug, 'fields' => array( 'short_description' => true ), ) );

			// Run a query to allow third parties to filter results from wporg
			$wporg = apply_filters( 'dpa_get_supported_plugins_wporg', $wporg, $slug, $plugin_data );

			// Overwrite the fallback data with new values from wporg
			if ( ! is_wp_error( $wporg ) ) {
				$plugin->contributors = (array) $wporg->contributors;
				$plugin->name         = $wporg->name;
				$plugin->description  = $wporg->short_description;
				$plugin->rating       = floatval( 0.05 * $wporg->rating );
			}

			// Convert wporg contributors data into profiles.wporg URL and Gravatar.
			$plugin->contributors = dpa_supported_plugins_get_contributor_data( $plugin->contributors );

			// Cache data for 72 hours
			set_transient( 'dpa_plugin_' . $slug, $plugin, 60 * 60 * 24 * 3 );

			$data = $plugin;
		}

		$plugins[] = $data;
	}
	
	return apply_filters( 'dpa_get_supported_plugins', $plugins );
}

/**
 * Get profiles.wporg URL and Gravatar from a plugin's raw contributors data, from plugins_api().
 *
 * @param array $contributors Raw data for a plugin's contributors, from plugins_api()
 * @return array Consists of nested arrays of [contributor name, profileswp.org URL, Gravatar URL]
 * @since 3.0
 * @todo Figure out how to handle 3rd party plugins adding their own support for Achievements.
 */
function dpa_supported_plugins_get_contributor_data( $raw_contributors ) {
	// We don't know email addresses for Gravatars, so use lookup table
	$people = array(
		'apeatling'             => 'http://www.gravatar.com/avatar/bb29d699b5cba218c313b61aa82249da?s=24&d=monsterid&r=g',
		'Blackphantom'          => 'http://www.gravatar.com/avatar/fa62da3fa8b3997be04448e7280dad29?s=24&d=monsterid&r=g',
		'boonebgorges'          => 'http://www.gravatar.com/avatar/9cf7c4541a582729a5fc7ae484786c0c?s=24&d=monsterid&r=g',
		'chexee'                => 'http://www.gravatar.com/avatar/0231c6b98cf90defe76bdad0c3c66acf?s=24&d=monsterid&r=g',
		'cuny-academic-commons' => 'http://www.gravatar.com/avatar/80c3fc801559bbc7111d5e3f56ac6a4c?s=24&d=monsterid&r=g',
		'DJPaul'                => 'http://www.gravatar.com/avatar/3bc9ab796299d67ce83dceb9554f75df?s=24&d=monsterid&r=g',
		'garyc40'               => 'http://www.gravatar.com/avatar/aea5ee57d1e882ad17e95c99265784d1?s=24&d=monsterid&r=g',
		'jeremyboggs'           => 'http://www.gravatar.com/avatar/2a062a10cb94152f4ab3daf569af54c3?s=24&d=monsterid&r=g',
		'jghazally'             => 'http://www.gravatar.com/avatar/e20e6b90f2f3862025ede5bf90b80a3d?s=24&d=monsterid&r=g',
		'johnjamesjacoby'       => 'http://www.gravatar.com/avatar/81ec16063d89b162d55efe72165c105f?s=24&d=monsterid&r=g',
		'matt'                  => 'http://www.gravatar.com/avatar/767fc9c115a1b989744c755db47feb60?s=24&d=monsterid&r=g',
		'MrMaz'                 => 'http://www.gravatar.com/avatar/a32efc5efefecb3fb1ef1149e23a077c?s=24&d=monsterid&r=g',
		'mufasa'                => 'http://www.gravatar.com/avatar/5ba89a2ce585864ce73cafa7e79d114c?s=24&d=monsterid&r=g',
		'mychelle'              => 'http://www.gravatar.com/avatar/da623a80bd7d7ded418c528a689520a3?s=24&d=monsterid&r=g',
		'slaFFik'               => 'http://www.gravatar.com/avatar/61fb07ede3247b63f19015f200b3eb2c?s=24&d=monsterid&r=g',
		'sushkov'               => 'http://www.gravatar.com/avatar/39639fde05c65fae440b775989e55006?s=24&d=monsterid&r=g',
		'valant'                => 'http://www.gravatar.com/avatar/bbbe17bf36f7df6f19c00f28f3614d4c?s=24&d=monsterid&r=g',
		'valentinas'            => 'http://www.gravatar.com/avatar/f8393ef67ed5144e15890d60eaa2289e?s=24&d=monsterid&r=g',
	);

	// Filter for 3rd party plugins
	$people = apply_filters( 'dpa_supported_plugins_get_contributors', $people );

	// Build array of contributors for the current plugin
	$contributors = array();
	foreach ( $raw_contributors as $contributor_slug => $contributor_profile ) {
		$contributors[$contributor_slug] = ! empty( $people[$contributor_slug] ) ? $people[$contributor_slug] : '';
	}

	return apply_filters( 'dpa_supported_plugins_get_contributor_data', $contributors, $raw_contributors, $people );
}
?>