<?php

if (!defined('ABSPATH')) {
    exit;
}

class LAHUTA_API
{
    public static function register_routes(): void
    {
        register_rest_route(
            LAHUTA_API_NAMESPACE,
            '/items',
            [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [self::class, 'get_items'],
                    'permission_callback' => '__return_true',
                ],
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [self::class, 'create_item'],
                    'permission_callback' => [self::class, 'permissions_check'],
                    'args' => [
                        'name' => [
                            'required' => true,
                            'type' => 'string',
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                    ],
                ],
            ]
        );
    }

    public static function get_items(WP_REST_Request $request)
    {
        return new WP_REST_Response([
            'success' => true,
            'data' => [],
        ], 200);
    }

    public static function create_item(WP_REST_Request $request)
    {
        $name = $request->get_param('name');

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Item created',
            'data' => [
                'name' => $name,
            ],
        ], 201);
    }

    public static function permissions_check(
        WP_REST_Request $request
    ) {
        return current_user_can('edit_posts');
    }
}