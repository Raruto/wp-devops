<?php
/**
 * DevOps Tools
 *
 * @package WP_DevOps
 * @author  Raruto
 * @license GPL-2.0+
 * @link    https://github.com/Raruto/wp-devops
 */

namespace Raruto;

use Raruto\Controller\WP_Dependency_Installer;
use Raruto\Controller\WP_Screen;
use Raruto\Controller\WP_Menu;
use Raruto\Controller\GHU_Rest_Update;
use Raruto\Controller\WP_Rest_Log_Table;
use Raruto\Controller\GHU_Tabs;
use Raruto\Utils\Constants;
use Raruto\Utils\WP_Constants;
use Raruto\Utils\WP_Hooks;
use Raruto\Utils\Util;

/**
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Main {

	/**
	 * Main constructor.
	 */
	private function __construct() {
		Constants::init();
		WP_Constants::init();
		Util::init();
		WP_Hooks::init();
		$config = array(
			0 => [
				'name'     => 'GitHub Updater',
				'host'     => 'github',
				'slug'     => 'github-updater/github-updater.php',
				'uri'      => 'afragen/github-updater',
				'branch'   => 'master',
				'required' => true,
				'token'    => null,
			],
			// 1 => [
			// 	'name'     => 'Query Monitor',
			// 	'host'     => 'wordpress',
			// 	'slug'     => 'query-monitor/query-monitor.php',
			// 	'uri'      => 'https://wordpress.org/plugins/query-monitor/',
			// 	'branch'   => 'trunk',
			// 	'required' => false,
			// ],
			// 2 => [
			// 	'name'     => 'Advanced Cron Manager',
			// 	'host'     => 'wordpress',
			// 	'slug'     => 'advanced-cron-manager/advanced-cron-manager.php',
			// 	'uri'      => 'https://wordpress.org/plugins/advanced-cron-manager/',
			// 	'version'   => '2.3.1',
			// 	'required' => false,
			// ],
			// 3 => [
			// 	'name'     => 'All-in-One WP Migration',
			// 	'host'     => 'wordpress',
			// 	'slug'     => 'all-in-one-wp-migration/all-in-one-wp-migration.php',
			// 	'uri'      => 'https://wordpress.org/plugins/all-in-one-wp-migration/',
			// 	'branch'   => 'trunk',
			// 	'required' => false,
			// ],
			// 4 => [
			// 	'name'     => 'Transients Manager',
			// 	'host'     => 'wordpress',
			// 	'slug'     => 'transients-manager/transients-manager.php',
			// 	'uri'      => 'https://wordpress.org/plugins/transients-manager/',
			// 	'branch'   => 'trunk',
			// 	'required' => false,
			// ],
			// 5 => [
			// 	'name'     => 'WP Sync DB',
			// 	'host'     => 'github',
			// 	'slug'     => 'wp-sync-db/wp-sync-db.php',
			// 	'uri'      => 'wp-sync-db/wp-sync-db',
			// 	'branch'   => 'master',
			// 	'required' => false,
			// ],
			// 6 => [
			// 	'name'     => 'Yoast SEO',
			// 	'host'     => 'wordpress',
			// 	'slug'     => 'wordpress-seo/wp-seo.php',
			// 	'uri'      => 'https://wordpress.org/plugins/wordpress-seo/',
			// 	'branch'   => 'trunk',
			// 	'required' => false,
			// ],
		);
		//add_filter( 'wp-devops\must_have_plugins', function( $config ) { return array(); });
		$config = apply_filters( 'wp-devops\must_have_plugins', $config );

		WP_Dependency_Installer::init( $config );
		WP_Screen::init();
		WP_Menu::init();
		GHU_Tabs::init();

		GHU_Rest_Update::init();
		WP_Rest_Log_Table::update_db_table(); // Trick to update database version of the plugin

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

	}

	/**
	 * Main init function.
	 */
	public static function init() {
		 $self = new self();
	}

	/**
	 * Load CSS frontend files.
	 */
	public function enqueue_styles() {
		if ( is_user_logged_in() ) {
			wp_enqueue_style( 'wp-devops/logged-user-style', plugins_url( Constants::$PLUGIN_CSS_FOLDER . 'logged-user.css', Constants::$PLUGIN_SLUG ), array(), '1.0' );
		}
	}

	/**
	 * Runs via plugin activation hook
	 */
	public static function install() {
		WP_Rest_Log_Table::update_db_table(); // create a database table ("ghu-logs")
	}
}
