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

class SubversionPlugin extends WordpressPlugin {
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
		return false;
	}

	public function is_in_development() {
		return file_exists( $this->path . '.svn/' );
	}

	public function get_vcs_type() {
		return 'svn';
	}

	public function get_url() {
		$dbpath = trailingslashit( $this->path ) . '.svn/wc.db';

		$root = '';
		// $database = \sqlite_open( $dbpath, 0666, $error );
		$database = new \PDO( 'sqlite:' . $dbpath );
		$sql      = 'SELECT root FROM REPOSITORY ORDER BY id';
		foreach ( $database->query( $sql ) as $row ) {
			$root = trailingslashit( $row['root'] );
			break;
		}

		// $info = \svn_info( $this->path );
		return $root;
	}

	public function get_reference() {
		$dbpath = trailingslashit( $this->path ) . '.svn/wc.db';

		// $database = \sqlite_open( $dbpath, 0666, $error );
		$database = new \PDO( 'sqlite:' . $dbpath );

		$key = substr( $this->filepath, strlen( $this->path ) );

		$sql = 'SELECT * FROM NODES WHERE local_relpath = "' . $key . '" ORDER BY wc_id';
		foreach ( $database->query( $sql ) as $row ) {
			$rel = $row['repos_path'];
			break;
		}

		return dirname( $rel );
	}
}
