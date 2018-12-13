<?php
/**
 * DevOps Tools
 *
 * @package WP_DevOps
 * @author  Raruto
 * @license GPL-2.0+
 * @link    https://github.com/Raruto/wp-devops
 */

namespace Raruto\Utils;

/**
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

class WP_Hooks {

	/**
	 * Holds the singleton instance.
	 *
	 * @var WP_Hooks
	 */
	private static $instance;

	/**
	 * WP_Hooks constructor.
	 */
	private function __construct() {
	}

	/**
	 * Singleton.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Init function.
	 */
	public static function init() {
		$self = self::get_instance();
		$self->init_hooks();
	}

	public function init_hooks() {
		add_filter( 'github_updater_set_options', array( &$this, 'filter_github_updater_set_options' ) );
		add_filter( 'github_updater_hide_settings', array( &$this, 'filter_hide_github_updater_settings' ) );

		add_filter( 'wp_default_editor', array( &$this, 'filter_wp_default_editor' ) );
		add_filter( 'admin_footer_text', array( &$this, 'filter_admin_footer_text' ) );
		add_filter( 'update_footer', array( &$this, 'filter_update_footer' ), 11 );
	}

	/**
	 * Override Github_Updater default options.
	 *
	 * @return array
	 *
	 * @link https://github.com/afragen/github-updater/wiki/Developer-Hooks
	 */
	public function filter_github_updater_set_options() {
		return array(
			// 'my-private-theme'    => 'kjasdp984298asdvhaljsg984aljhgosrpfiu',
			// 'github_access_token' => 'iorgoaihrg[89930ews8dchujnasgp',
			'branch_switch' => 1,
		);
	}

	/**
	 * Hide Github_Updater page for those without administrative privileges.
	 *
	 * @return boolean
	 *
	 * @link https://github.com/afragen/github-updater/wiki/Developer-Hooks
	 */
	public function filter_hide_github_updater_settings() {
		return false === Util::is_admin_user();
	}

	/**
	 * Set default editor mode to 'html' or 'tinymce'
	 *
	 * @param string $r Which editor should be displayed by default. Either 'tinymce', 'html', or 'test'.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/wp_default_editor/
	 */
	public function filter_wp_default_editor( $r ) {
		return Util::is_admin_user() ? 'html' : $r;
	}

	/**
	 * Remove left admin footer text
	 *
	 * @param string $text The content that will be printed.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/admin_footer_text/
	 */
	public function filter_admin_footer_text( $text ) {
		return __return_empty_string();
	}

	/**
	 * Remove right admin footer text (where the WordPress version nr is)
	 *
	 * @param string $content The content that will be printed.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/update_footer/
	 */
	public function filter_update_footer( $content ) {
		return Util::is_admin_user() ? $content : __return_empty_string();
	}
}
