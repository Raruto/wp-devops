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

interface PluginInterface {
	public function get_name();
	public function get_reponame();
	public function get_version();
	public function get_required_version();

	public function is_packagist();
	public function is_in_development();

	public function has_composer();

	public function get_composer();

	public function get_vcs_type();
	public function get_url();
	public function get_reference();
}
