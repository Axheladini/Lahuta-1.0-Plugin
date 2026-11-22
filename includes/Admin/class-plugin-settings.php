<?php 

namespace LahutaRestAPI\Admin;

defined( 'ABSPATH' ) || exit;

final class LahutaPluginSettings {

    public const SETTINGS_PAGE = 'lahuta-plugin-settings';

    private const OPTION_GROUP = 'lahuta_plugin_settings_group';
    private const OPTION_NAME = 'lahuta_plugin_settings';
    private const SETTINGS_SECTION = 'lahuta_plugin_settings_section';
    private const NONCE_ACTION = 'lahuta_plugin_settings_nonce';
    private const AJAX_ACTION = 'lahuta_save_plugin_settings';
    private const INPUT_TYPES = array(
        'hidden',
        'text',
        'search',
        'tel',
        'url',
        'email',
        'password',
        'date',
        'month',
        'week',
        'time',
        'datetime-local',
        'number',
        'range',
        'color',
        'checkbox',
        'radio',
    );
    private const FIELD_ATTRIBUTES = array(
        'placeholder',
        'min',
        'max',
        'step',
        'minlength',
        'maxlength',
        'pattern',
        'autocomplete',
        'inputmode',
        'size',
        'rows',
        'cols',
        'title',
        'required',
        'readonly',
        'disabled',
    );

    public function register(): void {

        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_' . self::AJAX_ACTION, array( $this, 'ajax_save_settings' ) );
    }

    public function register_settings(): void {

        register_setting(
            self::OPTION_GROUP,
            self::OPTION_NAME,
            array(
                'type'              => 'array',
                'default'           => array(),
                'sanitize_callback' => array( $this, 'sanitize_options' ),
            )
        );

        add_settings_section(
            self::SETTINGS_SECTION,
            '',
            '__return_false',
            self::SETTINGS_PAGE
        );

        foreach ( $this->get_fields() as $field_id => $field ) {
            add_settings_field(
                $field_id,
                $field['label'],
                array( $this, 'render_field' ),
                self::SETTINGS_PAGE,
                self::SETTINGS_SECTION,
                array(
                    'label_for' => $field_id,
                    'field_id'  => $field_id,
                )
            );
        }
    }

