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

/*
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class WP GHU Rest Update
 *
 * Class that will add our custom table records
 *
 * @package Fragen\GitHub_Updater
 */
 class GHU_Rest_Update {

 	/**
 	 * Holds the singleton instance.
 	 *
 	 * @var GHU_Rest_Update
 	 */
 	private static $instance;

 	/**
 	 * GHU_Rest_Update constructor.
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
 	 * GHU_Rest_Update init function.
 	 */
 	public static function init() {
 		 $self = self::get_instance();
 		 $self->init_hooks();
 	}

 	public function init_hooks() {
		add_action( 'wp_ajax_github-updater-update', array( &$this, 'ajax_update' ) , 0 );
		add_action( 'wp_ajax_nopriv_github-updater-update', array( &$this, 'ajax_update' ), 0 );
		add_action( 'github_updater_pre_rest_process_request', array( &$this, 'pre_rest_process_request' ) );
		add_action( 'github_updater_post_rest_process_request', array( &$this, 'after_rest_process_request' ), 10, 2 );
 	}

 	public function ajax_update() {
		$this->datetime = current_time( 'mysql' );
		$this->update_resource = "";
 	}

	public function pre_rest_process_request() {
		if ( isset( $_REQUEST['plugin'] ) ) {
			$this->update_resource = $_REQUEST['plugin'];
		} elseif ( isset( $_REQUEST['theme'] ) ) {
			$this->update_resource = $_REQUEST['theme'];
		}
	}

	public function after_rest_process_request( $response, $status_code ) {
		$elapsed_time = trim(str_replace("ms", "", $response['elapsed_time']));
		if( (float) $elapsed_time >= 1000){
			$elapsed_time = number_format($elapsed_time / 1000, 2) . " s";
		} else {
			$elapsed_time = $elapsed_time . " ms";
		}

		$db_record = array(
			'status' => $status_code,
			'time' => $this->datetime ,
			'elapsed_time' => $elapsed_time,
			'update_resource' => $this->update_resource,
			'webhook_source' => $response['webhook']['webhook_source'],
		);

		// Create a new record within the GHU_TABLE_LOGS table
		WP_Rest_Log_Table::insert_db_record( $db_record );
	}

 }
