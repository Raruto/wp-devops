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

require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

/**
 * Class WP_Plugin_Installer_Skin
 */
class WP_Plugin_Installer_Skin extends \Plugin_Installer_Skin {
	public function header() {}
	public function footer() {}
	public function error( $errors ) {}
	public function feedback( $string ) {}
}
