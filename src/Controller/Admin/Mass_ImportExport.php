<?php
/**
 * DevOps Tools
 *
 * @package WP_DevOps
 * @author  Raruto
 * @license GPL-2.0+
 * @link    https://github.com/Raruto/wp-devops
 */

namespace Raruto\Controller\Admin;

use Raruto\Controller\WP_Dependency_Installer;
use Raruto\Controller\WP_Installer;
use Raruto\Model\JSON\ComposerModel;
use Raruto\Model\JSON\GHUModel;
use Raruto\Model\JSON\GHUModelInterface;
use Raruto\Utils\Constants;
use Raruto\Utils\Util;

use Fragen\GitHub_Updater\Install;
use Fragen\Singleton;

/**
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Mass_ImportExport {

	/**
	 * Mass_ImportExport constructor.
	 */
	private function __construct() {
	}

	/**
	 * Mass_ImportExport init function.
	 */
	public static function init() {
		 $self = new self();
		 $self->init_hooks();
	}

	public function init_hooks() {

		if ( ! Util::is_admin_user() ) {
			return;
		}

		add_filter( 'bulk_actions-plugins', array( &$this, 'plugins_bulk_actions' ) );
		add_action( 'handle_bulk_actions-plugins', array( &$this, 'plugins_handle_bulk_actions' ), 10, 3 );

		add_action( 'admin_notices', array( &$this, 'plugins_bulk_action_admin_notice' ) );

		foreach ( array( 'plugins.php', 'plugin-install.php', 'themes.php', 'theme-install.php' ) as $screen ) {
			add_action( "admin_footer-{$screen}", array( &$this, 'addCustomImportButton' ) );
		}

		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
	}

	function enqueue_scripts( $hook ) {
		if ( 'plugins.php' != $hook ) {
			return;
		}
		// wp_enqueue_script( 'my_custom_script', get_template_directory_uri() . '/myscript.js' );
		?>
		<style>
		option[value="ghu-mass-export"], option[value="composer-mass-export"] { background: beige; }
		</style>
		<?php
	}

	/**
	 * Adds "Import" button on module list page
	 */
	public function addCustomImportButton() {
		global $current_screen;
		// Not our post type, exit earlier
		// You can remove this if condition if you don't have any specific post type to restrict to.
		// if ('module' != $current_screen->post_type) {
		// return;
		// }

		echo '<div class="wrap">';
		echo '<input id="json_file" type="file" accept=".json,application/json" hidden/>';
		echo '</div>';

		?>
		<script type="text/javascript">
		jQuery(document).ready( function($)
		{
		/**
		 * Originally taken from: https://stackoverflow.com/questions/42266658/download-text-from-html-pre-tag
		 */
		function saveTextAsFile(e) {
			var btnID = e.target.id;                                                                                            // eg. download-github-updater_json
			var textClass = btnID.substring(btnID.indexOf('-')+1, btnID.length);    // eg. github-updater_json
			var textToWrite = document.querySelector('pre.'+textClass).innerText;   // eg. pre.github-updater_json
			var json_pos = textClass.lastIndexOf('_json');
			var fileName = textClass.substring(0,json_pos) + "" + textClass.substring(json_pos+5);
			var textFileAsBlob = new Blob([textToWrite], {type:'text/plain'});
			var fileNameToSaveAs = fileName + ".json";
			var downloadLink = document.createElement("a");
			downloadLink.download = fileNameToSaveAs;
			downloadLink.innerHTML = "Download File";
			if (window.webkitURL != null) {
				// Chrome allows the link to be clicked without actually adding it to the DOM.
				downloadLink.href = window.webkitURL.createObjectURL(textFileAsBlob);
			} else {
				// Firefox requires the link to be added to the DOM before it can be clicked.
				downloadLink.href = window.URL.createObjectURL(textFileAsBlob);
				downloadLink.onclick = function(){
					document.body.removeChild(downloadLink);
				};
				downloadLink.style.display = "none";
				document.body.appendChild(downloadLink);
			}
			downloadLink.click();
		}
		function openDialog() {
			document.getElementById('json_file').click();
		}
		function uploadJsonFile(e) {
			var input = e.target;
			if(input.files && input.files[0]){
				var reader = new FileReader();
				reader.onload = function(e){
						var json_object = JSON.parse(e.target.result);
						$.post(ajaxurl, {
							contentType: "application/json",
							action: 'dependency_installer',
							method: 'upload',
							//config  : e.target.result,
							config  : json_object,
							complete: function(data){
										var timeout = setTimeout("location.reload(true);",5000);
										alert("After you press OK, this page will automatically refresh when done.\nMaybe you may have to refresh this page a couple of times to see some changes.\n\nPlease be patient...");
									}
						});
				};
				reader.readAsText(e.target.files[0]);
			}
		}
		function deleteJsonFile(e) {
			$.post(ajaxurl, {
				action: 'dependency_installer',
				method: 'delete',
				complete: function(data){
					var timeout = setTimeout("location.reload(true);",5000);
					alert("After you press OK, this page will automatically refresh when done.\nMaybe you may have to refresh this page a couple of times to see some changes.\n\nPlease be patient...");
				}
				});
		}
		<?php
		$buttons                = '';
		$current_action         = current_action();
		$current_file           = Util::remove_prefix( $current_action, 'admin_footer-' );
		$current_type           = Util::remove_suffix( $current_file, '.php' );
		$current_type           = Util::remove_suffix( $current_type, '-install' );
		$lazy_singularized_type = Util::remove_suffix( $current_type, 's' );
		if ( ! in_array( $current_file, array( 'plugin-install.php', 'theme-install.php', 'themes.php' ) ) ) {
			$install_page = $lazy_singularized_type . '-install.php';
			$buttons     .= '<a href="' . $install_page . '?tab=upload" class="page-title-action">' . __( 'Upload .zip file', Constants::$PLUGIN_TEXT_DOMAIN ) . '</a>';
		}
		if ( in_array( $lazy_singularized_type, array( 'plugin', 'theme' ) ) ) {
			if ( WP_Dependency_Installer::get_instance()->is_running_config() ) {
				$buttons .= '<a href="#" class="devops-mass-import delete-json_file page-title-action" style="background-color: #F9F163; color: #414143;">' . __( 'Delete uploaded .json file', Constants::$PLUGIN_TEXT_DOMAIN ) . '</a>';
			} else {
				$buttons .= '<a href="#" class="devops-mass-import upload-json_file page-title-action">' . __( 'Upload .json file', Constants::$PLUGIN_TEXT_DOMAIN ) . '</a>';
			}

			$buttons .= '<a href="options-general.php?page=github-updater&tab=github_updater_install_' . $lazy_singularized_type . '" class="page-title-action">' . __( 'Clone a git repository', Constants::$PLUGIN_TEXT_DOMAIN ) . '</a>';
		}
		if ( strlen( $buttons ) > 0 ) {
					$buttons = addcslashes( $buttons, "'" );
					printf( 'jQuery(jQuery(".wrap .page-title-action")[0]).after(\'%s\');', $buttons );
		}
		?>

		var input = document.getElementById('json_file');
		input.addEventListener('change', uploadJsonFile);
		var button00 = document.querySelectorAll('.devops-mass-import.delete-json_file');
		for (var i = 0; i < button00.length; i++) {
			if( typeof button00[i] !== "undefined" && button00[i] != null )
				button00[i].addEventListener('click', deleteJsonFile);
		}
		var button0 = document.querySelectorAll('.devops-mass-import.upload-json_file');
		for (var i = 0; i < button0.length; i++) {
			button0[i].addEventListener('click', openDialog);
		}
		});
		</script>
		<?php
	}

	/**
	 * Add our actions to the list of available plugins.php bulk actions
	 *
	 * @param  array $actions Key is the name of the action.
	 * @return array $actions The modified list.
	 */
	public function plugins_bulk_actions( $actions ) {
		$actions['composer-mass-export'] = __( 'Download composer.json', Constants::$PLUGIN_TEXT_DOMAIN );
		$actions['ghu-mass-export']      = __( 'Download github-updater.json', Constants::$PLUGIN_TEXT_DOMAIN );
		return $actions;
	}

	/**
	 * Handle our plugins.php bulk actions
	 *
	 * @param  string $redirect_to_url
	 * @param  string $action_name  Selected Bulk Action name.
	 * @param  array  $plugins_path  An array of selected plugins slugs.
	 *
	 * @link https://make.wordpress.org/core/2016/10/04/custom-bulk-actions/
	 */
	public function plugins_handle_bulk_actions( $redirect_to_url, $action_name, $plugins_paths ) {

		switch ( $action_name ) {
			case 'ghu-mass-export':
				$model     = new GHUModel();
				$file_name = 'github-updater';
				break;
			case 'composer-mass-export':
				$model     = new ComposerModel();
				$file_name = 'composer';
				break;
			default:
				return; // If it's not our action, it's not our concern.
		}

		// Check some privileges
		if ( ! current_user_can( 'administrator' ) ) {
			wp_die( "You don't seem to have the right permissions to do this." );
		}

		// Apply the actual action
		$model->fill( $plugins_paths );
		$json = $model->to_json();

		/**
		 * Create a json file download link "on the fly"
		 */
		header( 'Content-type: application/json' );
		header( 'Content-Disposition: attachment; filename="' . $file_name . '.json"' );
		echo $json;
		// echo json_encode( $plugins_paths, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		exit();
		// //
		// // Redirect the user
		// // wp_redirect(
		// $redirect_to_url = add_query_arg(
		// array(
		// 'updated' => '1', // count( $post_ids ),
		// ),
		// $redirect_to_url
		// )
		// );
		// // exit();
		// return $redirect_to_url;
	}

	public function plugins_bulk_action_admin_notice() {
		if ( ! empty( $_REQUEST['updated'] ) ) {
			$count = intval( $_REQUEST['updated'] );
			printf(
				'<div id="message" class="updated fade">' .
				_n(
					'%s plugin updated',
					'%s plugins updated',
					$count,
					Constants::$PLUGIN_TEXT_DOMAIN
				) . '</div>', $count
			);
		}
	}

}
