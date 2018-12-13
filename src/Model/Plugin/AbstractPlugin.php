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

use Raruto\Utils\Util;

/*
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

abstract class AbstractPlugin implements PluginInterface {

	protected $slug;                // eg. akismet
	protected $path;                // eg. www.example.com/wp-content/plugins/akismet/
	protected $filepath;        // eg. www.example.com/wp-content/plugins/akismet/akismet.php
	protected $plugin_path; // eg. akismet/akismet.php
	protected $plugin_data;
	protected $plugin_uri;
	protected $hostname;
	protected $icon;

	// TODO: Not all plugins are on WPackagist,
	// we should look at RepositoryInterface::findPackage
	// in the Composer APIs to ascertain if a package
	// is present or not (see also: https://github.com/tomjn/composerpress)
	const FALLBACK_VENDOR = '__NOTFOUND__';

	public function __construct( $plugin_path, $plugin_data ) {
		$folder   = plugin_dir_path( $plugin_path );                      // eg. akismet/
		$slug     = basename( $folder );                                                      // eg. akismet
		$path     = WP_CONTENT_DIR . '/plugins/' . $folder;                     // eg. www.example.com/wp-content/plugins/akismet/
		$filepath = WP_CONTENT_DIR . '/plugins/' . $plugin_path;    // eg. www.example.com/wp-content/plugins/akismet/akismet.php

		$this->slug        = $slug;
		$this->path        = $path;
		$this->filepath    = $filepath;
		$this->plugin_path = $plugin_path;
		$this->plugin_data = $plugin_data;

	}

	public function get_name() {
		return $this->slug;
	}

	public function get_plugin_path() {
		return $this->plugin_path;
	}

	public function has_plugin_uri() {
		return false !== $this->plugin_uri;
	}

	public function get_plugin_uri() {
		return $this->plugin_uri;
	}

	public function set_plugin_uri( $uri ) {
		$this->plugin_uri = ( ! empty( $uri ) && is_string( $uri ) ) ? $uri : $this->plugin_uri;
	}

	public function get_hostname() {
		return $this->hostname;
	}

	public function set_hostname( $hostname ) {
		$this->hostname = ( ! empty( $hostname ) && is_string( $hostname ) ) ? $hostname : $this->hostname;
	}

	public function get_icon() {
		return $this->icon;
	}

	public function set_icon( $icon ) {
		$this->icon = ( ! empty( $icon ) && is_string( $icon ) ) ? $icon : $this->icon;
	}

	public function get_reponame() {

		$namespace = Util::is_on_wporg($this->plugin_path) ? sanitize_title('wpackagist-plugin') : self::FALLBACK_VENDOR;
		$package   = basename( $this->path );

		$reponame = $namespace . '/' . sanitize_title( $package );
		if ( $this->has_composer() ) {
			$composer = $this->get_composer();
			if ( ! empty( $composer->name ) ) {
				return $composer->name;
			}
		}
		return $reponame;
	}

	public function is_in_development() {
		return false;
	}

	public function has_composer() {
		$path = trailingslashit( $this->path ) . 'composer.json';
		return file_exists( $path );
	}

	public function get_composer() {
		$path    = trailingslashit( $this->path ) . 'composer.json';
		$content = file_get_contents( $path );
		$json    = json_decode( $content );
		// wp_die( print_r( $json, true ) );
		return $json;
	}

	abstract public function has_token();
	abstract public function is_packagist();
	abstract public function get_version();
	abstract public function get_required_version();
	abstract public function get_vcs_type();
	abstract public function get_url();
	abstract public function get_reference();
}
