<?php

/**
 * Functions of Achievements' theme compatibility layer
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'DPA_Default' ) ) :

/**
 * Loads Achievements' theme compatibility functions
 *
 * This is not a real theme by WordPress standards and is instead used as the
 * fallback for any WordPress theme that does not have Achievements templates in it.
 *
 * To make your custom theme Achievements compatible and customise the templates,
 * you can copy these files into your theme without needing to merge anything
 * together; we'll safely handle the rest.
 *
 * See @link DPA_Theme_Compat() for more.
 *
 * @since 3.0
 */
class DPA_Default extends DPA_Theme_Compat {

	/**
	 * Constructor
	 *
	 * @since 3.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_actions();
	}

	/**
	 * Sets up global variables
	 *
	 * @since 3.0
	 */
	private function setup_globals() {
		$this->id      = 'default';
		$this->name    = __( 'Achievements Default', 'dpa' );
		$this->version = dpa_get_version();
		$this->dir     = trailingslashit( achievements()->plugin_dir . 'theme-compat' );
		$this->url     = trailingslashit( achievements()->plugin_url . 'theme-compat' );
	}

	/**
	 * Sets up the theme hooks
	 *
	 * @since 3.0
	 */
	private function setup_actions() {
		// Template pack
		add_action( 'dpa_enqueue_scripts', array( $this, 'enqueue_styles'  ) );
		add_action( 'dpa_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Notifications
		add_action( 'dpa_enqueue_scripts', array( $this, 'enqueue_notifications_style' ) );

		do_action_ref_array( 'dpa_theme_compat_actions', array( &$this ) );
	}

	/**
	 * Load the theme CSS
	 *
	 * @since 3.0
	 * @todo LTR CSS
	 */
	public function enqueue_styles() {
		// Only load on Achievements pages
		if ( ! is_achievements() )
			return;

		$file = 'css/achievements.css';

		// Check child theme
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $file ) ) {
			$location = trailingslashit( get_stylesheet_directory_uri() );
			$handle   = 'dpa-child-achievements';

		// Check parent theme
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . $file ) ) {
			$location = trailingslashit( get_template_directory_uri() );
			$handle   = 'dpa-parent-achievements';

		// Achievements theme compatibility
		} else {
			$location = trailingslashit( $this->url ) . 'achievements/';
			$handle   = 'dpa-default-achievements';
		}

		// Enqueue the stylesheet
		wp_enqueue_style( $handle, $location . $file, array(), $this->version, 'screen' );
	}

	/**
	 * Load the CSS for notifications
	 *
	 * @since 3.0
	 * @todo LTR CSS
	 */
	public function enqueue_notifications_style() {
		// If we don't have any notifications to show, bail out
		$notifications = dpa_get_user_notifications();
		if ( empty( $notifications ) )
			return;

		$file = 'css/notifications.css';

		// Check child theme
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $file ) ) {
			$location = trailingslashit( get_stylesheet_directory_uri() );
			$handle   = 'dpa-child-notifications';

		// Check parent theme
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . $file ) ) {
			$location = trailingslashit( get_template_directory_uri() );
			$handle   = 'dpa-parent-notifications';

		// Achievements theme compatibility
		} else {
			$location = trailingslashit( $this->url ) . 'achievements/';
			$handle   = 'dpa-default-notifications';
		}

		// Enqueue the stylesheet
		wp_enqueue_style( $handle, $location . $file, array(), $this->version, 'screen' );
	}

	/**
	 * Enqueue the required Javascript files
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		$notifications = dpa_get_user_notifications();

		// Only load on Achievements pages, or when we have notifications to show
		if ( ! is_achievements() && empty( $notifications ) )
			return;

		wp_enqueue_script( 'achievements-js', $this->url . 'achievements/js/achievements.js', array( 'jquery' ), $this->version, true );
	}
}  // class_exists

new DPA_Default();
endif;
