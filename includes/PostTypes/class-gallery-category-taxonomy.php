<?php

namespace LahutaRestAPI\PostTypes;

defined( 'ABSPATH' ) || exit;

final class GalleryCategoryTaxonomy {

    public function register(): void {
        add_action(
            'init',
            array( $this, 'register_taxonomy' )
        );
    }

    public function register_taxonomy(): void {

        register_taxonomy(
            'lahuta_gallery_category',
            array( 'lahuta_gallery' ),
            array(
                'labels' => array(
                    'name'          => __( 'Gallery Categories', 'lahuta-rest-api' ),
                    'singular_name' => __( 'Gallery Category', 'lahuta-rest-api' ),
                    'search_items'  => __( 'Search Gallery Categories', 'lahuta-rest-api' ),
                    'all_items'     => __( 'All Gallery Categories', 'lahuta-rest-api' ),
                    'edit_item'     => __( 'Edit Gallery Category', 'lahuta-rest-api' ),
                    'add_new_item'  => __( 'Add New Gallery Category', 'lahuta-rest-api' ),
                    'menu_name'     => __( 'Categories', 'lahuta-rest-api' ),
                ),
                'public'            => true,
                'hierarchical'      => true,
                'show_admin_column' => true,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'rest_base'         => 'gallery-categories',
                'rewrite'           => array(
                    'slug' => 'gallery-category',
                ),
            )
        );
    }
}