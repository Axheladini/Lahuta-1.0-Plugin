
<?php
/**
 * Plugin Name: Lahuta 1.0 REST API
 * Description: An WP REST API plugin for Lahuta 1.0, for creative portfolio, artists, designers, photographers, coders. Ecommerce friendly!
 * Version: 1.0.0
 * Author: Agon Xheladini
 */

if (!defined('ABSPATH')) {
    exit;
}

define('LAHUTA_API_VERSION', '1.0.0');
define('LAHUTA_API_NAMESPACE', 'lahuta/v1');
    
require_once plugin_dir_path(__FILE__)
    . 'includes/class-api.php';

add_action('rest_api_init', function () {
    Lahuta_API::register_routes();
});