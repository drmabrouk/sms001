<?php
if (!defined('ABSPATH')) {
    exit;
}

class SMS_Notifications {
    public static function create_notification($user_id, $type, $title, $message = '', $link = '', $priority = 'normal') {
        global $wpdb;
        $table = $wpdb->prefix . 'sms_notifications';

        return $wpdb->insert($table, array(
            'user_id'    => intval($user_id),
            'type'       => sanitize_text_field($type),
            'title'      => sanitize_text_field($title),
            'message'    => sanitize_textarea_field($message),
            'link'       => esc_url_raw($link),
            'priority'   => sanitize_text_field($priority),
            'is_read'    => 0,
            'created_at' => current_time('mysql')
        ));
    }

    public static function notify_roles($roles, $type, $title, $message = '', $link = '', $priority = 'normal', $institution_id = 0) {
        $roles = (array) $roles;
        $users = get_users(array('role__in' => $roles));

        foreach ($users as $user) {
            if ($institution_id > 0) {
                $user_insts = SMS_Users::get_user_institutions($user->ID);
                if (!in_array($institution_id, $user_insts)) {
                    continue;
                }
            }
            self::create_notification($user->ID, $type, $title, $message, $link, $priority);
        }
    }

    public static function get_user_notifications($user_id, $limit = 20) {
        global $wpdb;
        $table = $wpdb->prefix . 'sms_notifications';
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC LIMIT %d", $user_id, $limit), ARRAY_A);
    }

    public static function get_unread_count($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'sms_notifications';
        return intval($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE user_id = %d AND is_read = 0", $user_id)));
    }

    public static function mark_as_read($notification_id, $user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'sms_notifications';
        return $wpdb->update($table, array('is_read' => 1), array('id' => intval($notification_id), 'user_id' => intval($user_id)));
    }

    public static function mark_all_as_read($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'sms_notifications';
        return $wpdb->update($table, array('is_read' => 1), array('user_id' => intval($user_id), 'is_read' => 0));
    }

    public static function purge_expired_notifications() {
        global $wpdb;
        $table = $wpdb->prefix . 'sms_notifications';
        $seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days', current_time('timestamp')));
        return $wpdb->query($wpdb->prepare("DELETE FROM $table WHERE created_at < %s", $seven_days_ago));
    }
}
