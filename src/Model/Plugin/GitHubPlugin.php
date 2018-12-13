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

class GitHubPlugin extends GitPlugin {

	public function __construct( $plugin_path, $plugin_data ) {
		parent::__construct( $plugin_path, $plugin_data );

		if ( ! empty( $plugin_data['GitHub Plugin URI'] ) ) {
			$this->set_plugin_uri( $plugin_data['GitHub Plugin URI'] );
		}
		// if ( ! empty( $plugin_data['GitHub Branch'] ) ) {
		// 	$this->set_branch( $plugin_data['GitHub Branch'] );
		// }
		if ( ! empty( $plugin_data['GitHub Access Token'] ) ) {
			$this->set_token( $plugin_data['GitHub Access Token'] );
		}
		$this->set_hostname( 'github' );

		$this->icon = 'icon/GitHub-Mark-32px.png';
		if ( ! empty( $plugin_data['GitHub Access Token'] ) ) {
			$this->icon = 'icon/GitHub-Mark-Private-32px.png"';
		}

	}

}
