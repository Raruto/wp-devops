<?php
/**
 * DevOps Tools
 *
 * @package WP_DevOps
 * @author  Raruto
 * @license GPL-2.0+
 * @link    https://github.com/Raruto/wp-devops
 */

namespace Raruto\Controller\Admin;

use Raruto\Utils\Constants;
use Raruto\Utils\Util;

/**
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

class WP_DevOps_Links {

	/**
	 * WP_DevOps_Links constructor.
	 */
	private function __construct() {
	}

	/**
	 * WP_DevOps_Links init function.
	 */
	public static function init() {
		 $self = new self();
		 $self->init_hooks();
	}

	public function init_hooks() {
		if ( Util::is_admin_user() ) {
			add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_meta_settings_link' ), 100, 2 );
			// add_filter( 'plugin_action_links_' . Constants::$PLUGIN_SLUG, array( $this, 'plugin_settings_link' ) );
			// add_action( 'after_plugin_row', array( &$this, 'after_plugin_row' ) );
		}
		// add_action( 'all_admin_notices', array( &$this, 'pre_current_active_plugins' ) );
		// add_filter( 'views_plugins', array( &$this, 'wpse_add_my_view' ) );
		// add_action( 'admin_notices', array( &$this, 'pre_current_active_plugins' ), 0 );
	}

	/**
	 *
	 */
	// function plugin_settings_link( $links ) {
	// $settings_page = is_multisite() ? 'settings.php' : 'options-general.php';
	// $settings_link = array( '<a style="font-weight: 600; color: #4a4a4a; font-family: Monospace;" href="' . esc_url( network_admin_url( $settings_page ) ) . '?page=github-updater">' . esc_html__( 'GitHub Updater', Constants::$PLUGIN_TEXT_DOMAIN ) . '</a>' );
	// array_unshift( $links, $settings_link );
	// return $links + $settings_link;
	// }
	/**
	 *
	 */
	// function wpse_add_my_view( $views ) {
	// return $views + array( 'today' => "<a class='page-title-action' href='options-general.php?page=github-updater&tab=github_updater_install_plugin' style='top: 0px;'>" . __( 'Install from Git', 'myplugin' ) . '</a>' );
	// }
	function plugin_row_meta_settings_link( $links, $plugin_slug ) {
		if ( $plugin_slug == Constants::$PLUGIN_SLUG ) {
			$settings_page = is_multisite() ? 'settings.php' : 'options-general.php';
			$links[]       = '<a style="display:block; min-height: 50px;" href="#"></a>';
			$style         = 'style="font-weight: 600; color: #4a4a4a; font-family: Monospace;"';
			if ( is_plugin_active( 'github-updater/github-updater.php' ) ) {
				$links[] = '<a ' . $style . '  href="' . esc_url( network_admin_url( $settings_page ) ) . '?page=github-updater">' . esc_html__( 'GitHub Updater', Constants::$PLUGIN_TEXT_DOMAIN ) . '</a>';
			}
			if ( is_plugin_active( 'transients-manager/transients-manager.php' ) ) {
				$links[] = '<a ' . $style . ' href="' . esc_url( network_admin_url( 'tools.php' ) ) . '?page=pw-transients-manager">' . esc_html__( 'Transient Manager', Constants::$PLUGIN_TEXT_DOMAIN ) . '</a>';
			}
			if ( is_plugin_active( 'advanced-cron-manager/advanced-cron-manager.php' ) ) {
				$links[] = '<a ' . $style . ' href="' . esc_url( network_admin_url( 'tools.php' ) ) . '?page=advanced-cron-manager">' . esc_html__( 'Cron Manager', Constants::$PLUGIN_TEXT_DOMAIN ) . '</a>';
			}
			if ( is_plugin_active( 'wp-sync-db/wp-sync-db.php' ) ) {
				$links[] = '<a ' . $style . ' href="' . esc_url( network_admin_url( 'tools.php' ) ) . '?page=wp-sync-db">' . esc_html__( 'WP Sync DB', Constants::$PLUGIN_TEXT_DOMAIN ) . '</a>';
			}
			if ( is_plugin_active( 'all-in-one-wp-migration/all-in-one-wp-migration.php' ) ) {
				$links[] = '<a ' . $style . ' href="' . esc_url( network_admin_url( 'admin.php' ) ) . '?page=site-migration-export">' . esc_html__( 'Export DB', Constants::$PLUGIN_TEXT_DOMAIN ) . '</a>';
				$links[] = '<a ' . $style . ' href="' . esc_url( network_admin_url( 'admin.php' ) ) . '?page=site-migration-export">' . esc_html__( 'Import DB', Constants::$PLUGIN_TEXT_DOMAIN ) . '</a>';
			}
			if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
				$links[] = '<a ' . $style . ' href="' . esc_url( network_admin_url( 'admin.php' ) ) . '?page=wpseo_dashboard">' . esc_html__( 'SEO', Constants::$PLUGIN_TEXT_DOMAIN ) . '</a>';
			}
			$links[] = '<a style="display:block;" href="#"></a>';
		}
		return $links;
	}

}