    public function enqueue_assets( string $hook ): void {

        if ( 'toplevel_page_lahuta-admin' !== $hook ) {
            return;
        }

        wp_localize_script(
            'lahuta-script',
            'LahutaPluginSettings',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( self::NONCE_ACTION ),
                'action'  => self::AJAX_ACTION,
                'strings' => array(
                    'saving' => __( 'Saving settings...', 'lahuta-plugin' ),
                    'saved'  => __( 'Settings saved', 'lahuta-plugin' ),
                    'error'  => __( 'Could not save settings. Please try again.', 'lahuta-plugin' ),
                ),
            )
        );
    }

    public function render_field( array $args ): void {

        $field_id = $args['field_id'];
        $field    = $this->get_fields()[ $field_id ];
        $options  = get_option( self::OPTION_NAME, array() );
        $value    = is_array( $options ) && array_key_exists( $field_id, $options )
            ? $options[ $field_id ]
            : $field['default'];
        $name     = self::OPTION_NAME . '[' . $field_id . ']';

        if ( 'checkbox' === $field['type'] ) {
            printf(
                '<div class="form-check form-switch lahuta-setting-option"><input class="form-check-input" type="checkbox" id="%1$s" name="%2$s" value="1" %3$s></div>',
                esc_attr( $field_id ),
                esc_attr( $name ),
                checked( $value, true, false )
            );
            return;
        }

        if ( 'radio' === $field['type'] ) {
            foreach ( $field['options'] as $option_value => $option_label ) {
                $option_id = $field_id . '-' . sanitize_html_class( (string) $option_value );

                printf(
                    '<label class="form-check form-check-inline"><input class="form-check-input" type="radio" id="%1$s" name="%2$s" value="%3$s" %4$s> <span class="form-check-label">%5$s</span></label>',
                    esc_attr( $option_id ),
                    esc_attr( $name ),
                    esc_attr( (string) $option_value ),
                    checked( (string) $value, (string) $option_value, false ),
                    esc_html( $option_label )
                );
            }
            return;
        }

        if ( 'select' === $field['type'] ) {
            $multiple = ! empty( $field['multiple'] );
            $selected = $multiple && is_array( $value )
                ? array_map( 'strval', $value )
                : array( (string) $value );
            $select_name = $multiple ? $name . '[]' : $name;

            printf(
                '<select class="regular-text" id="%1$s" name="%2$s"%3$s%4$s>',
                esc_attr( $field_id ),
                esc_attr( $select_name ),
                $multiple ? ' multiple' : '',
                $this->render_field_attributes( $field )
            );

            foreach ( $field['options'] as $option_value => $option_label ) {
                printf(
                    '<option value="%1$s" %2$s>%3$s</option>',
                    esc_attr( (string) $option_value ),
                    selected( in_array( (string) $option_value, $selected, true ), true, false ),
                    esc_html( $option_label )
                );
            }

            echo '</select>';
            return;
        }

        if ( 'textarea' === $field['type'] ) {
            printf(
                '<textarea class="large-text" id="%1$s" name="%2$s"%3$s>%4$s</textarea>',
                esc_attr( $field_id ),
                esc_attr( $name ),
                $this->render_field_attributes( $field ),
                esc_textarea( (string) $value )
            );
            return;
        }

        if ( ! in_array( $field['type'], self::INPUT_TYPES, true ) ) {
            wp_die( esc_html__( 'Unsupported plugin setting field type.', 'lahuta-plugin' ) );
        }

        printf(
            '<input class="regular-text" type="%1$s" id="%2$s" name="%3$s" value="%4$s"%5$s>',
            esc_attr( $field['type'] ),
            esc_attr( $field_id ),
            esc_attr( $name ),
            esc_attr( is_scalar( $value ) ? (string) $value : '' ),
            $this->render_field_attributes( $field )
        );
    }

    public function sanitize_options( $input ): array {

        if ( ! is_array( $input ) ) {
            $input = array();
        }

        $sanitized = array();

        foreach ( $this->get_fields() as $field_id => $field ) {
            $type = $field['type'];

            if ( 'checkbox' === $type ) {
                $sanitized[ $field_id ] = isset( $input[ $field_id ] )
                    && is_scalar( $input[ $field_id ] )
                    && rest_sanitize_boolean( $input[ $field_id ] );
                continue;
            }

            if ( ! array_key_exists( $field_id, $input ) ) {
                $sanitized[ $field_id ] = 'select' === $type && ! empty( $field['multiple'] )
                    ? array()
                    : $field['default'];
                continue;
            }

            $sanitized[ $field_id ] = $this->sanitize_field_value(
                $field,
                $input[ $field_id ]
            );
        }

        return $sanitized;
    }

    private function sanitize_field_value( array $field, $value ) {

        $type    = $field['type'];
        $default = $field['default'];

        if ( 'select' === $type && ! empty( $field['multiple'] ) ) {
            if ( ! is_array( $value ) ) {
                return array();
            }

            $allowed = array();
            foreach ( $value as $selected_value ) {
                if (
                    is_scalar( $selected_value )
                    && array_key_exists( (string) $selected_value, $field['options'] )
                ) {
                    $allowed[] = (string) $selected_value;
                }
            }

            return array_values( array_unique( $allowed ) );
        }

        if ( ! is_scalar( $value ) ) {
            return $default;
        }

        $value = (string) $value;

        if ( 'select' === $type || 'radio' === $type ) {
            return array_key_exists( $value, $field['options'] ) ? $value : $default;
        }

        if ( 'textarea' === $type ) {
            return sanitize_textarea_field( $value );
        }

        if ( 'email' === $type ) {
            $email = sanitize_email( $value );
            return is_email( $email ) ? $email : '';
        }

        if ( 'url' === $type ) {
            return esc_url_raw( $value );
        }

        if ( 'number' === $type || 'range' === $type ) {
            if ( ! is_numeric( $value ) ) {
                return $default;
            }

            $number     = (float) $value;
            $attributes = isset( $field['attributes'] ) && is_array( $field['attributes'] )
                ? $field['attributes']
                : array();

            if (
                ( isset( $attributes['min'] ) && $number < (float) $attributes['min'] )
                || ( isset( $attributes['max'] ) && $number > (float) $attributes['max'] )
            ) {
                return $default;
            }

            return $number;
        }

        if ( 'color' === $type ) {
            return sanitize_hex_color( $value ) ?: $default;
        }

        if ( in_array( $type, array( 'date', 'datetime-local', 'month', 'week', 'time' ), true ) ) {
            return $this->sanitize_temporal_value( $type, $value ) ?: $default;
        }

        return sanitize_text_field( $value );
    }

    private function sanitize_temporal_value( string $type, string $value ): string {

        $formats = array();

        switch ( $type ) {
            case 'date':
                $formats = array( '!Y-m-d' );
                break;
            case 'datetime-local':
                $formats = array( '!Y-m-d\TH:i', '!Y-m-d\TH:i:s', '!Y-m-d\TH:i:s.v' );
                break;
            case 'month':
                $formats = array( '!Y-m' );
                break;
            case 'week':
                $formats = array( '!o-\WW' );
                break;
            case 'time':
                $formats = array( '!H:i', '!H:i:s', '!H:i:s.v' );
                break;
        }

        foreach ( $formats as $format ) {
            $date = \DateTimeImmutable::createFromFormat( $format, $value );

            if ( false !== $date && $date->format( substr( $format, 1 ) ) === $value ) {
                return $value;
            }
        }

        return '';
    }

    private function render_field_attributes( array $field ): string {

        $attributes = isset( $field['attributes'] ) && is_array( $field['attributes'] )
            ? $field['attributes']
            : array();
        $markup = '';

        foreach ( self::FIELD_ATTRIBUTES as $attribute ) {
            if ( ! array_key_exists( $attribute, $attributes ) ) {
                continue;
            }

            $value = $attributes[ $attribute ];

            if ( in_array( $attribute, array( 'required', 'readonly', 'disabled' ), true ) ) {
                if ( $value ) {
                    $markup .= ' ' . esc_attr( $attribute );
                }
                continue;
            }

            if ( is_scalar( $value ) ) {
                $markup .= sprintf(
                    ' %1$s="%2$s"',
                    esc_attr( $attribute ),
                    esc_attr( (string) $value )
                );
            }
        }

        return $markup;
    }

    public function ajax_save_settings(): void {

        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error(
                array( 'message' => __( 'You do not have permission to change plugin settings.', 'lahuta-plugin' ) ),
                403
            );
        }

        if ( ! isset( $_POST[ self::OPTION_NAME ] ) || ! is_array( $_POST[ self::OPTION_NAME ] ) ) {
            wp_send_json_error(
                array( 'message' => __( 'The submitted settings are invalid.', 'lahuta-plugin' ) ),
                400
            );
        }

        update_option(
            self::OPTION_NAME,
            wp_unslash( $_POST[ self::OPTION_NAME ] )
        );

        wp_send_json_success(
            array( 'message' => __( 'Settings saved', 'lahuta-plugin' ) )
        );
    }

    private function get_fields(): array {

        return array(
            'lahuta-gallery-date-created' => array(
                'label'   => __( 'Allow gallery date created', 'lahuta-plugin' ),
                'type'    => 'checkbox',
                'default' => false,
            ),
            'lahuta-image-date-created' => array(
                'label'   => __( 'Allow image date created', 'lahuta-plugin' ),
                'type'    => 'checkbox',
                'default' => false,
            ),
            /* Sample additional fields for future use 
            'lahuta-gallery-description' => array(
                'label'   => __( 'Allow gallery description', 'lahuta-plugin' ),
                'type'    => 'text',
                'default' => '',
                'attributes' => array(
                    'placeholder' => __( 'Enter gallery description', 'lahuta-plugin' ),
                ),
            ), 
            'gallery-title' => array(
                'label'      => __( 'Gallery title', 'lahuta-plugin' ),
                'type'       => 'text',
                'default'    => '',
                'attributes' => array( 'maxlength' => 100 ),
            ),
            'gallery-layout' => array(
                'label'   => __( 'Gallery layout', 'lahuta-plugin' ),
                'type'    => 'select',
                'default' => 'grid',
                'options' => array( 'grid' => 'Grid', 'list' => 'List' ),
            ),*/
        );
    }

}