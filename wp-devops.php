<?php
/**
 * Plugin Name: WP DevOps Tools
 * Description: Collection of useful WordPress Development Operations Tools
 * Plugin URI: https://github.com/Raruto/wp-devops
 * GitHub Plugin URI: https://github.com/Raruto/wp-devops
 * Text Domain: wp-devops
 * Author: Raruto
 * Author URI: https://github.com/Raruto
 *
 * Version: 0.0.1
 */

// Composer Autoloader
require __DIR__ . '/vendor/autoload.php';

// Plugin Initialization
add_action( 'plugins_loaded', array( 'Raruto\\Main', 'init' ) );
register_activation_hook( __FILE__, array( 'Raruto\\Main', 'install') );

// register_activation_hook( __FILE__, array( 'Raruto\\Main', 'register_must_have_plugins' ) );
