<?php

namespace LahutaRestAPI\Admin;

defined( 'ABSPATH' ) || exit;

final class AddNewGallery {

    private const POST_TYPE = 'lahuta_gallery';
    private const IMAGE_POST_TYPE = 'lahuta_gallery_image';
    private const TAXONOMY = 'lahuta_gallery_category';
    private const NONCE_ACTION = 'lahuta_gallery_nonce';
    private const GALLERY_META_KEY = 'gallery_id_custom';
    private const MAIN_IMAGE_META_KEY = 'is_main_custom';
    private const GALLERY_CREATED_DATE_META_KEY = 'gallery_created_date';
    private const IMAGE_CREATED_DATE_META_KEY = 'image_created_date';

    private const TITLE_MIN = 5;
    private const TITLE_MAX = 200;
    private const EXCERPT_MAX = 500;
    private const IMAGE_DESCRIPTION_MAX = 2000;
    private const MAX_IMAGES = 50;

    private const ALLOWED_MIMES = array(
        'jpg|jpeg|jpe' => 'image/jpeg',
        'png'          => 'image/png',
        'gif'          => 'image/gif',
        'webp'         => 'image/webp',
    );

    /** Posts and attachments created by the current request, removed again if it fails. */
    private array $created = array(
        'posts'       => array(),
        'attachments' => array(),
    );

    public function register(): void {

        add_action(
            'admin_enqueue_scripts',
            array( $this, 'enqueue_assets' )
        );

        add_action(
            'wp_ajax_lahuta_add_gallery',
            array( $this, 'ajax_save_gallery' )
        );

        add_action(
            'wp_ajax_lahuta_get_galleries',
            array( $this, 'ajax_get_galleries' )
        );

        add_action(
            'wp_ajax_lahuta_delete_gallery',
            array( $this, 'ajax_delete_gallery' )
        );
    }

