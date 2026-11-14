<?php

namespace LahutaRestAPI\Admin;

defined( 'ABSPATH' ) || exit;

final class GalleryCategoryAdmin {

    private const TAXONOMY = 'lahuta_gallery_category';
    private const NONCE_ACTION = 'lahuta_gallery_category_nonce';

    public function register(): void {


        add_action(
            'admin_enqueue_scripts',
            array( $this, 'enqueue_assets' )
        );

        add_action(
            'wp_ajax_lahuta_add_gallery_category',
            array( $this, 'ajax_add_category' )
        );

        add_action(
            'wp_ajax_lahuta_get_gallery_categories',
            array( $this, 'ajax_get_categories' )
        );

        add_action(
            'wp_ajax_lahuta_update_gallery_category',
            array( $this, 'ajax_update_category' )
        );

        add_action(
            'wp_ajax_lahuta_delete_gallery_category',
            array( $this, 'ajax_delete_category' )
        );

    }


    public function enqueue_assets( string $hook ): void {

        if ( 'toplevel_page_lahuta-admin' !== $hook ) {
            return;
        }

        wp_localize_script(
            'lahuta-script',
            'LahutaGalleryCategories',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( self::NONCE_ACTION ),
                'strings' => array(
                    'update'         => __( 'Update', 'lahuta-rest-api' ),
                    'delete'         => __( 'Delete', 'lahuta-rest-api' ),
                    'previous'       => __( 'Previous', 'lahuta-rest-api' ),
                    'next'           => __( 'Next', 'lahuta-rest-api' ),
                    'page'           => __( 'Page', 'lahuta-rest-api' ),
                    'of'             => __( 'of', 'lahuta-rest-api' ),
                    'loading'        => __( 'Loading categories...', 'lahuta-rest-api' ),
                    'parent'         => __( 'Parent:', 'lahuta-rest-api' ),
                    'noParent'       => __( 'None', 'lahuta-rest-api' ),
                    'noCategories'   => __( 'No gallery categories yet.', 'lahuta-rest-api' ),
                    'noMatchingCategories' => __( 'No categories match your search.', 'lahuta-rest-api' ),
                    'deleteConfirm'  => __( 'Delete "%s"? This will remove the category from assigned galleries.', 'lahuta-rest-api' ),
                    'notFound'       => __( 'Could not find that category. Refresh the category list and try again.', 'lahuta-rest-api' ),
                    'requestFailed'  => __( 'The category request failed. Please try again.', 'lahuta-rest-api' ),
                ),
            )
        );
    }


    public function ajax_add_category(): void {

        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        $taxonomy = get_taxonomy( self::TAXONOMY );

        if (
            ! $taxonomy ||
            ! current_user_can( $taxonomy->cap->manage_terms )
        ) {
            wp_send_json_error(
                array( 'message' => __( 'Permission denied.', 'lahuta-rest-api' ) ),
                403
            );
        }

        $name = isset( $_POST['name'] )
            ? sanitize_text_field( wp_unslash( $_POST['name'] ) )
            : '';

        $description = isset( $_POST['description'] )
            ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) )
            : '';

        $parent = isset( $_POST['parent'] )
            ? absint( $_POST['parent'] )
            : 0;

        if ( '' === $name ) {
            wp_send_json_error(
                array( 'message' => __( 'Enter a category name.', 'lahuta-rest-api' ) ),
                400
            );
        }

        if ( $parent && ! term_exists( $parent, self::TAXONOMY ) ) {
            wp_send_json_error(
                array( 'message' => __( 'Invalid parent category.', 'lahuta-rest-api' ) ),
                400
            );
        }

        $result = wp_insert_term(
            $name,
            self::TAXONOMY,
            array(
                'description' => $description,
                'parent'      => $parent,
            )
        );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error(
                array( 'message' => $result->get_error_message() ),
                400
            );
        }

        wp_send_json_success(
            array(
                'message'  => __( 'Category added successfully.', 'lahuta-rest-api' ),
                'term_id'  => (int) $result['term_id'],
            )
        );
    }

    public function ajax_update_category(): void {

        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        $taxonomy = get_taxonomy( self::TAXONOMY );

        if (
            ! $taxonomy ||
            ! current_user_can( $taxonomy->cap->manage_terms )
        ) {
            wp_send_json_error(
                array( 'message' => __( 'Permission denied.', 'lahuta-rest-api' ) ),
                403
            );
        }

        $term_id = isset( $_POST['term_id'] )
            ? absint( $_POST['term_id'] )
            : 0;
        $name = isset( $_POST['name'] )
            ? sanitize_text_field( wp_unslash( $_POST['name'] ) )
            : '';
        $description = isset( $_POST['description'] )
            ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) )
            : '';
        $parent = isset( $_POST['parent'] )
            ? absint( $_POST['parent'] )
            : 0;

        $term = get_term( $term_id, self::TAXONOMY );

        if ( ! $term_id || ! $term || is_wp_error( $term ) ) {
            wp_send_json_error(
                array( 'message' => __( 'Category not found.', 'lahuta-rest-api' ) ),
                404
            );
        }

        if ( '' === $name ) {
            wp_send_json_error(
                array( 'message' => __( 'Enter a category name.', 'lahuta-rest-api' ) ),
                400
            );
        }

        if ( $term_id === $parent ) {
            wp_send_json_error(
                array( 'message' => __( 'A category cannot be its own parent.', 'lahuta-rest-api' ) ),
                400
            );
        }

        if ( $parent && ! term_exists( $parent, self::TAXONOMY ) ) {
            wp_send_json_error(
                array( 'message' => __( 'Invalid parent category.', 'lahuta-rest-api' ) ),
                400
            );
        }

        if (
            $parent &&
            in_array( $term_id, get_ancestors( $parent, self::TAXONOMY, 'taxonomy' ), true )
        ) {
            wp_send_json_error(
                array( 'message' => __( 'A category cannot be moved beneath one of its own children.', 'lahuta-rest-api' ) ),
                400
            );
        }

        $result = wp_update_term(
            $term_id,
            self::TAXONOMY,
            array(
                'name'        => $name,
                'description' => $description,
                'parent'      => $parent,
            )
        );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error(
                array( 'message' => $result->get_error_message() ),
                400
            );
        }

        wp_send_json_success(
            array( 'message' => __( 'Category updated successfully.', 'lahuta-rest-api' ) )
        );
    }

    public function ajax_delete_category(): void {

        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        $taxonomy = get_taxonomy( self::TAXONOMY );

        if (
            ! $taxonomy ||
            ! current_user_can( $taxonomy->cap->delete_terms )
        ) {
            wp_send_json_error(
                array( 'message' => __( 'Permission denied.', 'lahuta-rest-api' ) ),
                403
            );
        }

        $term_id = isset( $_POST['term_id'] )
            ? absint( $_POST['term_id'] )
            : 0;
        $term = get_term( $term_id, self::TAXONOMY );

        if ( ! $term_id || ! $term || is_wp_error( $term ) ) {
            wp_send_json_error(
                array( 'message' => __( 'Category not found.', 'lahuta-rest-api' ) ),
                404
            );
        }

        $result = wp_delete_term( $term_id, self::TAXONOMY );

        if ( is_wp_error( $result ) || false === $result ) {
            wp_send_json_error(
                array(
                    'message' => is_wp_error( $result )
                        ? $result->get_error_message()
                        : __( 'Could not delete category.', 'lahuta-rest-api' ),
                ),
                400
            );
        }

        wp_send_json_success(
            array( 'message' => __( 'Category deleted successfully.', 'lahuta-rest-api' ) )
        );
    }

    public static function get_categories_data(): array|\WP_Error {
        $terms = get_terms(
            array(
                'taxonomy'   => self::TAXONOMY,
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            )
        );

        if ( is_wp_error( $terms ) ) {
            return $terms;
        }

        $categories = array();

        foreach ( $terms as $term ) {
            $parent = $term->parent
                ? get_term( $term->parent, self::TAXONOMY )
                : null;

            $categories[] = array(
                'id'          => $term->term_id,
                'name'        => $term->name,
                'description' => $term->description,
                'parent'      => $term->parent,
                'parent_name' => ( $parent && ! is_wp_error( $parent ) )
                    ? $parent->name
                    : '',
            );
        }

        return $categories;
    }

    public function ajax_get_categories(): void {

        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        $taxonomy = get_taxonomy( self::TAXONOMY );

        if (
            ! $taxonomy ||
            ! current_user_can( $taxonomy->cap->manage_terms )
        ) {
            wp_send_json_error(
                array( 'message' => __( 'Permission denied.', 'lahuta-rest-api' ) ),
                403
            );
        }

        $categories = self::get_categories_data();

        if ( is_wp_error( $categories ) ) {
            wp_send_json_error(
                array( 'message' => $categories->get_error_message() ),
                500
            );
        }

        wp_send_json_success( $categories );
    }
}