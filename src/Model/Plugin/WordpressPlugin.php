<?php
/**
 * DevOps Tools
 *
 * @package GitHub_Updater
 * @author  Raruto
 * @license GPL-2.0+
 * @link     https://github.com/Raruto/wp-devops
 */

namespace Raruto\Model\Plugin;

/*
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

class WordpressPlugin extends AbstractPlugin {

	public function __construct( $plugin_path, $plugin_data ) {
		parent::__construct( $plugin_path, $plugin_data );

		$this->plugin_uri = ! empty( $plugin_data['PluginURI'] ) ? $plugin_data['PluginURI'] : false;
		$this->hostname   = strpos( $plugin_data['PluginURI'], 'wordpress.org' ) > 0 ? 'WordPress' : 'uknown';

		$this->icon = $this->plugin_uri ? 'icon/wordpress-logo-32.png' : false;

	}

	public function get_version() {
		return $this->plugin_data['Version'];
	}

	public function get_required_version() {
		return $this->get_version();
	}

	public function has_token() {
		return false;
	}

	public function is_packagist() {
		return false;
	}

	public function is_in_development() {
		return false;
	}

	public function get_branch() {
		return false;
	}

	public function get_vcs_type() {
		return false;
	}

	public function get_url() {
		return false;
	}

	public function get_reference() {
		return '';
	}
}
