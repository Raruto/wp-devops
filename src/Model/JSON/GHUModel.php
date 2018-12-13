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

use Raruto\Model\Plugin\PluginInterface;
use Raruto\Model\Plugin\PluginFactory;

/*
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class GHU Model
 *
 * Class that will handle custom json object "github-updater.json"
 * based on: https://github.com/tomjn/composerpress
 *
 * @package Raruto\
 */
class GHUModel extends JSONModel
{

	public $plugins;

	public function __construct() {
		$this->plugins = array();

		$this->initialize_json_manifest();
	}

	public function add_plugin( $plugin_path, $plugin_data ) {
		$factory = new PluginFactory( $plugin_path, $plugin_data );

		$plugin = $factory->create( "github-updater" );	// returns an Array() Object (TODO: use classes instead..)

		//populate "github-updater.json"

		$plug = array();

		$plug['name'] = $plugin->get_name();
		$plug['host'] = $plugin->get_hostname();
		$plug['slug'] = $plugin->get_plugin_path();
		if($plugin->has_plugin_uri()){
			$plug['uri'] = $plugin->get_plugin_uri();
		}
		$plug['branch'] = $plugin->get_branch();
		$plug['version'] = $plugin->get_version();
		if($plugin->has_token()){
			$plug['token'] = $plugin->get_token();
		}
		$plug['required'] = is_plugin_active( $plugin->get_plugin_path() ); // Optional if the plugin is inactive

		$this->plugins[] = $plug;
	}

	public function initialize_json_manifest() {

	}

	public function finalize_json_manifest() {
		$manifest = array();
		$manifest = $this->plugins;
		return $manifest;
	}

}
