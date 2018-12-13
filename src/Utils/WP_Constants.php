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

class WP_Constants {

	/**
	 * Holds the singleton instance.
	 *
	 * @var WP_Constants
	 */
	private static $instance;

	/**
	 * WP_Constants constructor.
	 */
	private function __construct() {
		// Dissallow File Editing setting within the WordPress admin dashboard
		if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
			define( 'DISALLOW_FILE_EDIT', true );
		}
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
