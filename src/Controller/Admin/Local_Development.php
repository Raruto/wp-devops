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

class Local_Development {

	/**
	 * Local_Development constructor.
	 */
	private function __construct() {
	}

	/**
	 * Local_Development init function.
	 */
	public static function init() {
		 $self = new self();
		 $self->init_hooks();
	}

	public function init_hooks() {

		add_filter( 'plugin_row_meta', array( &$this, 'row_meta' ), 15, 2 );
		add_filter( 'theme_row_meta', array( &$this, 'row_meta' ), 15, 2 );
		if ( ! is_multisite() ) {
			add_filter( 'wp_prepare_themes_for_js', array( &$this, 'set_theme_description' ), 15, 1 );
		}

		// add_filter( 'site_transient_update_plugins', array( &$this, 'hide_update_nag' ), 15, 1 );
		// add_filter( 'site_transient_update_themes', array( &$this, 'hide_update_nag' ), 15, 1 );
		// add_action( 'admin_head-themes.php', array( &$this, 'hide_update_message' ) );
		// add_action( 'admin_head-plugins.php', array( &$this, 'hide_update_message' ) );

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

	/**
	 * Add an additional element to the row meta links.
	 *
	 * @param array  $links
	 * @param string $slug
	 *
	 * @return array $links
	 */
	public function row_meta( $links, $slug ) {
		$type = ( current_filter() == 'theme_row_meta' ) ? 'theme' : 'plugin';
		if ( Util::is_in_local_development( $slug, $type ) ) {
			$links[] = '<strong style="color:red; font-weight:700;">' . esc_html__( 'In Local Development', Constants::$PLUGIN_TEXT_DOMAIN ) . '</strong>';
		}
		return $links;
	}


	/**
	 * Sets the description for the single install theme action.
	 *
	 * @param $prepared_themes
	 *
	 * @return array
	 */
	public function set_theme_description( $prepared_themes ) {
		foreach ( $prepared_themes as $theme ) {
			$slug = $theme['id'];                              // eg. twentyfifteen
			if ( Util::is_in_local_development( $slug, 'theme' ) ) {
				$message                                        = wp_get_theme( $theme['id'] )->get( 'Description' );
				$message                                       .= '<p><strong style="color:red; font-weight:700;" >' . esc_html__( 'In Local Development', Constants::$PLUGIN_TEXT_DOMAIN ) . '</strong></p>';
				$prepared_themes[ $theme['id'] ]['description'] = $message;
			}
		}

		return $prepared_themes;
	}

	public function style_table() {
		global $pagenow;

		if ( 'plugins.php' === $pagenow ) {
			$installed_plugins = get_plugins();
			foreach ( $installed_plugins as $plugin_path => $plugin_data ) {
				$slug = dirname( $plugin_path );                    // eg. askimet
				if ( Util::is_in_local_development( $plugin_path, 'plugin' ) ) {
					$css[] = '[data-plugin="' . $plugin_path . '"] div.update-message';
				}
			}
		}
		if ( 'themes.php' === $pagenow ) {
			$installed_themes = wp_get_themes();
			foreach ( $installed_themes as $theme => $theme_data ) {
				$slug = $theme;                                    // eg. twentyfifteen
				if ( Util::is_in_local_development( $slug, 'theme' ) ) {
					$css[] = '[data-slug="' . $theme . '"] div.update-message';
				}
			}
		}

		if ( ! empty( $css ) ) {
			$css = implode( ', ', $css );
			echo '<!-- Local Development -->';
			echo '<style>';
			echo "$css { text-decoration: line-through; }";
			echo '</style>';
		}
	}

}
