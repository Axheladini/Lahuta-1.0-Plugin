<?php
/**
 * Plugin Name: Lahuta 1.0 REST API
 * Description: A plugin for building SPA's for Artists, designers, photographers, and other creatives. Through WordPress REST API.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.4.15
 * Author: Agon Xheladini
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: lahuta-rest-api
 *
 * @package LahutaRestAPI
 */

if (!defined('ABSPATH')) {
    exit;
}

define('LAHUTA_API_VERSION', '1.0.0');
define('LAHUTA_API_NAMESPACE', 'LahutaRestAPI');
define('LAHUTA_PLUGIN_FILE', __FILE__ );
define('LAHUTA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define('LAHUTA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

//Files for require_once
$files = array(
  'includes/class-plugin.php',
  'includes/class-activator.php',
  'includes/class-deactivator.php',
  'includes/Admin/class-admin.php',
  'includes/Admin/class-gallery-category-admin.php',
  'includes/Admin/class-add-new-gallery.php',
  'includes/PostTypes/class-gallery-post-type.php',
  'includes/PostTypes/class-gallery-category-taxonomy.php',
  'includes/PostTypes/class-gallery-image-post-type.php',
  'includes/REST/class-lauhta-galleries-with-images.php'
);

foreach ( $files as $file ) {
    require_once LAHUTA_PLUGIN_DIR . $file;
}


register_activation_hook(
    LAHUTA_PLUGIN_FILE,
    array( 'LahutaRestAPI\\Activator', 'activate' )
);

register_deactivation_hook(
    LAHUTA_PLUGIN_FILE,
    array( 'LahutaRestAPI\\Deactivator', 'deactivate' )
);

add_action(
    'plugins_loaded',
    static function () {
        \LahutaRestAPI\Plugin::get_instance()->init();
    }
);