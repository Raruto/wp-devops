<?php
/**
 * DevOps Tools
 *
 * @package WP_DevOps
 * @author  Raruto
 * @license GPL-2.0+
 * @link    https://github.com/Raruto/wp-devops
 *
 * based on: https://github.com/szepeviktor/github-link
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

class GHU_Icon_Links {

	/**
	 * GHU_Icon_Links constructor.
	 */
	private function __construct() {
	}

	/**
	 * GHU_Icon_Links init function.
	 */
	public static function init() {
		$self = new self();
		$self->init_hooks();
	}

	public function init_hooks() {
		include_once ABSPATH . '/wp-admin/includes/plugin.php';

		$this->extra_plugin_headers = array( 'GitHub Plugin URI', 'GitLab Plugin URI', 'Bitbucket Plugin URI' );

		add_filter( 'extra_plugin_headers', array( &$this, 'extra_plugin_headers' ) );

		$installed_plugins = get_plugins();

		foreach ( $installed_plugins as $plugin_slug => $plugin_data ) {
			add_filter( "plugin_action_links_{$plugin_slug}", array( &$this, 'plugin_action_links' ), 1000, 4 );
			add_filter( "network_admin_plugin_action_links_{$plugin_slug}", array( &$this, 'plugin_action_links' ), 1000, 4 );
		}
	}

	/**
	 * Add custom git plugin headers
	 */
	public function extra_plugin_headers() {
		return $this->extra_plugin_headers;
	}

	/**
	 * Add an additional element to the plugin action links.
	 *
	 * @param $actions
	 * @param $plugin_file
	 * @param $plugin_data
	 * @param $context
	 *
	 * @return array
	 */
	public function plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {

		// No GitHub data during search installed plugins.
		if ( 'search' === $context ) {
			return $actions;
		}

		$link_template = '<a href="%s" title="%s" target="_blank" style="color: #32373c;"><img src="%s" style="width: 16px; height: 16px; margin-top: 4px; padding-right: 4px; float: none;" height="16" width="16" alt="%s" />%s</a>';

		$branch = ! empty( $plugin_data['branch'] ) ? $plugin_data['branch'] : 'master';

		foreach ( $this->extra_plugin_headers as $header ) {
			if ( ! empty( $plugin_data[ $header ] ) ) {
				$githost_name = preg_replace( '/ Plugin URI$/', '', $header );
				$new_action   = array(
					strtolower( $githost_name ) => sprintf(
						$link_template,
						$plugin_data[ $header ],
						__( 'Visit ' . $githost_name . ' repository', Constants::$PLUGIN_TEXT_DOMAIN ),
						plugins_url( Constants::$PLUGIN_ICON_FOLDER . $githost_name . '-Mark-32px.png', Constants::$PLUGIN_SLUG ),
						$githost_name,
						$branch ? '/' . $branch : ''
					),
				);
				$actions      = $new_action + $actions;
			}
		}

		$plugin_page = Util::get_plugin_page( $plugin_file );
		if ( false !== $plugin_page ) {
			// GHU also sets plugin->url.
			// if ( false !== strstr( $plugin_page, '//wordpress.org/plugins/' ) ) {
			$new_action  = array(
				'wordpress_org' => sprintf(
					$link_template,
					$plugin_page,
					__( 'Visit WordPress.org Plugin Page', Constants::$PLUGIN_TEXT_DOMAIN ),
					plugins_url( Constants::$PLUGIN_ICON_FOLDER . 'wordpress-logo-32.png', Constants::$PLUGIN_SLUG ),
					'wp_org',
					''
				),
			);
				$actions = $new_action + $actions;
				// }
		}

		return $actions;
	}

}
