<?php

namespace LahutaRestAPI;


use LahutaRestAPI\Admin\LahutaAdmin;
use LahutaRestAPI\Admin\GalleryCategoryAdmin;
use LahutaRestAPI\Admin\AddNewGallery;
use LahutaRestAPI\Admin\LahutaPluginSettings;
use LahutaRestAPI\PostTypes\GalleryPostType; // Added gallery post type
use LahutaRestAPI\PostTypes\GalleryCategoryTaxonomy; // Added gallery category taxonomy
use LahutaRestAPI\PostTypes\GalleryImagePostType;  // Added gallery image post type (single image details)
use LahutaRestAPI\REST\LahutaGalleriesWithImages; // Added REST API endpoint for galleries with images

defined( 'ABSPATH' ) || exit;

final class Plugin {

    private static ?self $instance = null;

    public static function get_instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init(): void {
        
        // Initialize admin classes
        new LahutaAdmin();
        new LahutaGalleriesWithImages(); // Initialize the REST API endpoint for galleries with images

        $gallery_category_admin = new GalleryCategoryAdmin();
        $gallery_category_admin->register();

        $add_new_gallery = new AddNewGallery();
        $add_new_gallery->register();

        $plugin_settings = new LahutaPluginSettings();
        $plugin_settings->register();

        // Initialize other classes
        $gallery_post_type = new GalleryPostType();
        $gallery_post_type->register();

        $gallery_category_taxonomy = new GalleryCategoryTaxonomy();
        $gallery_category_taxonomy->register();
    
        $gallery_image_post_type = new GalleryImagePostType();
        $gallery_image_post_type->register();
      
    }
}