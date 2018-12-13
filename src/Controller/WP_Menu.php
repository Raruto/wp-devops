<?php
/**
 * DevOps Tools
 *
 * @package WP_DevOps
 * @author  Raruto
 * @license GPL-2.0+
 * @link    https://github.com/Raruto/wp-devops
 */

namespace Raruto\Controller;

use Raruto\Utils\Constants;
use Raruto\Utils\Util;

/**
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

class WP_Menu {

	/**
	 * Holds the singleton instance.
	 *
	 * @var WP_Menu
	 */
	private static $instance;

	/**
	 * WP_Menu constructor.
	 */
	private function __construct() {
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
	 * WP_Menu init function.
	 */
	public static function init() {
		 $self = self::get_instance();
		 $self->init_hooks();
	}

	public function init_hooks() {
		// add_action( 'admin_bar_menu', array( &$this, 'add_link_to_admin_bar_start' ), 0 );
		add_action( 'admin_bar_menu', array( &$this, 'add_link_to_admin_bar_end' ), 9999 );
		if ( Util::is_admin_user() ) {
			add_action( 'admin_print_scripts', array( &$this, 'add_css_footer' ) );
			add_action( 'wp_footer', array( &$this, 'add_css_footer' ) );
		}
	}

	public function add_link_to_admin_bar_start( $admin_bar ) {

		$args = array(
			'id'    => 'wpbar-devops',
			'title' => '<span class="ab-icon" style="background: #0073aa; margin: 0;"><img style="width: 1rem;height: 1rem;" src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNzkyIiBoZWlnaHQ9IjE3OTIiIHZlcnNpb249IjEiPjxwYXRoIGZpbGw9IiNmZjAiIGQ9Ik0xMDI0IDEzNzV2LTE5MHEwLTE0LTktMjN0LTIzLTEwSDgwMHEtMTMgMC0yMiAxMHQtMTAgMjN2MTkwcTAgMTQgMTAgMjR0MjIgOWgxOTJxMTMgMCAyMy05dDktMjR6bS0yLTM3NGwxOC00NTlxMC0xMi0xMC0xOS0xMy0xMS0yNC0xMUg3ODZxLTExIDAtMjQgMTEtMTAgNy0xMCAyMWwxNyA0NTdxMCAxMCAxMCAxN3QyNCA2aDE4NXExNCAwIDI0LTZ0MTAtMTd6bS0xNC05MzRsNzY4IDE0MDhxMzUgNjMtMiAxMjYtMTcgMjktNDYgNDZ0LTY0IDE3SDEyOHEtMzQgMC02My0xN3QtNDctNDZxLTM3LTYzLTItMTI2TDc4NCA2N3ExNy0zMSA0Ny00OXQ2NS0xOCA2NSAxOCA0NyA0OXoiLz48L3N2Zz4="></span>',
			'href'  => '#',
		);

		$admin_bar->add_node( $args );

	}

	public function add_link_to_admin_bar_end( $admin_bar ) {

		$admin_bar->add_group(
			array(
				'parent' => 'site-name',
				// 'parent' => 'wpbar-devops',
				'id'     => Constants::$PLUGIN_TEXT_DOMAIN,
			)
		);
		$args = array(
			'parent' => 'site-name',
			'id'     => 'media-libray',
			'title'  => __( 'Media' ),
			'href'   => esc_url( network_admin_url( 'upload.php' ) ),
			'meta'   => false,
		);
		$admin_bar->add_node( $args );

		if ( Util::is_admin_user() ) {
				$args = array(
					'parent' => 'site-name',
					'id'     => 'plugins',
					'title'  => __( 'Plugins' ),
					'href'   => esc_url( network_admin_url( 'plugins.php' ) ),
					'meta'   => false,
				);
				$admin_bar->add_node( $args );

				$args = array(
					'parent' => Constants::$PLUGIN_TEXT_DOMAIN,
					'id'     => 'debug-log',
					'title'  => esc_html__( 'debug.log', Constants::$PLUGIN_TEXT_DOMAIN ),
					'href'   => esc_url( network_site_url( 'wp-content/debug.log' ) ),
					'meta'   => false,
				);
				$admin_bar->add_node( $args );

				$args = array(
					'parent' => Constants::$PLUGIN_TEXT_DOMAIN,
					'id'     => 'transients-manager',
					'title'  => esc_html__( 'transients', Constants::$PLUGIN_TEXT_DOMAIN ),
					'href'   => esc_url( network_admin_url( 'tools.php' ) ) . '?page=pw-transients-manager">',
					'meta'   => false,
				);
				$admin_bar->add_node( $args );

				$args = array(
					'parent' => Constants::$PLUGIN_TEXT_DOMAIN,
					'id'     => 'cron-manager',
					'title'  => esc_html__( 'cron jobs', Constants::$PLUGIN_TEXT_DOMAIN ),
					'href'   => esc_url( network_admin_url( 'tools.php' ) ) . '?page=advanced-cron-manager">',
					'meta'   => false,
				);
				$admin_bar->add_node( $args );
		}
	}

	public function add_css_footer() {
		// echo '<p>This is inserted at the bottom</p>';
		$is_localhost = Util::is_localhost();
		$warning_message = $is_localhost ? 'localhost' : network_site_url( '/' );
		$warning_color = $is_localhost ? '#008000' : '#dc3232';
		?>
		<style>

		#wpadminbar #wp-admin-bar-site-name > .ab-item::after {
			content: " - <?php echo $warning_message ?>";
			font-weight: 800;
			font-family: Monospace;
			color: #fff;
		}

		#wpadminbar #wp-admin-bar-site-name > .ab-item {
			/*background-color: #0073aa;*/
			background-color: <?php echo $warning_color; ?>;
			color: #ff0;
		}

		#wpadminbar #wp-admin-bar-site-name > .ab-item::before {
			<?php if(false == $is_localhost) : ?>
			background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNzkyIiBoZWlnaHQ9IjE3OTIiIHZlcnNpb249IjEiPjxwYXRoIGZpbGw9IiNmZjAiIGQ9Ik0xMDI0IDEzNzV2LTE5MHEwLTE0LTktMjN0LTIzLTEwSDgwMHEtMTMgMC0yMiAxMHQtMTAgMjN2MTkwcTAgMTQgMTAgMjR0MjIgOWgxOTJxMTMgMCAyMy05dDktMjR6bS0yLTM3NGwxOC00NTlxMC0xMi0xMC0xOS0xMy0xMS0yNC0xMUg3ODZxLTExIDAtMjQgMTEtMTAgNy0xMCAyMWwxNyA0NTdxMCAxMCAxMCAxN3QyNCA2aDE4NXExNCAwIDI0LTZ0MTAtMTd6bS0xNC05MzRsNzY4IDE0MDhxMzUgNjMtMiAxMjYtMTcgMjktNDYgNDZ0LTY0IDE3SDEyOHEtMzQgMC02My0xN3QtNDctNDZxLTM3LTYzLTItMTI2TDc4NCA2N3ExNy0zMSA0Ny00OXQ2NS0xOCA2NSAxOCA0NyA0OXoiLz48L3N2Zz4=) !important;
			<?php else : ?>
			background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGFyaWEtaGlkZGVuPSJ0cnVlIiB3aWR0aD0iMTQiIGhlaWdodD0iMTYiIHN0eWxlPSItbXMtdHJhbnNmb3JtOnJvdGF0ZSgzNjBkZWcpOy13ZWJraXQtdHJhbnNmb3JtOnJvdGF0ZSgzNjBkZWcpO3RyYW5zZm9ybTpyb3RhdGUoMzYwZGVnKSI+PHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBkPSJNOS41IDNMOCA0LjUgMTEuNSA4IDggMTEuNSA5LjUgMTMgMTQgOCA5LjUgM3ptLTUgMEwwIDhsNC41IDVMNiAxMS41IDIuNSA4IDYgNC41IDQuNSAzeiIgZmlsbD0iI2ZmMCIvPjwvc3ZnPg==) !important;
			<?php endif; ?>
			/*background-image: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PHN2ZyAgIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIgICB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIiAgIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyIgICB4bWxuczpzdmc9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgICB4bWxuczpzb2RpcG9kaT0iaHR0cDovL3NvZGlwb2RpLnNvdXJjZWZvcmdlLm5ldC9EVEQvc29kaXBvZGktMC5kdGQiICAgeG1sbnM6aW5rc2NhcGU9Imh0dHA6Ly93d3cuaW5rc2NhcGUub3JnL25hbWVzcGFjZXMvaW5rc2NhcGUiICAgd2lkdGg9IjE2Ljk2NDYwNyIgICBoZWlnaHQ9IjE1LjczNTA1IiAgIHZlcnNpb249IjEiICAgaWQ9InN2ZzIiICAgaW5rc2NhcGU6dmVyc2lvbj0iMC45MSByMTM3MjUiICAgc29kaXBvZGk6ZG9jbmFtZT0iaW5kZXguc3ZnIj4gIDxtZXRhZGF0YSAgICAgaWQ9Im1ldGFkYXRhMTAiPiAgICA8cmRmOlJERj4gICAgICA8Y2M6V29yayAgICAgICAgIHJkZjphYm91dD0iIj4gICAgICAgIDxkYzpmb3JtYXQ+aW1hZ2Uvc3ZnK3htbDwvZGM6Zm9ybWF0PiAgICAgICAgPGRjOnR5cGUgICAgICAgICAgIHJkZjpyZXNvdXJjZT0iaHR0cDovL3B1cmwub3JnL2RjL2RjbWl0eXBlL1N0aWxsSW1hZ2UiIC8+ICAgICAgICA8ZGM6dGl0bGU+PC9kYzp0aXRsZT4gICAgICA8L2NjOldvcms+ICAgIDwvcmRmOlJERj4gIDwvbWV0YWRhdGE+ICA8ZGVmcyAgICAgaWQ9ImRlZnM4IiAvPiAgPHNvZGlwb2RpOm5hbWVkdmlldyAgICAgcGFnZWNvbG9yPSIjZmZmZmZmIiAgICAgYm9yZGVyY29sb3I9IiM2NjY2NjYiICAgICBib3JkZXJvcGFjaXR5PSIxIiAgICAgb2JqZWN0dG9sZXJhbmNlPSIxMCIgICAgIGdyaWR0b2xlcmFuY2U9IjEwIiAgICAgZ3VpZGV0b2xlcmFuY2U9IjEwIiAgICAgaW5rc2NhcGU6cGFnZW9wYWNpdHk9IjAiICAgICBpbmtzY2FwZTpwYWdlc2hhZG93PSIyIiAgICAgaW5rc2NhcGU6d2luZG93LXdpZHRoPSIxMzY2IiAgICAgaW5rc2NhcGU6d2luZG93LWhlaWdodD0iNzE1IiAgICAgaWQ9Im5hbWVkdmlldzYiICAgICBzaG93Z3JpZD0iZmFsc2UiICAgICBmaXQtbWFyZ2luLXRvcD0iMCIgICAgIGZpdC1tYXJnaW4tbGVmdD0iMCIgICAgIGZpdC1tYXJnaW4tcmlnaHQ9IjAiICAgICBmaXQtbWFyZ2luLWJvdHRvbT0iMCIgICAgIGlua3NjYXBlOnpvb209IjE2Ljg1NzE0MyIgICAgIGlua3NjYXBlOmN4PSItMC41NTk1NTgyNCIgICAgIGlua3NjYXBlOmN5PSI1LjA1MTUzMyIgICAgIGlua3NjYXBlOndpbmRvdy14PSItOCIgICAgIGlua3NjYXBlOndpbmRvdy15PSIyMiIgICAgIGlua3NjYXBlOndpbmRvdy1tYXhpbWl6ZWQ9IjEiICAgICBpbmtzY2FwZTpjdXJyZW50LWxheWVyPSJzdmcyIiAvPiAgPHBhdGggICAgIGQ9Im0gOS42OTI2NzYyLDEzLjAwMjIyIDAsLTEuNzk2NjcgcSAwLC0wLjEzMjM4IC0wLjA4NTEsLTAuMjE3NDkgLTAuMDg1MSwtMC4wODUxIC0wLjIxNzQ5LC0wLjA5NDYgbCAtMS44MTU1OCwwIHEgLTAuMTIyOTMsMCAtMC4yMDgwNCwwLjA5NDYgLTAuMDg1MSwwLjA5NDYgLTAuMDk0NiwwLjIxNzQ5IGwgMCwxLjc5NjY3IHEgMCwwLjEzMjM5IDAuMDk0NiwwLjIyNjk1IDAuMDk0NiwwLjA5NDYgMC4yMDgwNCwwLjA4NTEgbCAxLjgxNTU4LDAgcSAwLjEyMjkzLDAgMC4yMTc0OSwtMC4wODUxIDAuMDk0NiwtMC4wODUxIDAuMDg1MSwtMC4yMjY5NSB6IG0gLTAuMDE4OSwtMy41MzY2IDAuMTcwMjEsLTQuMzQwMzggcSAwLC0wLjExMzQ3IC0wLjA5NDYsLTAuMTc5NjYgLTAuMTIyOTMsLTAuMTA0MDIgLTAuMjI2OTUsLTAuMTA0MDIgbCAtMi4wODAzNiwwIHEgLTAuMTA0MDIsMCAtMC4yMjY5NSwwLjEwNDAyIC0wLjA5NDYsMC4wNjYyIC0wLjA5NDYsMC4xOTg1OCBsIDAuMTYwNzYsNC4zMjE0NiBxIDAsMC4wOTQ2IDAuMDk0NiwwLjE2MDc2IDAuMDk0NiwwLjA2NjIgMC4yMjY5NSwwLjA1NjcgbCAxLjc0OTM5LDAgcSAwLjEzMjM4LDAgMC4yMjY5NCwtMC4wNTY3IDAuMDk0NiwtMC4wNTY3IDAuMDk0NiwtMC4xNjA3NiB6IG0gLTAuMTMyMzksLTguODMyMDUgNy4yNjIzMjk4LDEzLjMxNDI3IHEgMC4zMzA5NywwLjU5NTc0IC0wLjAxODksMS4xOTE0OCAtMC4xNjA3NiwwLjI3NDIzIC0wLjQzNDk5LDAuNDM0OTggLTAuMjc0MjIsMC4xNjA3NSAtMC42MDUxOSwwLjE2MDc1IGwgLTE0LjUyNDY1OTgsMCBxIC0wLjMyMTUxMDA0LDAgLTAuNTk1NzQwMDQsLTAuMTYwNzUgLTAuMjc0MjMsLTAuMTYwNzUgLTAuNDQ0NDQsLTAuNDM0OTggLTAuMzQ5ODgsLTAuNTk1NzQgLTAuMDE4OSwtMS4xOTE0OCBMIDcuNDIzMjI2MiwwLjYzMzU3IFEgNy41ODM5NzYyLDAuMzQwNDMgNy44Njc2NjYyLDAuMTcwMjIgOC4xNTEzNDYyLDAgOC40ODIzMTYyLDAgcSAwLjMzMDk3LDAgMC42MTQ2NSwwLjE3MDIyIDAuMjgzNjksMC4xNzAyMSAwLjQ0NDQ0LDAuNDYzMzUgeiIgICAgIGlkPSJwYXRoNCIgICAgIGlua3NjYXBlOmNvbm5lY3Rvci1jdXJ2YXR1cmU9IjAiICAgICBzdHlsZT0iZmlsbDojZmZmZjAwIiAvPjwvc3ZnPg==)!important;*/
			background-size: 100%;
			background-position: center center;
			background-repeat: no-repeat;
			content: " " !important;
			display: block;
			width: 16px;
			height: 21px;
		}

		</style>
		<?php
	}
}
