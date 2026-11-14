<?php

namespace LahutaRestAPI;

defined( 'ABSPATH' ) || exit;

final class Activator {

    public static function activate(): void {
        Plugin::get_instance()->register_post_type();

        flush_rewrite_rules();
    }
}