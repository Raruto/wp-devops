<?php
/**
 * GitHub Updater
 *
 * @package WP_DevOps
 * @author  Raruto
 * @license GPL-2.0+
 * @link    https://github.com/Raruto/wp-devops
 */

namespace Raruto\Model\WP;

use Raruto\Utils\Constants;
use Raruto\Utils\Util;

use Fragen\Singleton;


/**
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class WP_Installer
 *
 * A lightweight class to add WordPress plugins or themes
 * It can install a plugin from w.org, GitHub, Bitbucket, or GitLab.
 *
 * based on: https://github.com/afragen/wp-dependency-installer
 */
class WP_Installer {

	/**
	 * Holds the singleton instance.
	 *
	 * @var \WP_Installer
	 */
	private static $instance;

	/**
	 * Holds the current plugin's slug.
	 *
	 * @var string $current_slug
	 */
	protected $current_slug;

	/**
	 * WP_Installer constructor.
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
	 * Process the plugin returning a download link.
	 *
	 * TODO: also support private repo (currently it works with public ones).
	 *
	 * @param array $plugin Plugin config.
	 * @return string $download_link
	 */
	public function get_download_link( $plugin ) {
		$download_link = null;

		// FIXME: Some automatically generated json files have
		// $plugin['host'] == "uknown" (so it doesn't even have a "uri")
		// Default beavhiour is to ignore this plugin
		if ( ! isset( $plugin['uri'] ) ) {
			return false;
		}

		$uri        = $plugin['uri'];
		$slug       = $plugin['slug'];
		$host       = explode( '.', parse_url( $uri, PHP_URL_HOST ) );
		$host       = isset( $plugin['host'] ) ? $plugin['host'] : $host[0];
		$host       = strtolower( $host );
		$path       = parse_url( $uri, PHP_URL_PATH );
		$owner_repo = str_replace( '.git', '', trim( $path, '/' ) );

		switch ( $host ) {
			case ( 'github' ):
				$download_link = 'https://api.github.com/repos/' . $owner_repo . '/zipball/' . $plugin['branch'];
				if ( ! empty( $plugin['token'] ) ) {
					$download_link = add_query_arg( 'access_token', $plugin['token'], $download_link );
				}
				break;
			case ( 'bitbucket' ):
				$download_link = 'https://bitbucket.org/' . $owner_repo . '/get/' . $plugin['branch'] . '.zip';
				break;
			case ( 'gitlab' ):
				$download_link = 'https://gitlab.com/' . $owner_repo . '/repository/archive.zip';
				$download_link = add_query_arg( 'ref', $plugin['branch'], $download_link );
				if ( ! empty( $plugin['token'] ) ) {
					$download_link = add_query_arg( 'private_token', $plugin['token'], $download_link );
				}
				break;
			case ( 'wordpress' ):
				if ( ! empty( $plugin['version'] ) ) {
					$download_link = 'https://downloads.wordpress.org/plugin/' . basename( $owner_repo ) . '.' . $plugin['version'] . '.zip';
				} else {
					$download_link = 'https://downloads.wordpress.org/plugin/' . basename( $owner_repo ) . '.zip';
				}
				break;
		}

		return $download_link;
	}

	/**
	 * Install and activate plugin.
	 *
	 * @param array $plugin Plugin config.
	 * @return bool|array false or Message.
	 */
	public function install( $plugin, $automatic_activate = false ) {

		$slug = $plugin['slug'];

		if ( Util::is_installed( $slug ) || ! current_user_can( 'update_plugins' ) ) {
			return false;
		}

		// some wp.org plugins are not correctly handled by GitHub_Updater
		// eg. https://downloads.wordpress.org/plugin/advanced-cron-manager.zip
		// instead of: advanced-cron-manager.2.3.1.zip
		// Default workflow: fallback to \Plugin_Upgrader class
		if ( false == Util::is_on_wporg( $slug ) && class_exists( 'Fragen\Singleton' ) ) {
			$installer = Singleton::get_instance( 'Fragen\GitHub_Updater\Install', $this );
			$result    = $installer->install(
				'plugin',
				$plugin
			);
			if ( false == $result ) {
				return array(
					'status' => 'error',
				);
			}
		} else {
			$this->current_slug = $slug;
			add_filter( 'upgrader_source_selection', array( $this, 'upgrader_source_selection' ), 10, 2 );

			// if you want some text update info, use the "\Plugin_Installer_Skin" instead.
			$skin = new WP_Plugin_Installer_Skin(
				array(
					'type'  => 'plugin',
					'nonce' => wp_nonce_url( $plugin['download_link'] ),
				)
			);
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			$upgrader = new \Plugin_Upgrader( $skin ); // FIXME: add root namespace slash
			$result   = $upgrader->install( $plugin['download_link'] );

			if ( is_wp_error( $result ) ) {
				return array(
					'status'  => 'error',
					'message' => $result->get_error_message(),
				);
			}

			wp_cache_flush();

			if ( 'error' === $result['status'] ) {
				return $result;
			}
		}

		if ( $automatic_activate ) {
			$this->activate( $slug );

			return array(
				'status'  => 'updated',
				'slug'    => $slug,
				'message' => sprintf( __( '%s has been installed and activated.' ), $plugin['name'] ),
			);

		}

		$action  = 'activate';
		$message = ' <a href="javascript:;" class="wpdi-button" data-action="' . $action . '" data-slug="' . $slug . '">' . ucfirst( $action ) . ' Now &raquo;</a>';

		return array(
			'status'  => 'updated',
			'message' => sprintf( __( '%s has been installed.' ) . $message, $plugin['name'] ),
		);
	}

	/**
	 * Activate plugin.
	 *
	 * @param string $plugin Plugin config.
	 * @return array Message.
	 */
	public function activate( $plugin ) {

		$slug = $plugin['slug'];

		// network activate only if on network admin pages.
		$result = is_network_admin() ? activate_plugin( $slug, null, true ) : activate_plugin( $slug );

		if ( is_wp_error( $result ) ) {
			return array(
				'status'  => 'error',
				'message' => $result->get_error_message(),
			);
		}

		return array(
			'status'  => 'updated',
			'message' => sprintf( __( '%s has been activated.' ), $plugin['name'] ),
		);

	}

	/**
	 * Correctly rename plugin for activation.
	 *
	 * @param string $source
	 * @param string $remote_source
	 *
	 * @return string $new_source
	 */
	public function upgrader_source_selection( $source, $remote_source ) {
		global $wp_filesystem;
		$new_source = trailingslashit( $remote_source ) . dirname( $this->current_slug );
		$wp_filesystem->move( $source, $new_source );

		return trailingslashit( $new_source );
	}

}
