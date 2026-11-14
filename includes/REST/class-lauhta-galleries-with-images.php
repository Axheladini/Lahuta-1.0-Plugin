<?php

namespace LahutaRestAPI\REST;

defined( 'ABSPATH' ) || exit;

final class LahutaGalleriesWithImages {

    private const GALLERY_POST_TYPE = 'lahuta_gallery';
    private const IMAGE_POST_TYPE = 'lahuta_gallery_image';
    private const CATEGORY_META_KEY = 'category_id_custom';
    private const GALLERY_META_KEY = 'gallery_id_custom';
    private const MAIN_IMAGE_META_KEY = 'is_main_custom';

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes(): void {
        register_rest_route(
            'wp/v2',
            '/lahuta-galleries',
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_galleries' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    self::CATEGORY_META_KEY => array(
                        'required'          => true,
                        'type'              => 'integer',
                        'sanitize_callback' => 'absint',
                        'validate_callback' => static function ( $value ): bool {
                            return is_numeric( $value ) && (int) $value > 0;
                        },
                    ),
                ),
            )
        );
    }

    public function get_galleries( \WP_REST_Request $request ): \WP_REST_Response {
        $category_id = absint( $request->get_param( self::CATEGORY_META_KEY ) );
        $galleries = get_posts(
            array(
                'post_type'      => self::GALLERY_POST_TYPE,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'lahuta_gallery_category',
                        'field'    => 'term_id',
                        'terms'    => $category_id,
                    ),
                ),
            )
        );

        if ( ! $galleries ) {
            return new \WP_REST_Response( array(), 200 );
        }

        $gallery_ids = wp_list_pluck( $galleries, 'ID' );
        $image_posts = get_posts(
            array(
                'post_type'      => self::IMAGE_POST_TYPE,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    array(
                        'key'     => self::GALLERY_META_KEY,
                        'value'   => $gallery_ids,
                        'compare' => 'IN',
                        'type'    => 'NUMERIC',
                    ),
                ),
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
            )
        );

        $images_by_gallery = array();

        foreach ( $image_posts as $image ) {
            $gallery_id = absint( get_post_meta( $image->ID, self::GALLERY_META_KEY, true ) );
            $formatted = $this->format_image( $image );

            $images_by_gallery[ $gallery_id ][] = $formatted;
        }

        $response = array_map(
            function ( \WP_Post $gallery ) use ( $images_by_gallery ): array {
                $images = $images_by_gallery[ $gallery->ID ] ?? array();
                $main_image = null;

                foreach ( $images as $image ) {
                    if ( $image['is_main_custom'] ) {
                        $main_image = $image;
                        break;
                    }
                }

                return $this->format_gallery( $gallery, $images, $main_image );
            },
            $galleries
        );

        return new \WP_REST_Response( $response, 200 );
    }

    private function format_gallery( \WP_Post $gallery, array $images, ?array $main_image ): array {
        $gallery_id = $gallery->ID;
        $content    = apply_filters( 'the_content', $gallery->post_content );
        $custom_fields = $this->get_custom_fields( $gallery_id );

        return array(
            'id'            => $gallery_id,
            'title'         => get_the_title( $gallery_id ),
            'slug'          => $gallery->post_name,
            'content'       => $content,
            'editor'        => $content,
            'excerpt'       => $gallery->post_excerpt,
            'custom_fields' => $custom_fields,
            'permalink'     => get_permalink( $gallery_id ),
            'main_image'    => $main_image,
            'images'        => $images,
        );
    }

    private function format_image( \WP_Post $image ): array {
        $image_id      = $image->ID;
        $attachment_id = absint( get_post_thumbnail_id( $image_id ) );
        $full_image    = $attachment_id ? wp_get_attachment_image_src( $attachment_id, 'full' ) : false;
        $thumbnail     = $attachment_id ? wp_get_attachment_image_src( $attachment_id, 'thumbnail' ) : false;
        $custom_fields = $this->get_custom_fields( $image_id );
        $content       = apply_filters( 'the_content', $image->post_content );
        $width         = $full_image ? (int) $full_image[1] : 0;
        $height        = $full_image ? (int) $full_image[2] : 0;

        return array(
            'id'                => $image_id,
            'title'             => get_the_title( $image_id ),
            'slug'              => $image->post_name,
            'content'           => $content,
            'editor'            => $content,
            'gallery_id_custom' => absint( get_post_meta( $image_id, self::GALLERY_META_KEY, true ) ),
            'custom_fields'     => $custom_fields,
            'thumbnail'         => $thumbnail ? $thumbnail[0] : '',
            'thumbnail_url'     => $thumbnail ? $thumbnail[0] : '',
            'image_url'         => $full_image ? $full_image[0] : '',
            'width'             => $width,
            'height'            => $height,
            'dimensions'        => array(
                'width'  => $width,
                'height' => $height,
            ),
            'is_main_custom'    => ! empty( $custom_fields[ self::MAIN_IMAGE_META_KEY ] ),
            'permalink'         => get_permalink( $image_id ),
        );
    }

    private function get_custom_fields( int $post_id ): array {
        $custom_fields = get_post_meta( $post_id );

        foreach ( $custom_fields as $key => $values ) {
            if ( str_starts_with( $key, '_' ) ) {
                unset( $custom_fields[ $key ] );
                continue;
            }

            $value = count( $values ) === 1
                ? maybe_unserialize( $values[0] )
                : array_map( 'maybe_unserialize', $values );

            if ( self::MAIN_IMAGE_META_KEY === $key ) {
                $value = rest_sanitize_boolean( $value );
            } elseif ( in_array( $key, array( self::CATEGORY_META_KEY, self::GALLERY_META_KEY ), true ) ) {
                $value = absint( $value );
            }

            $custom_fields[ $key ] = $value;
        }

        return $custom_fields;
    }
}
