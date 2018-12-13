<?php
/**
 * GitHub Updater
 *
 * @package WP_DevOps
 * @author  Raruto
 * @license GPL-2.0+
 * @link    https://github.com/Raruto/wp-devops
 */

namespace Raruto\Controller;

use Raruto\Utils\Constants;
use Raruto\Utils\Util;

use Raruto\Model\WP\WP_Installer;

/**
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class WP_Dependency_Installer
 *
 * A lightweight class to add to WordPress plugins or themes to automatically install
 * required plugin dependencies. Uses a JSON config file to declare plugin dependencies.
 * It can install a plugin from w.org, GitHub, Bitbucket, or GitLab.
 *
 * based on: https://github.com/afragen/wp-dependency-installer
 */
class WP_Dependency_Installer {

	/**
	 * Holds the singleton instance.
	 *
	 * @var WP_Dependency_Installer
	 */
	private static $instance;

	/**
	 * Holds the installer instance.
	 *
	 * @var WP_Installer
	 */
	private $installer;

	/**
	 * Holds the JSON file contents.
	 *
	 * @var array $config
	 */
	protected $config = array();

	/**
	 * Holds names of installed dependencies for admin notices.
	 *
	 * @var array $notices
	 */
	protected $notices = array();

	/**
	 * Set to true/false to toggle \PanD.
	 *
	 * @var bool $dismissible_notices
	 *
	 * @link https://github.com/collizo4sky/persist-admin-notices-dismissal
	 */
	protected static $dismissible_notices;

	/**
	 * Set to true/false to toggle.
	 *
	 * @var bool $automatic_activate_required
	 */
	protected static $automatic_activate_required;

	/**
	 * Set to true/false to toggle.
	 *
	 * @var bool $automatic_install_required
	 */
	protected static $automatic_install_required;

	/**
	 * Array of required plugins' slugs.
	 *
	 * @var array $required_plugins
	 */
	protected static $required_plugins;

	/**
	 * Array of must have plugins' configs.
	 *
	 * @var array $must_have_plugins
	 */
	protected static $must_have_plugins;


	/**
	 * WP_Dependency_Installer constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_footer', array( $this, 'admin_footer' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'network_admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'wp_ajax_dependency_installer', array( $this, 'ajax_router' ) );

		self::$dismissible_notices         = false; // disable \PanD.
		self::$automatic_activate_required = false;
		self::$automatic_install_required  = false;

		self::$required_plugins = array();
		self::$must_have_plugins = array();

		$this->installer = WP_Installer::get_instance();
	}

	/**
	 * Check if a valid dependency installer json file is currently registered.
	 *
	 * @return boolean True if there is a valid $config file.
	 */
	public function is_running_config() {
		return isset( $this->config ) && count( $this->config ) > count( self::$must_have_plugins );
	}

	/**
	 * Check if a dependency is currently required.
	 *
	 * @param array $dependency
	 * @return boolean True if is required. Default: True
	 */
	private function is_required( &$dependency ) {
		if ( isset( $dependency['required'] ) ) {
			return ( true === $dependency['required'] || 'true' === $dependency['required'] );
		}
		if ( isset( $dependency['optional'] ) ) {
			return ( false === $dependency['optional'] || 'false' === $dependency['optional'] );
		}
		return true;
	}

	/**
	 * Check if a dependency should be automatically activated.
	 *
	 * @param array $dependency
	 * @return boolean
	 */
	private function is_automatic_activate( &$dependency ) {
		 $is_required = $this->is_required( $dependency );
		 return ! $is_required || ( $is_required && ! self::$automatic_activate_required );
	}

	/**
	 * Check if a dependency should be automatically installed.
	 *
	 * @param array $dependency
	 * @return boolean
	 */
	private function is_automatic_install( &$dependency ) {
		$is_required = $this->is_required( $dependency );
		return ! $is_required || ( $is_required && ! self::$automatic_install_required );
	}

	/**
	 * Singleton.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Init function.
	 */
	public static function init( $must_have_plugins ) {
		self::get_instance();
		self::$must_have_plugins = $must_have_plugins;
		self::$instance->register( $must_have_plugins );
		self::$instance->run();
	}

	/**
	 * Register wp-dependencies.json
	 */
	public function run() {
		$registered_configs = get_site_option( 'ghu_wp-dependencies' );
		if ( ! empty( $registered_configs ) ) {
			$this->register( $registered_configs );
		}
	}

