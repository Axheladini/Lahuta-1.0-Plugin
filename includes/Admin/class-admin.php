<?php
namespace LahutaRestAPI\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class LahutaAdmin { 

    public function __construct() {
        
        add_action( 'admin_menu', array( $this, 'add_admin_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'lahuta_enqueue_admin_assets' ) );
    
    }

    public function add_admin_page() {
        add_menu_page(
            'Lahuta SPA',              // Page title
            'Lahuta SPA',              // Menu title
            'manage_options',         // Capability
            'lahuta-admin',              // Menu slug
             array( $this, 'render_lahuta_admin_page' ), // Callback
            'dashicons-admin-generic', // Icon
            25                        // Position
        );
    }


    public function lahuta_enqueue_admin_assets( $hook ) {
        if ( 'toplevel_page_lahuta-admin' !== $hook ) {
            return;
        }

        wp_register_style(
            'bootstrap-icons',
             LAHUTA_PLUGIN_URL . 'dist/bootstrap-icons/bootstrap-icons.css',
             [],
            '1.0.0',
             false
        );

        wp_register_style(
            'bootstrap-style',
            LAHUTA_PLUGIN_URL . 'dist/bootstrap/css/bootstrap.min.css',
            [],
            '1.0.0',
            false
        );

        wp_register_style(
            'lahuta-style',
            LAHUTA_PLUGIN_URL . 'dist/css/style.css',
            [],
            (string) filemtime( LAHUTA_PLUGIN_DIR . 'dist/css/style.css' ),
            false
        );

        wp_register_script(
            'lahuta-script',
             LAHUTA_PLUGIN_URL . 'dist/js/admin.js',
            array( 'jquery' ),
            (string) filemtime( LAHUTA_PLUGIN_DIR . 'dist/js/admin.js' ),
            false
        );

        wp_register_script(
            'bootstrap-script',
             LAHUTA_PLUGIN_URL . 'dist/bootstrap/js/bootstrap.min.js',
            [],
            '1.0.0',
            false
        );

         wp_register_script(
            'validate-min',
             LAHUTA_PLUGIN_URL . 'dist/js/validate.min.js',
             array( 'jquery' ),
            '1.0.0',
            false
        );

        wp_enqueue_style('bootstrap-style');
        wp_enqueue_style('bootstrap-icons');
        wp_enqueue_style('lahuta-style');
        wp_enqueue_script('jquery');
        wp_enqueue_script('bootstrap-script');
        wp_enqueue_script('lahuta-script');
        wp_enqueue_script('validate-min');
        
    
    }

    public function render_lahuta_admin_page() {
        $gallery_categories = GalleryCategoryAdmin::get_categories_data();
        $gallery_category_load_error = '';

        if ( is_wp_error( $gallery_categories ) ) {
            error_log( 'Lahuta gallery categories could not be loaded: ' . $gallery_categories->get_error_message() );
            $gallery_categories = array();
            $gallery_category_load_error = __( 'Gallery categories could not be loaded.', 'lahuta-rest-api' );
        }
        ?>

        <div class="container-sm lahuta-admin-container">
            <h3><?php esc_html_e( 'Lahuta', 'lahuta-admin' ); ?></h3>
            <p><?php esc_html_e( 'Welcome to Lahuta RESTful WP Plugin for creatives', 'lahuta-admin' ); ?></p>
            
             
                    <ul class="nav nav-tabs lahuta-admin-nav" id="myTabs" role="tablist">
                        <li class="nav-item lahuta-admin-nav__item" role="presentation">
                            <button class="nav-link active"
                                    id="design-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#tab1"
                                    type="button"
                                    role="tab">
                                <?php echo __('Gallery', 'lahuta-plugin'); ?>
                            </button>
                        </li>
                        <li class="nav-item lahuta-admin-nav__item" role="presentation">
                            <button class="nav-link"
                                    id="development-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#tab2"
                                    type="button"
                                    role="tab">
                                <?php echo __('Plugin Settings', 'lahuta-plugin'); ?>
                            </button>
                        </li>
                        <li class="nav-item lahuta-admin-nav__item" role="presentation">
                            <button class="nav-link"
                                    id="artwork-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#tab3"
                                    type="button"
                                    role="tab">
                                About
                            </button>
                        </li>
                        <li class="nav-item lahuta-admin-nav__item" role="presentation">
                            <button class="nav-link"
                                    id="development-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#tab4"
                                    type="button"
                                    role="tab">
                                <?php echo __('Shop', 'lahuta-plugin'); ?>
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content lahuta-admin-tabs">
                        <div class="tab-pane fade show active lahuta-admin-tabs__content" id="tab1" role="tabpanel" aria-labelledby="design-tab">
                           
                            <!-- Gallery management starts here -->
                            <?php include LAHUTA_PLUGIN_DIR . 'includes/templates/lahuta-gallery-management.php'; ?>
                             <!-- Gallery management ENDS here -->

                        </div>

                        <div class="tab-pane fade lahuta-admin-tabs__content"
                            id="tab2" role="tabpanel"
                            aria-labelledby="development-tab">
                            <!-- Gallery management starts here -->
                            <?php include LAHUTA_PLUGIN_DIR . 'includes/templates/lahuta-plugin-settings.php'; ?>
                             <!-- Gallery management ENDS here -->
                        </div>

                        <div class="tab-pane fade lahuta-admin-tabs__content"
                            id="tab3" role="tabpanel"
                            aria-labelledby="artwork-tab">
                            <h4>Artwork</h4>
                            <p>Artists, painters, and illustrators.</p>
                        </div>

                        <div class="tab-pane fade lahuta-admin-tabs__content"
                            id="tab3" role="tabpanel"
                            aria-labelledby="artwork-tab">
                            <h4>Artwork</h4>
                            <p>Artists, painters, and illustrators.</p>
                        </div>
                    </div>  

        </div>

        <?php
    }
}
