<?php
/**
 * Common functions
 *
 * Common functions are ones that are used by more than one component, like
 * achievements, achievement_progress, events taxonomy...
 *
 * @package Achievements
 * @subpackage CommonFunctions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Assist pagination by returning correct page number
 *
 * @global WP_Query $wp_query
 * @return int Current page number
 * @since 3.0
 */
function dpa_get_paged() {
	global $wp_query;

	// Make sure to not paginate widget queries
	if ( ! dpa_is_query_name( 'dpa_widget' ) ) {
		$paged = 0;

		// Check the query var
		if ( get_query_var( 'paged' ) ) {
			$paged = get_query_var( 'paged' );

		// Check query paged
		} elseif ( ! empty( $wp_query->query['paged'] ) ) {
			$paged = $wp_query->query['paged'];
		}

		// Paged found
		if ( ! empty( $paged ) )
			return (int) $paged;
	}

	// Default to first page
	return 1;
}

/**
 * Provides post_parent__in and __not_in support.
 *
 * @global WP $wp
 * @global WPDB $wpdb
 * @param string $where
 * @param WP_Query $object
 * @return string
 * @see http://core.trac.wordpress.org/ticket/13927/
 * @since 3.0
 */
function dpa_query_post_parent__in( $where, $object = null ) {
	global $wp, $wpdb;

	// Noop if WP core supports this already
	if ( in_array( 'post_parent__in', $wp->private_query_vars ) )
		return $where;

	// Other plugins or themes might implement something like this. Check for known implementations.
	if ( function_exists( 'bbp_query_post_parent__in' ) || class_exists( 'Ideation_Gallery_Sidebar' ) )
		return $where;

	// Bail if no WP_Query object passed
	if ( empty( $object ) )
		return $where;

	// Only 1 post_parent so return $where
	if ( is_numeric( $object->query_vars['post_parent'] ) )
		return $where;

	// Including specific post_parent's
	if ( ! empty( $object->query_vars['post_parent__in'] ) ) {
		$ids    = implode( ',', array_map( 'absint', $object->query_vars['post_parent__in'] ) );
		$where .= " AND $wpdb->posts.post_parent IN ($ids)";

	// Excluding specific post_parent's
	} elseif ( ! empty( $object->query_vars['post_parent__not_in'] ) ) {
		$ids    = implode( ',', array_map( 'absint', $object->query_vars['post_parent__not_in'] ) );
		$where .= " AND $wpdb->posts.post_parent NOT IN ($ids)";
	}

	// Return possibly modified $where
	return $where;
}