<?php

namespace LahutaRestAPI\PostTypes;

defined( 'ABSPATH' ) || exit;

final class GalleryImagePostType {

    public function register(): void {

        $actions = array(
           'register_post_type', 
           'register_meta',
        );

       foreach ( $actions as $action ) {
           add_action( 'init', array( $this, $action ) );
       }
       
    }

    public function register_post_type(): void {

        register_post_type(
            'lahuta_gallery_image',
            array(
                'labels' => array(
                    'name'          => __( 'Gallery Images', 'lahuta-rest-api' ),
                    'singular_name' => __( 'Gallery Image', 'lahuta-rest-api' ),
                    'add_new_item'  => __( 'Add New Gallery Image', 'lahuta-rest-api' ),
                    'edit_item'     => __( 'Edit Gallery Image', 'lahuta-rest-api' ),
                ),
                'public'       => true,
                'show_ui'      => true,
                'show_in_rest' => true,
                'supports'     => array(
                    'title',
                    'editor',
                    'thumbnail',
                    'page-attributes',
                    'custom-fields',
                ),
            )
        );
    }

    /* Register the custom meta field for gallery ID */
    public function register_meta(): void {

        register_post_meta(
            'lahuta_gallery_image', 
            'gallery_id_custom', [
            'type'              => 'integer',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'absint',
            'auth_callback'     => [$this, 'meta_auth_callback'],
        ]);

        register_post_meta(
            'lahuta_gallery_image',
            'is_main_custom', [
            'type'              => 'boolean',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
            'auth_callback'     => [$this, 'meta_auth_callback'],
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