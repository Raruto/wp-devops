<?php
/**
 * DevOps Tools
 *
 * @package WP_DevOps
 * @author  Raruto
 * @license GPL-2.0+
 * @link     https://github.com/Raruto/wp-devops
 */

namespace Raruto\Model\JSON;

/*
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Abstract Class JSON Model
 *
 * @package Raruto\
 */
abstract class JSONModel implements JSONModelInterface {

	protected $json_object;

	public function fill( $plugins_paths = null ) {
		// THINGS TO REMEMBER:
		// $slug = $plugin_path;																// eg. akismet/akismet.php
		// $path = plugin_dir_path( $plugin_path );							// eg. akismet/
		// $folder_name = basename($path);											// eg. akismet
		// $fullpath = WP_CONTENT_DIR.'/plugins/'.$path;				// eg. www.example.com/wp-content/plugins/akismet/
		// $filepath = WP_CONTENT_DIR.'/plugins/'.$plugin_path;	// eg. www.example.com/wp-content/plugins/akismet/akismet.php
		$installed_plugins = get_plugins();
		foreach ( $installed_plugins as $plugin_path => $plugin_data ) {
			if ( ! isset( $plugins_paths ) || in_array( $plugin_path, $plugins_paths ) ) {
				$this->add_plugin( $plugin_path, $plugin_data );
			}
		}
		// var_dump($installed_plugins);
	}

	abstract public function add_plugin( $plugin_path, $plugin_data );

	abstract public function initialize_json_manifest();

	abstract public function finalize_json_manifest();

	public function to_json() {
		$this->json_object = $this->finalize_json_manifest();
		return json_encode( $this->json_object, ( JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) );

	}
}
