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

class Util {

	/**
	 * Holds the singleton instance.
	 *
	 * @var Util
	 */
	private static $instance;

	/**
	 * Util constructor.
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
		self::get_instance();
	}

	/**
	 * Remove prefix from a given string
	 * eg. "admin_footer-plugins.php" becomes "plugins.php"
	 *
	 * @param  string $string eg. "admin_footer-plugins.php"
	 * @param  string $prefix eg. "admin_footer-"
	 * @return string         eg. "plugins.php"
	 */
	public static function remove_prefix( $string, $prefix = '' ) {
		return preg_replace( '/^' . preg_quote( $prefix ) . '/', '', $string );
	}

	/**
	 * Remove suffix from a given string
	 * eg. "plugin-install.php" becomes "plugin"
	 *
	 * @param  string $string eg. "plugin-install.php"
	 * @param  string $suffix eg. "-install.php"
	 * @return string         eg. "plugin"
	 */
	public static function remove_suffix( $string, $suffix = '' ) {
		return preg_replace( '/' . preg_quote( $suffix ) . '+$/', '', $string );
	}

	/**
	 * Try to detect if a user has admin capabilities.
	 *
	 * @return boolean true if it has admin capabilities.
	 */
	public static function is_admin_user() {
		// include_once(ABSPATH . 'wp-includes/pluggable.php');
		return in_array( 'administrator', wp_get_current_user()->roles );
	}

	/**
	 * Check if a plugin is installed.
	 *
	 * @param string $slug Plugin slug.
	 *
	 * @return boolean
	 */
	public static function is_installed( $slug ) {
		$plugins = get_plugins();

		return isset( $plugins[ $slug ] );
	}

	/**
	 * Try to detect if plugin/theme is in local development.
	 *
	 * @param  string $slug eg. akismet/akismet.php
	 * @param  string $type valid types: 'plugin', 'theme' (default: 'plugin')
	 * @return boolean      true if is in local development
	 */
	public static function is_in_local_development( $slug, $type = 'plugin' ) {
		$dirname  = dirname( $slug );                                      // eg. akismet
		$folder   = ( $type == 'theme' ) ? 'themes' : 'plugins';
		$fullpath = WP_CONTENT_DIR . '/' . $folder . '/' . $dirname . '/'; // eg. www.example.com/wp-content/plugins/akismet/

		// Under development plugins/themes
		if ( file_exists( $fullpath . '.git/' ) || file_exists( $fullpath . '.hg/' ) || file_exists( $fullpath . '.svn/' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Try to detect if webisite is in local development.
	 *
	 * @return boolean      true if is in local development
	 */
	public static function is_localhost() {
		$whitelist = array( '127.0.0.1', '::1' );
		return in_array( $_SERVER['REMOTE_ADDR'], $whitelist );
	}

	/**
	 * Check if a plugin is a official WP.org plugin.
	 *
	 * @param string $slug       eg askimet/askimet.php
	 * @return boolean $on_wporg True if is on WP.org
	 */
	public static function is_on_wporg( $slug ) {
		$on_wporg = false;
		_maybe_update_plugins();
		$plugin_state = get_site_transient( 'update_plugins' );
		if ( isset( $plugin_state->response[ $slug ] )
		|| isset( $plugin_state->no_update[ $slug ] )
		) {
			$on_wporg = true;
		}
		return $on_wporg;
	}

	/**
	 * Get WP.org plugin page.
	 *
	 * @param string $slug eg askimet/askimet.php
	 * @return mixed $plugin_page URL if it has a WP.org page (default: false)
	 */
	public static function get_plugin_page( $slug ) {
		$on_wporg = Util::is_on_wporg( $slug );
		if ( $on_wporg ) {
			$plugin_page = '';
			_maybe_update_plugins();
			$plugin_state = get_site_transient( 'update_plugins' );
			if ( isset( $plugin_state->response[ $slug ] )
			&& property_exists( $plugin_state->response[ $slug ], 'url' )
			) {
					$plugin_page = $plugin_state->response[ $slug ]->url;
			} elseif ( isset( $plugin_state->no_update[ $slug ] )
					&& property_exists( $plugin_state->no_update[ $slug ], 'url' )
				) {
					$plugin_page = $plugin_state->no_update[ $slug ]->url;
			}
			return $plugin_page;
		}
		return false;
	}

}
