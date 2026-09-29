<?php
if (!defined('ABSPATH')) {
    exit;
}

class SMS_Auth {
    public function __construct() {
        add_action('init', array($this, 'check_admin_bar_visibility'));
        add_action('admin_init', array($this, 'restrict_wp_admin_access'));
        add_filter('login_redirect', array($this, 'custom_login_redirect'), 10, 3);
        add_filter('authenticate', array($this, 'check_user_membership_on_login'), 30, 3);
    }

    public static function is_admin_user($user = null) {
        if (!$user) {
            $user = wp_get_current_user();
        }
        if (!$user || !$user->exists()) {
            return false;
        }
        if (in_array('administrator', (array)$user->roles) || in_array('sms_administrator', (array)$user->roles)) {
            return true;
        }
        return false;
    }

    public static function is_user_expired($user_id) {
        $validity = get_user_meta($user_id, 'sms_membership_validity', true);
        if (empty($validity)) {
            // Default 1 year from registration date
            $user = get_userdata($user_id);
            if ($user && !empty($user->user_registered)) {
                $reg_time = strtotime($user->user_registered);
                $validity = date('Y-m-d', strtotime('+1 year', $reg_time));
                update_user_meta($user_id, 'sms_membership_validity', $validity);
            }
        }

        if (!empty($validity)) {
            $today = date('Y-m-d');
            if ($today > $validity) {
                update_user_meta($user_id, 'sms_account_status', 'expired');
                return true;
            }
        }

        $status = get_user_meta($user_id, 'sms_account_status', true);
        if ($status === 'expired') {
            return true;
        }

        return false;
    }

    public static function get_user_status($user_id) {
        if (self::is_user_expired($user_id)) {
            return 'expired';
        }
        $status = get_user_meta($user_id, 'sms_account_status', true);
        return $status ? $status : 'active';
    }

    public function check_admin_bar_visibility() {
        if (is_user_logged_in()) {
            if (!self::is_admin_user()) {
                show_admin_bar(false);
            }
        }
    }

    public function restrict_wp_admin_access() {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }

        if (is_user_logged_in() && !self::is_admin_user()) {
            $dashboard_page_id = get_option('sms_dashboard_page_id');
            $redirect_url = $dashboard_page_id ? get_permalink($dashboard_page_id) : home_url('/');
            wp_safe_redirect($redirect_url);
            exit;
        }
    }

    public function custom_login_redirect($redirect_to, $requested_redirect_to, $user) {
        if (is_wp_error($user) || !$user) {
            return $redirect_to;
        }

        if (!self::is_admin_user($user)) {
            $dashboard_page_id = get_option('sms_dashboard_page_id');
            if ($dashboard_page_id) {
                return get_permalink($dashboard_page_id);
            }
        }
        return $redirect_to;
    }

    public function check_user_membership_on_login($user, $username, $password) {
        if (is_wp_error($user) || !$user) {
            return $user;
        }

        // Bypass check for super admin
        if (self::is_admin_user($user)) {
            return $user;
        }

        $user_id = $user->ID;
        $status  = self::get_user_status($user_id);

        if ($status === 'expired') {
            return new WP_Error(
                'sms_account_expired',
                '<strong>عفواً:</strong> لقد انتهت صلاحية اشتراكك الحسابي. يرجى التواصل مع مدير النظام لتجديد العضوية.'
            );
        }

        if ($status === 'inactive') {
            return new WP_Error(
                'sms_account_inactive',
                '<strong>عفواً:</strong> هذا الحساب غير مفعل حالياً. يرجى التواصل مع مدير النظام.'
            );
        }

        return $user;
    }
}
