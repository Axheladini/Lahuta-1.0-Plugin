<?php

namespace LahutaRestAPI\PostTypes;

defined( 'ABSPATH' ) || exit;

final class GalleryPostType {
 

    public function register(): void {
        
       $actions = array(
           'register_post_type', 
           'register_meta_category_id',
        );

       foreach ( $actions as $action ) {
           add_action( 'init', array( $this, $action ) );
       }

    }
    
    /* Register the custom post type for galleries */
    public function register_post_type(): void {

        $labels = array(
            'name'               => __( 'Galleries', 'lahuta-rest-api' ),
            'singular_name'      => __( 'Gallery', 'lahuta-rest-api' ),
            'add_new_item'       => __( 'Add New Gallery', 'lahuta-rest-api' ),
            'edit_item'          => __( 'Edit Gallery', 'lahuta-rest-api' ),
            'new_item'           => __( 'New Gallery', 'lahuta-rest-api' ),
            'view_item'          => __( 'View Gallery', 'lahuta-rest-api' ),
            'search_items'       => __( 'Search Galleries', 'lahuta-rest-api' ),
            'not_found'          => __( 'No galleries found', 'lahuta-rest-api' ),
            'menu_name'          => __( 'Galleries', 'lahuta-rest-api' ),
        );

        register_post_type(
            'lahuta_gallery',
            array(
                'labels'          => $labels,
                'public'          => true,
                'show_ui'         => true,
                'show_in_rest'    => true,
                'rest_base'       => 'galleries',
                'has_archive'     => true,
                'rewrite'         => array(
                    'slug' => 'galleries',
                ),
                'menu_icon'       => 'dashicons-format-gallery',
                'supports'        => array(
                    'title',
                    'editor',
                    'excerpt',
                    'author',
                    'revisions',
                    'custom-fields',
                ),
                'taxonomies'      => array(
                    'lahuta_gallery_category',
                ),
            )
        );
    }
    
    /* Register the custom meta field for gallery category ID */
    public function register_meta_category_id(): void {

        register_post_meta(
            'lahuta_gallery', 
            'category_id_custom', [
            'type'              => 'integer',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'absint',
            'auth_callback'     => [$this, 'meta_auth_callback'],
        ]);

        
        register_post_meta(
            'lahuta_gallery', 
            'gallery_created_date', [
            'type'              => 'string',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => function( $value ) {

            
                $date = \DateTimeImmutable::createFromFormat( '!Y-m-d', (string) $value );
                return $date && $date->format( 'Y-m-d' ) === $value ? $value : '';

            },
            'auth_callback'     => function(){
                return current_user_can( 'edit_posts' );
            }
        ]);


    }

    public function meta_auth_callback(
        $allowed,
        $meta_key,
        $post_id,
        $user_id,
        $cap,
        $caps
    ) {
        return user_can($user_id, 'edit_post', $post_id);
    }

}