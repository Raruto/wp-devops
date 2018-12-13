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

use Raruto\Controller\Admin\GHU_Icon_Links;
use Raruto\Controller\Admin\Hide_Plugins;
use Raruto\Controller\Admin\Local_Development;
use Raruto\Controller\Admin\Mass_ImportExport;
use Raruto\Controller\Admin\WP_DevOps_Links;
use Raruto\Utils\Constants;
use Raruto\Utils\Util;

/**
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

class WP_Screen {

	/**
	 * WP_Screen constructor.
	 */
	private function __construct() {
	}

	/**
	 * WP_Screen init function.
	 */
	public static function init() {
		 $self = new self();

		 GHU_Icon_Links::init();
		 Hide_Plugins::init();
		 Local_Development::init();
		 Mass_ImportExport::init();
		 WP_DevOps_Links::init();
	}

}