	/**
	 * Determine if dependency is active or installed.
	 */
	public function admin_init() {
		// Initialize Persist admin Notices Dismissal dependency.
		if ( class_exists( '\PanD' ) ) {
			\PanD::init();
		}

		// Get the gears turning.
		$this->apply_config();

		// Generate admin notices.
		foreach ( $this->config as $slug => $dependency ) {
			$is_required = $this->is_required( $dependency );

			if ( $is_required ) {
				$this->hide_plugin_action_links( $slug );
			}

			if ( is_plugin_active( $slug ) ) {
				continue;
			}

			if ( Util::is_installed( $slug ) ) {
				if ( $this->is_automatic_activate( $dependency ) ) {
					$this->notices[] = array(
						'action'   => 'activate',
						'slug'     => $slug,
						'required' => $is_required,
						'text'     => sprintf( __( '%s.' ), $dependency['name'] ),
					);

				} else {
					$this->notices[] = $this->activate( $slug );
				}
			} elseif ( $this->is_automatic_install( $dependency ) ) {
				$this->notices[] = array(
					'action'   => 'install',
					'slug'     => $slug,
					'required' => $is_required,
					'text'     => sprintf( __( '%s.' ), $dependency['name'] ),
				);
			} else {
				$this->notices[] = $this->install( $slug );
			}
		}
	}