    public function enqueue_assets( string $hook ): void {

        if ( 'toplevel_page_lahuta-admin' !== $hook ) {
            return;
        }

        // Loads TinyMCE so the per-image description editors can be created dynamically.
        wp_enqueue_editor();

        wp_localize_script(
            'lahuta-script',
            'LahutaGallery',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( self::NONCE_ACTION ),
                'settings' => array(
                    'galleryDateCreatedEnabled' => $this->is_setting_enabled( 'lahuta-gallery-date-created' ),
                    'imageDateCreatedEnabled'   => $this->is_setting_enabled( 'lahuta-image-date-created' ),
                ),
                'limits'  => array(
                    'titleMin'    => self::TITLE_MIN,
                    'titleMax'    => self::TITLE_MAX,
                    'excerptMax'  => self::EXCERPT_MAX,
                    'descMax'     => self::IMAGE_DESCRIPTION_MAX,
                    'maxImages'   => self::MAX_IMAGES,
                ),
                'strings' => array(
                    'add'              => __( 'Add Gallery', 'lahuta-rest-api' ),
                    'update'           => __( 'Update Gallery', 'lahuta-rest-api' ),
                    'saving'           => __( 'Saving gallery...', 'lahuta-rest-api' ),
                    'edit'             => __( 'Edit', 'lahuta-rest-api' ),
                    'delete'           => __( 'Delete', 'lahuta-rest-api' ),
                    'remove'           => __( 'Remove', 'lahuta-rest-api' ),
                    'imageTitle'       => __( 'Image title', 'lahuta-rest-api' ),
                    'imageDescription' => __( 'Image description', 'lahuta-rest-api' ),
                    'dateDescription'  => __( 'Image or event date', 'lahuta-rest-api' ),
                    'mainImage'        => __( 'Main image', 'lahuta-rest-api' ),
                    'mainImageRequired'=> __( 'Select exactly one image as the main image before saving.', 'lahuta-rest-api' ),
                    'replaceThumb'     => __( 'Replace image (optional)', 'lahuta-rest-api' ),
                    'titleError'       => __( 'Image title is required and must be between 5 and 200 characters.', 'lahuta-rest-api' ),
                    'descError'        => __( 'Image description is required and must not exceed 2000 characters.', 'lahuta-rest-api' ),
                    'dateError'        => __( 'Image date is required.', 'lahuta-rest-api' ),
                    'invalidFile'      => __( 'Only jpg, jpeg, png, gif and webp images are allowed.', 'lahuta-rest-api' ),
                    'tooManyImages'    => __( 'You can add up to 50 images per gallery.', 'lahuta-rest-api' ),
                    'fixImageErrors'   => __( 'Please fix the errors in the highlighted images.', 'lahuta-rest-api' ),
                    'noGalleries'      => __( 'No galleries found.', 'lahuta-rest-api' ),
                    'noMatching'       => __( 'No galleries match your search.', 'lahuta-rest-api' ),
                    'loading'          => __( 'Loading galleries...', 'lahuta-rest-api' ),
                    'uncategorized'    => __( 'None', 'lahuta-rest-api' ),
                    'page'             => __( 'Page', 'lahuta-rest-api' ),
                    'of'               => __( 'of', 'lahuta-rest-api' ),
                    'previous'         => __( 'Previous', 'lahuta-rest-api' ),
                    'next'             => __( 'Next', 'lahuta-rest-api' ),
                    'deleteConfirm'    => __( 'Delete the gallery "%s" and all of its images? This cannot be undone.', 'lahuta-rest-api' ),
                    'requestFailed'    => __( 'The request failed. Please try again.', 'lahuta-rest-api' ),
                ),
            )
        );
    }

    /**
     * Creates or updates a gallery together with its images.
     */
    public function ajax_save_gallery(): void {


        check_ajax_referer( self::NONCE_ACTION, 'nonce' );
        


        $gallery_id = isset( $_POST['gallery_id'] ) ? absint( $_POST['gallery_id'] ) : 0;

        $this->authorize_save( $gallery_id );

        $data = $this->validate_request( $gallery_id );

        $post_data = array(
            'post_type'    => self::POST_TYPE,
            'post_title'   => $data['title'],
            'post_excerpt' => $data['excerpt'],
            'post_status'  => 'publish',
        );

        if ( $gallery_id ) {
            $post_data['ID'] = $gallery_id;
            $result          = wp_update_post( $post_data, true );
        } else {
            $result = wp_insert_post( $post_data, true );
        }

        if ( is_wp_error( $result ) ) {
            $this->fail( $result->get_error_message(), 500 );
        }

        $gallery_id = (int) $result;

        if ( ! $data['is_edit'] ) {
            $this->created['posts'][] = $gallery_id;
        }

        $terms = wp_set_object_terms( $gallery_id, array( $data['category_id'] ), self::TAXONOMY );

        if ( is_wp_error( $terms ) ) {
            $this->fail( $terms->get_error_message(), 500 );
        }
   
        update_post_meta( $gallery_id, 'category_id_custom', $data['category_id'] );
        
        update_post_meta( $gallery_id, self::GALLERY_CREATED_DATE_META_KEY, $data['created_date'] );

        foreach ( $data['removed_images'] as $image_id ) {
            $this->delete_image( $image_id );
        }

        foreach ( $data['images'] as $position => $image ) {
            $saved = $this->save_image( $gallery_id, $image, $position );

            if ( is_wp_error( $saved ) ) {
                $this->fail( $saved->get_error_message(), 500 );
            }
        }

        wp_send_json_success(
            array(
                'message' => $data['is_edit']
                    ? __( 'Gallery updated successfully.', 'lahuta-rest-api' )
                    : __( 'Gallery created successfully.', 'lahuta-rest-api' ),
                'gallery' => $this->format_gallery( get_post( $gallery_id ) ),
            )
        );
    }

    public function ajax_get_galleries(): void {

        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error(
                array( 'message' => __( 'You do not have permission to view galleries.', 'lahuta-rest-api' ) ),
                403
            );
        }

        $galleries = get_posts(
            array(
                'post_type'      => self::POST_TYPE,
                'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
            )
        );

        wp_send_json_success(
            array_map( array( $this, 'format_gallery' ), $galleries )
        );
    }

    public function ajax_delete_gallery(): void {

        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        $gallery_id = isset( $_POST['gallery_id'] ) ? absint( $_POST['gallery_id'] ) : 0;

        if ( ! $gallery_id || self::POST_TYPE !== get_post_type( $gallery_id ) ) {
            wp_send_json_error(
                array( 'message' => __( 'The gallery could not be found.', 'lahuta-rest-api' ) ),
                404
            );
        }

        if ( ! current_user_can( 'delete_post', $gallery_id ) ) {
            wp_send_json_error(
                array( 'message' => __( 'You do not have permission to delete this gallery.', 'lahuta-rest-api' ) ),
                403
            );
        }

        foreach ( $this->get_image_posts( $gallery_id ) as $image ) {
            $this->delete_image( $image->ID );
        }

        if ( ! wp_delete_post( $gallery_id, true ) ) {
            wp_send_json_error(
                array( 'message' => __( 'The gallery could not be deleted.', 'lahuta-rest-api' ) ),
                500
            );
        }

        wp_send_json_success(
            array( 'message' => __( 'Gallery deleted successfully.', 'lahuta-rest-api' ) )
        );
    }

    private function authorize_save( int $gallery_id ): void {

        if ( $gallery_id ) {
            if ( self::POST_TYPE !== get_post_type( $gallery_id ) ) {
                $this->fail( __( 'The gallery could not be found.', 'lahuta-rest-api' ), 404 );
            }

            if ( ! current_user_can( 'edit_post', $gallery_id ) ) {
                $this->fail( __( 'You do not have permission to edit this gallery.', 'lahuta-rest-api' ), 403 );
            }
        } else {
            $post_type_object = get_post_type_object( self::POST_TYPE );

            if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->create_posts ) ) {
                $this->fail( __( 'You do not have permission to create galleries.', 'lahuta-rest-api' ), 403 );
            }
        }

        if ( ! current_user_can( 'upload_files' ) ) {
            $this->fail( __( 'You do not have permission to upload files.', 'lahuta-rest-api' ), 403 );
        }
    }

    /**
     * Validates and sanitizes every submitted field before anything is written.
     * Sends a JSON error containing per-field messages when validation fails.
     */
    private function validate_request( int $gallery_id ): array {

        $errors  = array();
        $is_edit = $gallery_id > 0;

        $title = isset( $_POST['title'] )
            ? sanitize_text_field( wp_unslash( $_POST['title'] ) )
            : '';

        $excerpt = isset( $_POST['excerpt'] )
            ? sanitize_textarea_field( wp_unslash( $_POST['excerpt'] ) )
            : '';

        $category_id = isset( $_POST['lahuta_gallery_category'] )
            ? (int) $_POST['lahuta_gallery_category']
            : 0;

            
        if ( $this->is_setting_enabled( 'lahuta-gallery-date-created' ) ) {
            $created_date = isset( $_POST['created_date'] ) && is_string( $_POST['created_date'] )
                ? sanitize_text_field( wp_unslash( $_POST['created_date'] ) )
                : '';

            if ( ! $this->is_valid_date( $created_date ) ) {
                $errors['gallery_created_date'] = __( 'Please enter a valid gallery date.', 'lahuta-rest-api' );
            }
        } else {
            $created_date = current_time( 'Y-m-d' );
        }


        $title_length = mb_strlen( $title );

        if ( $title_length < self::TITLE_MIN || $title_length > self::TITLE_MAX ) {
            $errors['title'] = __( 'Gallery title is required and must be between 5 and 200 characters.', 'lahuta-rest-api' );
        }

        if ( '' === $excerpt || mb_strlen( $excerpt ) > self::EXCERPT_MAX ) {
            $errors['excerpt'] = __( 'Gallery description is required and must be up to 500 characters.', 'lahuta-rest-api' );
        }

        if ( $category_id < 1 || ! term_exists( $category_id, self::TAXONOMY ) ) {
            $errors['category'] = __( 'Please select a valid gallery category.', 'lahuta-rest-api' );
        }

        $removed_images = array();

        if ( $is_edit && ! empty( $_POST['removed_images'] ) && is_array( $_POST['removed_images'] ) ) {
            foreach ( array_map( 'absint', wp_unslash( $_POST['removed_images'] ) ) as $image_id ) {
                if ( $image_id && $this->image_belongs_to_gallery( $image_id, $gallery_id ) ) {
                    $removed_images[] = $image_id;
                }
            }
        }

        $images          = array();
        $image_date_enabled = $this->is_setting_enabled( 'lahuta-image-date-created' );
        $raw_images      = isset( $_POST['images'] ) && is_array( $_POST['images'] )
            ? wp_unslash( $_POST['images'] )
            : array();
        $main_image_count = 0;

        if ( count( $raw_images ) > self::MAX_IMAGES ) {
            $this->fail( __( 'You can add up to 50 images per gallery.', 'lahuta-rest-api' ) );
        }

        foreach ( $raw_images as $key => $raw ) {
            if ( ! is_array( $raw ) ) {
                continue;
            }

            $index       = absint( $key );
            $image_id    = isset( $raw['id'] ) ? absint( $raw['id'] ) : 0;
            $image_title = isset( $raw['title'] ) ? sanitize_text_field( $raw['title'] ) : '';
            $description = isset( $raw['description'] ) ? wp_kses_post( $raw['description'] ) : '';
            $plain_desc  = trim( wp_strip_all_tags( $description ) );
            $prefix      = 'images.' . $index . '.';

            if ( $image_id && ( ! $is_edit || ! $this->image_belongs_to_gallery( $image_id, $gallery_id ) ) ) {
                $errors[ $prefix . 'title' ] = __( 'This image does not belong to the gallery.', 'lahuta-rest-api' );
                continue;
            }

            $image_title_length = mb_strlen( $image_title );

            if ( $image_title_length < self::TITLE_MIN || $image_title_length > self::TITLE_MAX ) {
                $errors[ $prefix . 'title' ] = __( 'Image title is required and must be between 5 and 200 characters.', 'lahuta-rest-api' );
            }

            if ( '' === $plain_desc || mb_strlen( $plain_desc ) > self::IMAGE_DESCRIPTION_MAX ) {
                $errors[ $prefix . 'description' ] = __( 'Image description is required and must not exceed 2000 characters.', 'lahuta-rest-api' );
            }

            if ( $image_date_enabled ) {
                $has_image_created_date = array_key_exists( 'created_date', $raw );
                $image_created_date = $has_image_created_date && is_string( $raw['created_date'] )
                    ? sanitize_text_field( $raw['created_date'] )
                    : '';

                if ( $has_image_created_date && ! is_string( $raw['created_date'] ) ) {
                    $errors[ $prefix . 'created_date' ] = __( 'Please enter a valid image or event date.', 'lahuta-rest-api' );
                }

                if ( '' === $image_created_date ) {
                    $stored_date = $image_id
                        ? (string) get_post_meta( $image_id, self::IMAGE_CREATED_DATE_META_KEY, true )
                        : '';
                    $image_created_date = $this->is_valid_date( $stored_date )
                        ? $stored_date
                        : current_time( 'Y-m-d' );
                }

                if ( ! $this->is_valid_date( $image_created_date ) ) {
                    $errors[ $prefix . 'created_date' ] = __( 'Please enter a valid image or event date.', 'lahuta-rest-api' );
                }
            } else {
                $image_created_date = current_time( 'Y-m-d' );
            }

            $thumb_error = $this->check_file(
                'image_thumbnail_' . $index,
                ! $image_id,
                __( 'Image thumbnail', 'lahuta-rest-api' )
            );

            if ( $thumb_error ) {
                $errors[ $prefix . 'thumbnail' ] = $thumb_error;
            }

            $is_main = isset( $raw['is_main'] ) && rest_sanitize_boolean( $raw['is_main'] );
            $main_image_count += $is_main ? 1 : 0;

            $images[] = array(
                'index'        => $index,
                'id'           => $image_id,
                'title'        => $image_title,
                'description'  => $description,
                'created_date' => $image_created_date,
                'is_main'      => $is_main,
            );
        }

        if ( 1 !== $main_image_count ) {
            $errors['main_image'] = __( 'Select exactly one image as the main image before saving.', 'lahuta-rest-api' );
        }

        if ( $errors ) {
            $this->fail( reset( $errors ), 400, $errors );
        }

        return array(
            'is_edit'        => $is_edit,
            'title'          => $title,
            'excerpt'        => $excerpt,
            'category_id'    => $category_id,
            'created_date'   => $created_date,
            'images'         => $images,
            'removed_images' => $removed_images,
        );
    }

    private function is_setting_enabled( string $setting_key ): bool {

        $settings = get_option( 'lahuta_plugin_settings', array() );

        return is_array( $settings ) && ! empty( $settings[ $setting_key ] );
    }

    private function is_valid_date( string $date ): bool {

        $parsed_date = \DateTimeImmutable::createFromFormat( '!Y-m-d', $date );

        return false !== $parsed_date && $parsed_date->format( 'Y-m-d' ) === $date;
    }

    /**
     * Returns an error message, or null when the file is acceptable (or optional and absent).
     */
    private function check_file( string $key, bool $required, string $label ): ?string {

        if ( empty( $_FILES[ $key ] ) || ! is_array( $_FILES[ $key ] ) || UPLOAD_ERR_NO_FILE === $_FILES[ $key ]['error'] ) {
            /* translators: %s: field label */
            return $required ? sprintf( __( '%s is required.', 'lahuta-rest-api' ), $label ) : null;
        }

        $file = $_FILES[ $key ];

        if ( UPLOAD_ERR_OK !== $file['error'] ) {
            /* translators: %s: field label */
            return sprintf( __( '%s could not be uploaded. Please try again.', 'lahuta-rest-api' ), $label );
        }

        $check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], self::ALLOWED_MIMES );

        if ( empty( $check['type'] ) ) {
            /* translators: %s: field label */
            return sprintf( __( '%s is not a valid image. Allowed types: jpg, jpeg, png, gif, webp.', 'lahuta-rest-api' ), $label );
        }

        return null;
    }

    private function has_file( string $key ): bool {
        return ! empty( $_FILES[ $key ] ) && UPLOAD_ERR_OK === $_FILES[ $key ]['error'];
    }

    private function upload_file( string $key, int $parent_id ): int|\WP_Error {

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment_id = media_handle_upload(
            $key,
            $parent_id,
            array(),
            array(
                'test_form' => false,
                'mimes'     => self::ALLOWED_MIMES,
            )
        );

        if ( ! is_wp_error( $attachment_id ) ) {
            $this->created['attachments'][] = $attachment_id;
        }

        return $attachment_id;
    }

    private function save_image( int $gallery_id, array $image, int $position ): true|\WP_Error {

        $post_data = array(
            'post_type'    => self::IMAGE_POST_TYPE,
            'post_title'   => $image['title'],
            'post_content' => $image['description'],
            'post_status'  => 'publish',
            'menu_order'   => $position,
        );

        if ( $image['id'] ) {
            $post_data['ID'] = $image['id'];
            $image_id        = wp_update_post( $post_data, true );
        } else {
            $image_id = wp_insert_post( $post_data, true );
        }

        if ( is_wp_error( $image_id ) ) {
            return $image_id;
        }

        if ( ! $image['id'] ) {
            $this->created['posts'][] = $image_id;
        }

        update_post_meta( $image_id, self::GALLERY_META_KEY, $gallery_id );

        $thumb_key = 'image_thumbnail_' . $image['index'];

        if ( $this->has_file( $thumb_key ) ) {
            $old_thumb_id = (int) get_post_thumbnail_id( $image_id );
            $attachment   = $this->upload_file( $thumb_key, $image_id );

            if ( is_wp_error( $attachment ) ) {
                return $attachment;
            }

            set_post_thumbnail( $image_id, $attachment );

            if ( $old_thumb_id ) {
                wp_delete_attachment( $old_thumb_id, true );
            }
        }

        update_post_meta( $image_id, self::MAIN_IMAGE_META_KEY, (bool) $image['is_main'] );
        update_post_meta( $image_id, self::IMAGE_CREATED_DATE_META_KEY, $image['created_date'] );

        return true;
    }

    private function delete_image( int $image_id ): void {

        $thumb_id = (int) get_post_thumbnail_id( $image_id );

        if ( $thumb_id ) {
            wp_delete_attachment( $thumb_id, true );
        }

        wp_delete_post( $image_id, true );
    }

    private function image_belongs_to_gallery( int $image_id, int $gallery_id ): bool {
        return self::IMAGE_POST_TYPE === get_post_type( $image_id )
            && (int) get_post_meta( $image_id, self::GALLERY_META_KEY, true ) === $gallery_id
            && current_user_can( 'edit_post', $image_id );
    }

    private function get_image_posts( int $gallery_id ): array {

        return get_posts(
            array(
                'post_type'      => self::IMAGE_POST_TYPE,
                'post_status'    => 'any',
                'posts_per_page' => -1,
                'meta_key'       => self::GALLERY_META_KEY,
                'meta_value'     => $gallery_id,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
            )
        );
    }

    private function format_gallery( \WP_Post $gallery ): array {

        $terms          = wp_get_object_terms( $gallery->ID, self::TAXONOMY );
        $term           = ( ! is_wp_error( $terms ) && $terms ) ? $terms[0] : null;
        $images         = array();
        $main_image_url = '';
        $has_main_image = false;

        $date_created   = get_post_meta( $gallery->ID, self::GALLERY_CREATED_DATE_META_KEY, true );

        foreach ( $this->get_image_posts( $gallery->ID ) as $image ) {
            $is_main   = (bool) get_post_meta( $image->ID, self::MAIN_IMAGE_META_KEY, true );
            $image_url = (string) get_the_post_thumbnail_url( $image->ID, 'thumbnail' );

            if ( $is_main && ! $has_main_image ) {
                $main_image_url = $image_url;
                $has_main_image = true;
            } elseif ( $is_main ) {
                $is_main = false;
            }

            $images[] = array(
                'id'            => $image->ID,
                'title'         => $image->post_title,
                'description'   => $image->post_content,
                'created_date'  => get_post_meta( $image->ID, self::IMAGE_CREATED_DATE_META_KEY, true ),
                'thumbnail_url' => $image_url,
                'is_main'       => $is_main,
            );
        }

        return array(
            'id'            => $gallery->ID,
            'title'         => $gallery->post_title,
            'excerpt'       => $gallery->post_excerpt,
            'category_id'   => $term ? $term->term_id : 0,
            'category_name' => $term ? $term->name : '',
            'created_date'  => $date_created,
            'thumbnail_url' => $main_image_url,
            'images'        => $images,
        );
    }

    /**
     * Removes everything created by this request, then sends the JSON error.
     */
    private function fail( string $message, int $status = 400, array $errors = array() ): never {

        foreach ( $this->created['attachments'] as $attachment_id ) {
            wp_delete_attachment( $attachment_id, true );
        }

        foreach ( $this->created['posts'] as $post_id ) {
            wp_delete_post( $post_id, true );
        }

        $this->created = array(
            'posts'       => array(),
            'attachments' => array(),
        );

        wp_send_json_error(
            array(
                'message' => $message,
                'errors'  => $errors,
            ),
            $status
        );
    }
}
