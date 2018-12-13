<?php
/**
 * DevOps Tools
 *
 * @package WP_DevOps
 * @author  Raruto
 * @license GPL-2.0+
 * @link    https://github.com/Raruto/wp-devops
 *
 * based on: https://github.com/ThemeBoy/hide-plugins/
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

class Hide_Plugins {

	/**
	 * Hide_Plugins constructor.
	 */
	private function __construct() {
	}

	/**
	 * Hide_Plugins init function.
	 */
	public static function init() {
		 $self = new self();
		 $self->init_hooks();
	}

	public function init_hooks() {

		// add_filter( 'site_transient_update_plugins', array( &$this, 'hide_update_nag' ), 15, 1 );
		// add_filter( 'site_transient_update_themes', array( &$this, 'hide_update_nag' ), 15, 1 );
		// add_action( 'admin_head-themes.php', array( &$this, 'hide_update_message' ) );
		// add_action( 'admin_head-plugins.php', array( &$this, 'hide_update_message' ) );
		add_filter( 'all_plugins', array( $this, 'prepare_items' ) );
		add_filter( 'option_hide_plugins', array( $this, 'hide_self' ) );

		add_action( 'admin_action_hide_plugin', array( $this, 'hide_plugin_action' ) );
		add_action( 'admin_action_show_plugin', array( $this, 'show_plugin_action' ) );

		add_filter( 'network_admin_plugin_action_links', array( $this, 'action_links' ), 10, 4 );
		add_filter( 'plugin_action_links', array( $this, 'action_links' ), 1000, 4 );

		add_action( 'admin_head-themes.php', array( &$this, 'style_table' ) );
		add_action( 'admin_head-plugins.php', array( &$this, 'style_table' ) );
	}

	/**
	 * Hide the update nag.
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	// public function hide_update_nag( $value ) {
	// switch ( current_filter() ) {
	// case 'site_transient_update_plugins':
	// $repos = self::$plugins_slugs;
	// break;
	// case 'site_transient_update_themes':
	// $repos = self::$themes_slugs;
	// break;
	// default:
	// return $value;
	// }
	//
	// if ( ! empty( $repos ) ) {
	// foreach ( array_keys( $repos ) as $repo ) {
	// if ( 'update_nag' === $repo ) {
	// continue;
	// }
	// if ( isset( $value->response[ $repo ] ) ) {
	// unset( $value->response[ $repo ] );
	// }
	// }
	// }
	//
	// return $value;
	// }
	/**
	 * Hide update messages for GitHub Updater.
	 */
	// public function hide_update_message() {
	// global $pagenow;
	//
	// if ( 'plugins.php' === $pagenow && ! empty( self::$config['plugins'] ) ) {
	// foreach ( array_keys( self::$config['plugins'] ) as $plugin ) {
	// $css[] = '[data-slug="' . dirname( $plugin ) . '"] div.update-message';
	// }
	// }
	// if ( 'themes.php' === $pagenow && ! empty( self::$config['themes'] ) ) {
	// foreach ( array_keys( self::$config['themes'] ) as $theme ) {
	// $css[] = '[data-slug="' . $theme . '"] div.update-message';
	// $css[] = '#' . $theme . ' div.update-message';
	// }
	// }
	//
	// if ( empty( $css ) ) {
	// return;
	// }
	//
	// $css = implode( ', ', $css );
	// echo "<!-- Local Development -->";
	// echo "<style>";
	// echo "$css { display: none; }";
	// echo "</style>";
	// }
	public function style_table() {
		global $pagenow;

		if ( 'plugins.php' === $pagenow ) {
			$installed_plugins = get_plugins();
			$hidden            = (array) array_unique( get_option( 'hide_plugins', array( Constants::$PLUGIN_SLUG ) ) );
			foreach ( $installed_plugins as $plugin_path => $plugin_data ) {
				$slug = dirname( $plugin_path );                    // eg. askimet
				if ( in_array( $plugin_path, $hidden ) ) {
					$css2[] = '[data-plugin="' . $plugin_path . '"] em';
					$css3[] = '[data-plugin="' . $plugin_path . '"]';
					$css3[] = '[data-plugin="' . $plugin_path . '"] th';
					$css3[] = '[data-plugin="' . $plugin_path . '"] td';
					$css4[] = '[data-plugin="' . $plugin_path . '"] th.check-column';
				}
			}
		}
		// if ( 'themes.php' === $pagenow ) {
		// $installed_themes = wp_get_themes();
		// foreach ( $installed_themes as $theme => $theme_data ) {
		// $slug = $theme;                                    // eg. twentyfifteen
		// Do stuff..
		// }
		// }
		if ( ! empty( $css2 ) ) {
			$css2 = implode( ', ', $css2 );
			echo '<!-- Hidden Plugins -->';
			echo '<style>';
			echo "$css2 { color: #4a4a4a; }";
			echo '</style>';
		}

		if ( ! empty( $css3 ) ) {
			$css3 = implode( ', ', $css3 );
			echo '<!-- Hidden Plugins -->';
			echo '<style>';
			echo "$css3 { background-color: #f1f1f1 !important; }";
			echo '</style>';
		}

		if ( ! empty( $css4 ) ) {
			$css4 = implode( ', ', $css4 );
			echo '<!-- Hidden Plugins -->';
			echo '<style>';
			echo "$css4 { border-left: 4px solid gray !important; }";
			echo '</style>';
		}

	}

	/**
	 * Hide this plugin from other users.
	 */
	public function hide_self( $option ) {
		if ( ! is_array( $option ) ) {
			$option = array();
		}
		if ( ! in_array( Constants::$PLUGIN_SLUG, $option ) ) {
			$option[] = Constants::$PLUGIN_SLUG;
		}
		return $option;
	}

	/**
	 * Prepare plugins.
	 */
	public function prepare_items( $plugins ) {
		$is_admin_user = Util::is_admin_user();
		$hidden        = (array) array_unique( get_option( 'hide_plugins', array( Constants::$PLUGIN_SLUG ) ) );

		if ( ! is_array( $hidden ) ) {
			$hidden = array();
		}
		$hidden = array( Constants::$PLUGIN_SLUG ) + $hidden;

		foreach ( $hidden as $filename ) {
			if ( array_key_exists( $filename, $plugins ) ) {
				if ( $is_admin_user ) {
					$plugins[ $filename ]['Name']        = '<em>' . $plugins[ $filename ]['Name'] . '</em>';
					$plugins[ $filename ]['Description'] = '<em>' . $plugins[ $filename ]['Description'] . '</em>';
				} else {
					unset( $plugins[ $filename ] );
				}
			}
		}
		return $plugins;
	}

	/**
	 * Add the action link.
	 */
	public function action_links( $actions, $plugin_file, $plugin_data, $context ) {
		global $page, $s;
		$is_admin_user = in_array( 'administrator', wp_get_current_user()->roles );
		$current_user  = wp_get_current_user();

		if ( ! $is_admin_user ) {
			return $actions;
		}

		$hidden = (array) get_option( 'hide_plugins', array( Constants::$PLUGIN_SLUG ) );
		if ( Constants::$PLUGIN_SLUG != $plugin_file ) {
			if ( in_array( $plugin_file, $hidden ) ) {
				$actions['show'] = '<a href="' . wp_nonce_url( 'plugins.php?action=show_plugin&amp;plugin=' . $plugin_file . '&amp;plugin_status=' . $context . '&amp;paged=' . $page . '&amp;s=' . $s, 'show-plugin_' . $plugin_file ) . '" title="' . esc_attr__( 'Show this plugin', Constants::$PLUGIN_TEXT_DOMAIN ) . '" class="edit" style="color: black; font-weight: 600;">' . __( 'Show', Constants::$PLUGIN_TEXT_DOMAIN ) . '</a>';
			} else {
				$actions['hide'] = '<a href="' . wp_nonce_url( 'plugins.php?action=hide_plugin&amp;plugin=' . $plugin_file . '&amp;plugin_status=' . $context . '&amp;paged=' . $page . '&amp;s=' . $s, 'hide-plugin_' . $plugin_file ) . '" title="' . esc_attr__( 'Hide this plugin', Constants::$PLUGIN_TEXT_DOMAIN ) . '" class="edit" style="color: gray; font-weight: 600; opacity:0.5;">' . __( 'Hide', Constants::$PLUGIN_TEXT_DOMAIN ) . '</a>';
			}
		}
		return $actions;
	}

	/**
	 * "Hide Plugin" action link.
	 */
	public function hide_plugin_action() {
		if ( empty( $_REQUEST['plugin'] ) ) {
			wp_die( __( 'Plugin file does not exist.', Constants::$PLUGIN_TEXT_DOMAIN ) );
		}

		// Get the filename
		$filename = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';

		// Hide the plugin
		if ( ! empty( $filename ) ) {
			$hidden = get_option( 'hide_plugins', array() );
			if ( ! in_array( $filename, $hidden ) ) {
				$hidden[] = $filename;
			}
			update_option( 'hide_plugins', $hidden );
		} else {
			wp_die( __( 'Plugin file does not exist.', Constants::$PLUGIN_TEXT_DOMAIN ) );
		}
	}

	/**
	 * "Show Plugin" action link.
	 */
	public function show_plugin_action() {
		if ( empty( $_REQUEST['plugin'] ) ) {
			wp_die( __( 'Plugin file does not exist.', Constants::$PLUGIN_TEXT_DOMAIN ) );
		}

		// Get the filename
		$filename = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';

		// Show the plugin
		if ( ! empty( $filename ) ) {
			$hidden = get_option( 'hide_plugins', array() );
			if ( ( $key = array_search( $filename, $hidden ) ) !== false ) {
				unset( $hidden[ $key ] );
			}
			update_option( 'hide_plugins', $hidden );
		} else {
			wp_die( __( 'Plugin file does not exist.', Constants::$PLUGIN_TEXT_DOMAIN ) );
		}
	}

}
