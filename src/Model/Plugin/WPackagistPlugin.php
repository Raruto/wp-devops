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

class WPackagistPlugin extends WordpressPlugin {

	public function __construct( $plugin_path, $plugin_data ) {
		parent::__construct( $plugin_path, $plugin_data );
	}

	public function get_version() {
		$version = $this->plugin_data['Version'];
		if ( $this->has_composer() ) {
			$composer = $this->get_composer();
			if ( ! empty( $composer->version ) ) {
				return $composer->version;
			}
		}
		return $version;
	}

	public function get_required_version() {
		$version = '>=' . $this->plugin_data['Version'];
		if ( $this->has_composer() ) {
			$composer = $this->get_composer();
			if ( ! empty( $composer->version ) ) {
				return $composer->version;
			}
		}
		return $version;
	}

	public function is_packagist() {
		return true;
	}

	public function is_in_development() {
		return false;
	}

	public function get_vcs_type() {
		return 'composer';
	}

	public function get_url() {
		return 'https://wpackagist.org';
	}
}
