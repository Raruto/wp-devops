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

use Raruto\Controller\WP_Rest_Log_Table;
use Raruto\Model\JSON\GHUModel;
use Raruto\Model\JSON\ComposerModel;
use Raruto\Controller\WP_Dependency_Installer;

/*
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class GHU Tabs
 *
 * Class that will add some custom github updater tabs
 *
 * @package Raruto\WP_DevOps
 */
 class GHU_Tabs {

 	/**
 	 * Holds the singleton instance.
 	 *
 	 * @var GHU_Tabs
 	 */
 	private static $instance;

	private static $install_tabs;

 	/**
 	 * GHU_Tabs constructor.
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
 	 * GHU_Tabs init function.
 	 */
 	public static function init() {
 		 $self = self::get_instance();
 		 $self->init_hooks();
 	}

	public function init_hooks() {
		self::$install_tabs = [
			'github_updater_mass_import_export' => esc_html__( 'Mass Import/Export', 'github-updater' ),
			'github_updater_faq'                => esc_html__( 'FAQ', 'github-updater' ),
		];
		add_filter( 'github_updater_add_settings_tabs', array( &$this, 'after_add_settings_tabs' ), 11 );
		add_action( 'github_updater_add_admin_page', array( &$this, 'before_add_admin_page' ), 9 );
		add_action( 'github_updater_add_admin_page', array( &$this, 'after_add_admin_page'), 11 );
	}

	/**
	 * [after_add_settings_tabs description]
	 * @param [type] $tabs [description]
	 */
	public function after_add_settings_tabs( $tabs ) {
		return array_merge( $tabs, self::$install_tabs );
	}

	/**
	 * [add_admin_page description]
	 * @param [type] $tab [description]
	 */
	public function before_add_admin_page( $tab ) {
		if ( 'github_updater_settings' === $tab ) {
			self::before_github_updater_settings();
		}
		if ( 'github_updater_install_plugin' === $tab ) {
			self::before_github_updater_install_plugin();
		}
		if ( 'github_updater_install_theme' === $tab ) {
			self::before_github_updater_install_theme();
		}
	}

	/**
	 * [after_add_admin_page description]
	 * @param [type] $tab [description]
	 */
	public function after_add_admin_page( $tab ) {
		if ( 'github_updater_faq' === $tab ) {
			self::after_github_updater_faq();
		}
		if ( 'github_updater_remote_management' === $tab ) {
			self::after_github_updater_remote_management();
		}
		if ( 'github_updater_mass_import_export' === $tab ) {
			self::after_github_updater_mass_import_export();
		}
	}

	/**
	 * [before_github_updater_settings description]
	 * @return [type] [description]
	 */
	public function before_github_updater_settings(){
		print( '<hr style="clear: both;">' );
		printf( '<p>' . esc_html( 'Please refer to the %s for more details on how to configure the Git Hosts tabs') . '</p>', '<a href="https://github.com/afragen/github-updater/wiki/Settings#settings-tabs-for-git-hosts" target="_blank">wiki</a>' );
	}

	/**
	 * [before_github_updater_install_plugin description]
	 * @return [type] [description]
	 */
	public function before_github_updater_install_plugin(){
		printf(
			'<p>' . esc_html__( "Refer to %s for more details on remote %s installations", 'github-updater' ) . '</p><hr>',
			'<a href="https://github.com/afragen/github-updater/wiki/Remote-Installation" target="_blank">wiki</a>',
			'plugin'
		);
	}

	/**
	 * [before_github_updater_install_theme description]
	 * @return [type] [description]
	 */
	public function before_github_updater_install_theme(){
		printf(
			'<p>' . esc_html__( "Refer to %s for more details on remote %s installations", 'github-updater' ) . '</p><hr>',
			'<a href="https://github.com/afragen/github-updater/wiki/Remote-Installation" target="_blank">wiki</a>',
			'theme'
		);
	}

	/**
	 * [after_github_updater_faq description]
	 * @return [type] [description]
	 */
	public function after_github_updater_faq(){
		?>
			<p>
				<?php
					printf(esc_html__('FAQs are actively under construction, for any other doubts refer to: %s, %s, %s', 'github-updater'),
						'<a href="https://github.com/afragen/github-updater/wiki" target="_blank">wiki</a>',
						'<a href="https://github.com/afragen/github-updater/issues" target="_blank">github</a>',
						'<a href="https://github-updater.herokuapp.com/" target="_blank">slack</a>'
					);
				?>
			</p>
			<hr>
			<h3><?php print(esc_html__('Wiki Pages', 'github-updater')); ?></h3>
			<ol>
				<li><a target="_blank"  href="https://github.com/afragen/github-updater/wiki/Home">Home</a></li>
				<li><a target="_blank"  href="https://github.com/afragen/github-updater/wiki/General-Usage">General Usage</a></li>
				<li><a target="_blank"  href="https://github.com/afragen/github-updater/wiki/Installation">Installation</a></li>
				<li><a target="_blank"  href="https://github.com/afragen/github-updater/wiki/Settings">Settings</a></li>
				<li><a target="_blank"  href="https://github.com/afragen/github-updater/wiki/Usage">Usage</a></li>
				<li><a target="_blank"  href="https://github.com/afragen/github-updater/wiki/Background-Processing">Background Processing</a></li>
				<li><a target="_blank"  href="https://github.com/afragen/github-updater/wiki/Self-Hosted-or-Enterprise-Installations">Self-Hosted or Enterprise Installations</a></li>
				<li><a target="_blank"  href="https://github.com/afragen/github-updater/wiki/Versions-and-Branches">Versions and Branches</a></li>
				<li><a target="_blank"  href="https://github.com/afragen/github-updater/wiki/Language-Packs">Language Packs</a></li>
				<li><a target="_blank"  href="https://github.com/afragen/github-updater/wiki/Remote-Installation">Remote Installation</a></li>
				<li><a target="_blank"  href="https://github.com/afragen/github-updater/wiki/Remote-Management---RESTful-Endpoints">Remote Management / RESTful Endpoints</a></li>
				<li><a target="_blank"  href="https://github.com/afragen/github-updater/wiki/WP-CLI">WP-CLI Support</a></li>
				<li><a target="_blank"  href="https://github.com/afragen/github-updater/wiki/Messages">Messages</a></li>
				<li><a target="_blank"  href="https://github.com/afragen/github-updater/wiki/WordPress.org-Directory">WordPress.org Directory</a></li>
				<li><a target="_blank"  href="https://github.com/afragen/github-updater/wiki/Developer-Hooks">Developer Hooks</a></li>
				<li><a target="_blank"  href="https://github.com/afragen/github-updater/wiki/Translations">Translations</a></li>
				<li><a target="_blank"  href="https://github.com/afragen/github-updater/wiki/Extras-and-Credits">Extras and Credits</a></li>
			</ol>
			<hr>
			<?php $plugin_data = get_plugin_data(  WP_PLUGIN_DIR . "/github-updater/github-updater.php", false, false ); ?>
			<?php $plugin_version = $plugin_data['Version']; ?>
			<h3><?php print(esc_html__('Open issues', 'github-updater')); ?></h3>
			<ul class="ghu-issues" style="list-style:initial; margin-left:2%;"></ul>
			<hr>
			<h3>
				<?php print(esc_html__('Relase notes', 'github-updater')); ?>
				-
				<?php printf(esc_html__('see v. %s', 'github-updater'), $plugin_version); ?>
			</h3>
			<pre id ="github-changes" style="white-space: pre-wrap; height:50ch; overflow-y:scroll;"></pre>
			<hr>
			<script>
			/**
			 * Dynamically retrieve GitHub opened issues,
			 * see: https://developer.github.com/v3/issues/
			 */
			$.getJSON("https://api.github.com/repos/afragen/github-updater/issues", function (allIssues) {
					$.each(allIssues, function (i, issue) {
							$(".ghu-issues")
									.append("<li><a target=\"_blank\"  href=\"" + issue.html_url + "\">" + issue.title.replace(/\"/g,'&quot;') + "</a></li>")
					});
			});
			/**
			 * Dynamically retrieve GitHub release notes,
			 * see: https://developer.github.com/v3/contents/
			 */
			$.ajax({
				url: 'https://api.github.com/repos/afragen/github-updater/contents/CHANGES.md',
			})
			.done(function(data) {
				var pre = document.getElementById("github-changes");
				pre.innerHTML = atob(data.content);
			})
			.fail(function() {
				console.log("Failed to fetch data from GitHub API")
			});
			</script>
			<hr>
			<sub style="float:right; text-align:center;">
			<?php
				printf(
					esc_html__('GHU is a free software release under the %s', 'github-updater'),
					'<a href="https://github.com/afragen/github-updater/blob/master/LICENSE" target="_blank">"GNU General Public License v2.0"</a>'
				);
			?>
			<br>
			<?php
				printf(
					esc_html__('feel free to %s or %s to the plugin\'s author', 'github-updater'),
					'<a href="https://github.com/afragen/github-updater" target="_blank">contribute</a>',
					'<a href="http://thefragens.com/github-updater-donate" target="_blank">donate</a>'
				);
			?>
		</sub>
	<?php
	}

	/**
	 * [after_github_updater_remote_management description]
	 * @return [type] [description]
	 */
	public function after_github_updater_remote_management(){
		$table = new WP_Rest_Log_Table();
		$table->output();
	}

	/**
	 * [after_github_updater_mass_import_export description]
	 * @return [type] [description]
	 */
	public function after_github_updater_mass_import_export(){
		?>
		<style>
			/* div.json-beta-feature { display: none; } */
			.json-beta-feature button::after { content: " (beta) "; color: #f00; font-weight: 700; }
			pre.github-updater_json, pre.composer_json { padding:1em; background:#fff; border: 1px solid #ddd; }
			button.upload-json, button.delete-json { width: 100%; margin-top:1em; }
			button.download-json { width: 100%; }
			h3.json-alternative { width:100%; text-align:center; }
		</style>

		<?php
		echo '<div class="wrap">';
		// Readme.md link reminder
		echo '<sub>Here you can Import/Export a "github-updater.json" file to be able to easily install plugins (here or somewhere else)</sub>';
		$model = new GHUModel();
		$model->fill();
		$github_updater_json = $model->to_json();
		$model = new ComposerModel();
		$model->fill();
		$composer_json = $model->to_json();
		if(WP_Dependency_Installer::get_instance()->is_running_config()){
			echo '<button type="button" value="delete" id="delete-json_file" class="delete-json button button-secondary">Delete currently uploaded github-updater.json configuration file</button>';
			echo '<h3 class="json-alternative">or</h3>';
		}
		echo '<button type="button" value="upload" id="upload-json_file" class="upload-json button button-secondary">Upload a github-updater.json</button>';
		echo '<input id="json_file" type="file" accept=".json,application/json" hidden/>';
		echo '<h3 class="json-alternative">or</h3>';
		echo '<button type="button" value="download" id="download-github-updater_json" class="download-json button button-secondary">Download the following github-updater.json</button>';
		echo '<h4>github-updater.json</h4>';
		echo '<pre class="github-updater_json">'. $github_updater_json .'</pre>';
		echo '<div class="json-beta-feature">';
		echo '<h3 class="json-alternative">or</h3>';
		echo '<button type="button" value="download" id="download-composer_json" class="download-json button button-secondary">Download the following composer.json</button>';
		echo '<sub style="float:right;">NB: actually wpackagist doesn\'t provide an easy way to check if a plugin is really present on their repo, please be patient..</sub>';
		echo '<h4>composer.json</h4>';
		echo '<pre class="composer_json">'. $composer_json .'</pre>';
		echo '</div>';
		echo '</div>';
		?>
		<script>
		/**
		 * Originally taken from: https://stackoverflow.com/questions/42266658/download-text-from-html-pre-tag
		 */
		function saveTextAsFile(e) {
			var btnID = e.target.id;																							// eg. download-github-updater_json
			var textClass = btnID.substring(btnID.indexOf('-')+1, btnID.length);	// eg. github-updater_json
			var textToWrite = document.querySelector('pre.'+textClass).innerText;	// eg. pre.github-updater_json
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
		var input = document.getElementById('json_file');
		input.addEventListener('change', uploadJsonFile);
		var button00 = document.getElementById('delete-json_file');
		if( typeof button00 !== "undefined" && button00 != null )
			button00.addEventListener('click', deleteJsonFile);
		var button0 = document.getElementById('upload-json_file');
		button0.addEventListener('click', openDialog);
		var button1 = document.getElementById('download-github-updater_json');
		button1.addEventListener('click', saveTextAsFile);
		var button2 = document.getElementById('download-composer_json');
		button2.addEventListener('click', saveTextAsFile);
		</script>
		<?php
	}
 }
