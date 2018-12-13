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

class Constants {

	/**
	 * Holds the singleton instance.
	 *
	 * @var Constants
	 */
	private static $instance;

	/**
	 * eg: wp-devops/wp-devops.php
	 *
	 * @var string
	 */
	public static $PLUGIN_SLUG;

	/**
	 * eg: wp-devops/assets/img/icon/
	 *
	 * @var string
	 */
	public static $PLUGIN_ICON_FOLDER;

	/**
	 * eg: wp-devops/assets/css/
	 *
	 * @var string
	 */
	public static $PLUGIN_CSS_FOLDER;

	/**
	 * eg: www.example.com/wp-content/plugins/wp-devops/
	 *
	 * @var string
	 */
	public static $PLUGIN_ROOT;

	/**
	 * eg: www.example.com/wp-content/plugins/wp-devops/wp-devops.php
	 *
	 * @var string
	 */
	public static $PLUGIN_ROOT_FILE;

	/**
	 * @var string
	 */
	public static $PLUGIN_FILE;

	/**
	 * @var string
	 */
	public static $PLUGIN_TEXT_DOMAIN;

	/**
	 * Constants constructor.
	 */
	private function __construct() {
		$plugin_root_folder       = dirname( dirname( dirname( __FILE__ ) ) );
		$plugin_name              = basename( $plugin_root_folder );
		self::$PLUGIN_FILE        = $plugin_root_folder;
		self::$PLUGIN_ROOT        = $plugin_root_folder . '/';
		self::$PLUGIN_ROOT_FILE   = self::$PLUGIN_ROOT . $plugin_name . '.php';
		self::$PLUGIN_SLUG        = $plugin_name . '/' . basename( self::$PLUGIN_ROOT_FILE );
		self::$PLUGIN_ICON_FOLDER = 'assets/img/icon/';
		self::$PLUGIN_CSS_FOLDER = 'assets/css/';
		self::$PLUGIN_TEXT_DOMAIN = 'wp-devops';
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

}
