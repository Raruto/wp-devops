<?php
/**
 * DevOps Tools
 *
 * @package	WP_DevOps
 * @author	Raruto
 * @license	GPL-2.0+
 * @link	 https://github.com/Raruto/wp-devops
 */

namespace Raruto\Model\JSON;

/*
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Interface JSON Model
 *
 * @package Raruto\
 */
interface JSONModelInterface
{
	public function fill();

	public function add_plugin( $plugin_path, $plugin_data );

	public function initialize_json_manifest();

	public function finalize_json_manifest();

	public function to_json();
}
