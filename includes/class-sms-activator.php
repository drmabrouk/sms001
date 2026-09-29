<?php
if (!defined('ABSPATH')) {
    exit;
}

class SMS_Activator {
    public static function activate() {
        SMS_Roles::register_roles();
        SMS_DB::create_tables();
        self::create_dashboard_page();
        flush_rewrite_rules();
    }

    private static function create_dashboard_page() {
        $page_title = SMS_PAGE_TITLE; // "لوحة التحكم"
        $page_slug  = SMS_PAGE_SLUG;  // "sms-dashboard"

        $existing_page = get_page_by_path($page_slug);
        if (!$existing_page) {
            $page_id = wp_insert_post(array(
                'post_title'     => $page_title,
                'post_name'      => $page_slug,
                'post_content'   => '[sms_dashboard_app]',
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed'
            ));
            if ($page_id && !is_wp_error($page_id)) {
                update_option('sms_dashboard_page_id', $page_id);
            }
        } else {
            update_option('sms_dashboard_page_id', $existing_page->ID);
        }
    }
}
