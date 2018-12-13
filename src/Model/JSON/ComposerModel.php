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
 * Class Composer JSON Model
 *
 * Class that will handle custom json object "composer.json"
 * based on: https://github.com/tomjn/composerpress
 *
 * @package Raruto\
 */
class ComposerModel extends JSONModel
{

	// Composer
	public $required;
	public $repos;
	public $homepage;
	public $description;
	public $version;
	public $name;
	public $extra;
	public $license;

	public function __construct() {

		// Init Composer Stuff

		$this->required = array();
		$this->repos = array();
		$this->extra = array();
		$this->homepage = '';
		$this->description = '';
		$this->version = '';
		$this->name = '';
		$this->license = '';

		$this->initialize_json_manifest();
	}

	public function add_plugin( $plugin_path, $plugin_data ) {
		$factory = new PluginFactory( $plugin_path, $plugin_data );

		$plugin = $factory->create( "composer" );				// returns a PluginInterface Instance

		//populate "composer.json"

		$remote_url = $plugin->get_url();
		$reference = $plugin->get_reference();
		$reponame = $plugin->get_reponame();
		$version = $plugin->get_version();
		$required_version = empty( $version ) ? 'dev-master' : $plugin->get_required_version();
		$vcstype = $plugin->get_vcs_type();

		if ( !$plugin->is_packagist() && $remote_url !== false ) {
			if ( $plugin->has_composer() ) {
				$this->add_repository( $vcstype, $remote_url, $reference );
				if ( !empty( $version ) ) {
					$version = $required_version;
				}
				$this->required( $reponame, $version );
			} else {
				$source = array(
					'url' => $remote_url,
					'type' => $vcstype
				);
				$source['reference'] = $reference;
				$package = array(
					'name' => $reponame,
					'version' => 'dev-master',
					'type' => 'wordpress-plugin',
					'source' => $source
				);
				$this->add_package_repository( $package );
				$this->required( $reponame, 'dev-master' );
			}
		} else {
			$this->required( $reponame, $version );
		}

	}

	public function set_homepage( $homepage ) {
		$this->homepage = $homepage;
	}

	public function set_name( $name ) {
		$this->name = $name;
	}

	public function set_version( $version ) {
		$this->version = $version;
	}

	public function set_description( $description ) {
		$this->description = $description;
	}

	public function set_license( $license ) {
		$this->license = $license;
	}

	public function add_repository( $type, $url, $reference = '' ) {
		$source = array(
			'type' => $type,
			'url' => $url.$reference
		);

		/*if ( !empty( $reference ) ) {
			$source['reference'] = trailingslashit( $reference );
		}*/
		$this->repos[] = $source;
	}

	public function add_package_repository( $package ) {
		$this->repos[] = array(
			'type' => 'package',
			'package' => $package
		);
	}

	public function add_extra( $name, $data ) {
		$this->extra[$name] = $data;
	}

	public function required( $package, $version ) {
		$this->required[ $package ] = $version;
	}

	public function initialize_json_manifest() {
		$this->required( 'johnpbloch/wordpress', '*@stable' );
		$this->required( 'php', '>=' . phpversion() );

		$this->set_name( 'wpsite/'.sanitize_title( get_bloginfo( 'name' ) ) );
		$this->set_homepage( home_url() );
		$description = get_bloginfo( 'description' );
		$this->set_description( $description );
		$this->set_license( 'GPL-2.0+' );
		$this->set_version( get_bloginfo( 'version' ) );

		$this->add_repository( 'composer', 'http://wpackagist.org' );
	}

	public function finalize_json_manifest() {
		$manifest = array();
		$manifest['name'] = $this->name;
		if ( !empty( $this->description ) ) {
			$manifest['description'] = $this->description;
		}
		if ( !empty( $this->license ) ) {
			$manifest['license'] = $this->license;
		}
		if ( !empty( $this->homepage ) ) {
			$manifest['homepage'] = $this->homepage;
		}
		$manifest['version'] = $this->version;
		if ( !empty( $this->repos ) ) {
			$manifest['repositories'] = $this->repos;
		}
		if ( !empty( $this->extra ) ) {
			$manifest['extra'] = $this->extra;
		}
		if ( !empty( $this->required ) ) {
			$manifest['require'] = $this->required;
			ksort($manifest['require']);
		}
		$manifest['minimum-stability'] = 'dev';

		return $manifest;
	}

}
