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

class PluginFactory {

	protected $plugin_path;
	protected $plugin_data;

	public function __construct( $plugin_path, $plugin_data ) {
		$this->plugin_path = $plugin_path;
		$this->plugin_data = $plugin_data;
	}

	public function create( $type = 'github-updater' ) {

		$plugin_path = $this->plugin_path;
		$plugin_data = $this->plugin_data;

		$type = $this->guess_type( $plugin_path, $plugin_data );
		switch ( $type ) {
			case 'git':
				$plugin = new GitPlugin( $plugin_path, $plugin_data );
				break;
			case 'github':
				$plugin = new GitHubPlugin( $plugin_path, $plugin_data );
				break;
			case 'gitlab':
				$plugin = new GitLabPlugin( $plugin_path, $plugin_data );
				break;
			case 'bitbucket':
				$plugin = new BitbucketPlugin( $plugin_path, $plugin_data );
				break;
			case 'gitea':
				$plugin = new GiteaPlugin( $plugin_path, $plugin_data );
				break;
			case 'WordPress':
				$plugin = new WordpressPlugin( $plugin_path, $plugin_data );
				break;
			case 'mercurial':
				$plugin = new MercurialPlugin( $plugin_path, $plugin_data );
				break;
			case 'subversion':
				$plugin = new MercurialPlugin( $plugin_path, $plugin_data );
				break;
			case 'mercurial':
				$plugin = new MercurialPlugin( $plugin_path, $plugin_data );
				break;
			default:
				$plugin = new WordpressPlugin( $plugin_path, $plugin_data );
				break;
		};
		return $plugin;
	}

	public static function guess_type( $plugin_path, $plugin_data ) {
		$git        = 'git';
		$github     = 'github';
		$gitlab     = 'gitlab';
		$bitbucket  = 'bitbucket';
		$gitea      = 'gitea';
		$wp_org     = 'WordPress';
		$mercurial  = 'mercurial';
		$subversion = 'subversion';
		$wpackagist = 'wpackagist';

		$folder   = plugin_dir_path( $plugin_path );          // eg. akismet/
		$slug     = dirname( $plugin_path );                                  // eg. akismet
		$fullpath = WP_CONTENT_DIR . '/plugins/' . $folder; // eg. www.example.com/wp-content/plugins/akismet/

		// Trying to detect plugin's source
		if ( ! empty( $plugin_data['GitHub Plugin URI'] ) ) {
			return $github;
		}
		if ( ! empty( $plugin_data['GitLab Plugin URI'] ) ) {
			return $gitlab;
		}
		if ( ! empty( $plugin_data['Bitbucket Plugin URI'] ) ) {
			return $bitbucket;
		}
		if ( ! empty( $plugin_data['Gitea Plugin URI'] ) ) {
			return $gitea;
		}
		if ( ! empty( $plugin_data['PluginURI'] ) ) {

			// Becasue some plugins doesn't use GitHub Updater Headers
			// we need to check if it could come from a different source
			// other than wp.org
			if ( strpos( $plugin_data['PluginURI'], 'wordpress.org' ) > 0 ) {
				return $wp_org;
			} elseif ( strpos( $plugin_data['PluginURI'], 'github.com' ) > 0 ) {
				return $github;
			} elseif ( strpos( $plugin_data['PluginURI'], 'gitlab.com' ) > 0 ) {
				return $gitlab;
			} elseif ( strpos( $plugin_data['PluginURI'], 'bitbucket.org' ) > 0 ) {
				return $bitbucket;
			} elseif ( strpos( $plugin_data['PluginURI'], 'gitea.io' ) > 0 ) {
				return $gitea;
			}
		}

		// Under development plugins
		if ( file_exists( $fullpath . '.git/' ) ) {
			return $git;
		} elseif ( file_exists( $fullpath . '.hg/' ) ) {
			return $mercurial;
		} elseif ( file_exists( $fullpath . '.svn/' ) ) {
			return $subversion;
		}
		// TODO: check if the package can be retrieved via wpackagist..
		// else {
		// return $wpackagist;
		// }
		return 'uknown';
	}
}
