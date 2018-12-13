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

// use Gitonomy\Git\Repository;
/*
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

class GitPlugin extends WordpressPlugin {
	// private $repository;
	protected $branch;
	protected $token;

	public function __construct( $plugin_path, $plugin_data ) {
		parent::__construct( $plugin_path, $plugin_data );

		// $this->repository = new Repository( $this->path ); // REQUIRES: Gitonomy, Symfony
		if ( $this->is_in_development() ) {
			// Parse config file with sections
			$this->config = parse_ini_file( $this->path . '.git/config', true );
		}
		$this->branch = ! empty( $plugin_data['branch'] ) ? $plugin_data['branch'] : 'master';
		$this->token  = false;
	}

	public function get_branch() {
		return $this->branch;
	}

	public function set_branch( $branch ) {
		$this->branch = ( ! empty( $uri ) && is_string( $branch ) ) ? $branch : $this->branch;
	}

	public function get_token() {
		return $this->token;
	}

	public function set_token( $token ) {
		$this->$token = ( ! empty( $token ) && is_string( $token ) ) ? $token : $this->$token;
	}

	public function has_token() {
		return false !== $this->token;
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
				if ( is_numeric( $composer->version ) ) {
					return '~' . $composer->version;
				}
				return $composer->version;
			}
		}
		return $version;
	}

	public function is_packagist() {
		return false;
	}

	public function is_in_development() {
		return file_exists( $this->path . '.git/' );
	}

	public function has_vcs() {
		return true;
	}

	public function get_vcs_type() {
		return 'git';
	}

	public function get_url() {
		if ( $this->has_composer() ) {
			// wp_die( 'omg composer'.$this->get_name() );
		}
		// get the repository URL
		// $remote_url = $this->repository->run( 'config', array( '--get' => 'remote.origin.url' ) ); // REQUIRES: Gitonomy, Symfony
		if ( ! empty( $this->config ) ) {
			$remote_url = $this->config['remote origin']['url'];
			$remote_url = trim( $remote_url );
		} else {
			$remote_url = false;
		}
		return $remote_url;

	}
}