	/**
	 * Register jQuery AJAX.
	 */
	public function admin_footer() {
		?>
		<script>
			(function ($) {
				$(function () {
					$(document).on('click', '.wpdi-button', function () {
						var $this = $(this);
						var $parent = $(this).closest('p');
						$parent.html('Running...');
						$.post(ajaxurl, {
							action: 'dependency_installer',
							method: $this.attr('data-action'),
							slug  : $this.attr('data-slug')
						}, function (response) {
							$parent.html(response);
						});
					});
					$(document).on('click', '.dependency-installer .notice-dismiss', function () {
						var $this = $(this);
						$.post(ajaxurl, {
							action: 'dependency_installer',
							method: 'dismiss',
							slug  : $this.attr('data-slug')
						});
					});
				});
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Display admin notices / action links.
	 *
	 * @return bool/string false or Admin notice.
	 */
	public function admin_notices() {
		if ( ! current_user_can( 'update_plugins' ) ) {
			return false;
		}

		global $pagenow;
		if ('plugins.php' !== $pagenow && 'themes.php' !== $pagenow) {
			return false;
		}

		$message = null;
		foreach ( $this->notices as $notice ) {
			$status      = empty( $notice['status'] ) ? 'updated' : $notice['status'];
			$is_required = $this->is_required( $notice );
			if ( $is_required ) {
				$status = 'notice-error';
			}
			if ( ! empty( $notice['text'] ) ) {
				$plugin_name = esc_html( $notice['text'] );
			}
			if ( ! empty( $notice['action'] ) ) {
				$action  = esc_attr( $notice['action'] );
				$message = ' <a href="javascript:;" class="wpdi-button" data-action="' . $action . '" data-slug="' . $notice['slug'] . '">' . ucfirst( $action ) . ' Now &raquo;</a>';
			}
			if ( ! empty( $notice['status'] ) ) {
				$message = esc_html( $notice['message'] );
			}
			$dismissible          = isset( $notice['slug'] )
				? 'dependency-installer-' . dirname( $notice['slug'] ) . '-7'
				: null;
			$notice_alert_message = $is_required
				? __( 'required', 'wp-devops' )
				: __( 'optional', 'wp-devops' );
			$color                = $is_required ? '#d52121' : '#23912D';
			if ( self::$dismissible_notices && class_exists( '\PanD' ) && ! \PanD::is_admin_notice_active( $dismissible ) ) {
				continue;
			}
			?>
			<div data-dismissible="<?php echo $dismissible; ?>" class="<?php echo $status; ?> notice is-dismissible dependency-installer">
				<p><?php echo '<strong style="color: ' . $color . ';">[ ' . $notice_alert_message . ' ]</strong> <b style="font-family: Monospace;">' . $plugin_name . '</b>' . $message; ?></p>
				</div>
				<?php
		}
	}

	/**
	 * AJAX router.
	 */
	public function ajax_router() {
		$method    = isset( $_POST['method'] ) ? $_POST['method'] : '';
		$config    = isset( $_POST['config'] ) ? $_POST['config'] : '';
		$slug      = isset( $_POST['slug'] ) ? $_POST['slug'] : '';
		$whitelist = array( 'install', 'activate', 'dismiss', 'upload', 'delete' );

		if ( in_array( $method, $whitelist, true ) ) {
			$response = $this->$method( $method == 'upload' ? $config : $slug );
			echo $response['message'];
		}
		wp_die();
	}

	/**
	 * Register dependencies (supports multiple instances).
	 *
	 * @param array $config JSON config as string.
	 */
	public function register( $config ) {
		foreach ( $config as $dependency ) {
			$slug = $dependency['slug'];
			if ( ! isset( $this->config[ $slug ] ) || $this->is_required( $dependency ) ) {
				$this->config[ $slug ] = $dependency;
			}
		}
	}

	/**
	 * Upload dependencies.
	 *
	 * @param array $config JSON config as string.
	 */
	public function upload( $config ) {
		if ( empty( $config ) || ! is_array( $config ) && null === ( $config = json_decode( $config, true ) ) ) {
			return;
		}
		update_site_option( 'ghu_wp-dependencies', $config );
		$this->register( $config );
	}

	/**
	 * Delete dependencies.
	 * TODO: allow deleting specifc plugins instead of whole json config file
	 *
	 * @param string $slug Plugin slug.
	 */
	public function delete( $slug ) {
		delete_site_option( 'ghu_wp-dependencies' );
	}

	/**
	 * Process the registered dependencies.
	 */
	public function apply_config() {
		foreach ( $this->config as $dependency ) {
			if ( false == ( $download_link = $this->installer->get_download_link( $dependency ) ) ) {
				continue;
			}
			$this->config[ $dependency['slug'] ]['download_link'] = $download_link;
		}
	}

	/**
	 * Install and activate dependency.
	 *
	 * @param string $slug Plugin slug.
	 *
	 * @return bool|array false or Message.
	 */
	public function install( $slug ) {
		if ( ! isset( $this->config[ $slug ] ) ) {
			return false;
		}

		$dependency = $this->config[ $slug ];

		if ( ! isset( $dependency['slug'] ) ) {
			$dependency['slug'] = $slug;
		}

		$automatic_activate = $this->is_required( $dependency ) && self::$automatic_activate_required;

		$result = $this->installer->install( $dependency, $automatic_activate );

		return $result;

	}

	/**
	 * Activate dependency.
	 *
	 * @param string $slug Plugin slug.
	 *
	 * @return array Message.
	 */
	public function activate( $slug ) {
		if ( ! isset( $this->config[ $slug ] ) ) {
			return false;
		}

		$dependency = $this->config[ $slug ];

		if ( ! isset( $dependency['slug'] ) ) {
			$dependency['slug'] = $slug;
		}

		$result = $this->installer->activate( $dependency );

		return $result;

	}

	/**
	 * Dismiss admin notice for a week.
	 *
	 * @return array Empty Message.
	 */
	public function dismiss() {
		return array(
			'status'  => 'updated',
			'message' => '',
		);
	}

	/**
	 * Hide links from plugin row.
	 *
	 * @param $plugin_file
	 */
	public function hide_plugin_action_links( $plugin_file ) {
		self::$required_plugins[] = $plugin_file;
		add_filter( 'plugin_row_meta', array( &$this, 'row_meta' ), 15, 2 );

		// add_filter( 'network_admin_plugin_action_links_' . $plugin_file, array( $this, 'unset_action_links' ) );
		// add_filter( 'plugin_action_links_' . $plugin_file, array( $this, 'unset_action_links' ) );
		// add_action(
		// 'after_plugin_row_' . $plugin_file,
		// function( $plugin_file ) {
		// print( '<script>jQuery(".inactive[data-plugin=\'' . $plugin_file . '\']").attr("class", "active");</script>' );
		// print( '<script>jQuery(".active[data-plugin=\'' . $plugin_file . '\'] .check-column input").remove();</script>' );
		// }
		// );
	}

	// /**
	// * Unset plugin action links so mandatory plugins can't be modified.
	// *
	// * @param $actions
	// *
	// * @return mixed
	// */
	// public function unset_action_links( $actions ) {
	// if ( isset( $actions['edit'] ) ) {
	// unset( $actions['edit'] );
	// }
	// if ( isset( $actions['delete'] ) ) {
	// unset( $actions['delete'] );
	// }
	// if ( isset( $actions['deactivate'] ) ) {
	// unset( $actions['deactivate'] );
	// }
	//
	// return array_merge( $actions, array( 'required-plugin' => '<strong style="color:green; font-weight:700; display: inline-block;">' . __( 'Plugin dependency' . '</strong>' ) ) );
	// }
	/**
	 * Add an additional element to the row meta links.
	 *
	 * @param array  $links
	 * @param string $slug
	 *
	 * @return array $links
	 */
	public function row_meta( $links, $slug ) {
		if ( in_array( $slug, self::$required_plugins ) ) {
			$links[] = '<strong style="color:green; font-weight:700; /*display: inline-block;*/ float:right;">' . __( 'Plugin dependency', Constants::$PLUGIN_TEXT_DOMAIN ) . '</strong>';
		}
			return $links;
	}


}
