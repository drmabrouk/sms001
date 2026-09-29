<?php
if (!defined('ABSPATH')) {
    exit;
}

class SMS_Users {
    public function __construct() {
        add_action('delete_user', array($this, 'on_wp_delete_user'));
    }

    public static function get_arab_countries() {
        return array(
            'الإمارات العربية المتحدة',
            'المملكة العربية السعودية',
            'الكويت',
            'قطر',
            'سلطنة عمان',
            'البحرين',
            'مصر',
            'الأردن',
            'لبنان',
            'العراق',
            'المغرب',
            'تونس',
            'الجزائر',
            'ليبيا',
            'السودان',
            'اليمن',
            'فلسطين',
            'سوريا',
            'موريتانيا',
            'الصومال',
            'جيبوتي',
            'جزر القمر'
        );
    }

    public static function get_user_institutions($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'sms_user_institutions';
        $results = $wpdb->get_col($wpdb->prepare("SELECT institution_id FROM $table WHERE user_id = %d", $user_id));
        return array_map('intval', $results);
    }

    public static function update_user_institutions($user_id, $institution_ids) {
        global $wpdb;
        $table = $wpdb->prefix . 'sms_user_institutions';

        $user = get_userdata($user_id);
        if (!$user) return;

        $roles = (array) $user->roles;
        $primary_role = !empty($roles) ? $roles[0] : '';

        // Coordinator and Teacher are strictly allowed ONLY 1 institution
        if (in_array($primary_role, array('sms_coordinator', 'sms_teacher')) && count($institution_ids) > 1) {
            $institution_ids = array(end($institution_ids));
        }

        $wpdb->delete($table, array('user_id' => $user_id), array('%d'));

        foreach ($institution_ids as $inst_id) {
            $inst_id = intval($inst_id);
            if ($inst_id > 0) {
                $wpdb->insert($table, array(
                    'user_id' => $user_id,
                    'institution_id' => $inst_id
                ), array('%d', '%d'));
            }
        }
    }

    public function on_wp_delete_user($user_id) {
        global $wpdb;

        // Clean up institution mapping
        $table_inst = $wpdb->prefix . 'sms_user_institutions';
        $wpdb->delete($table_inst, array('user_id' => $user_id), array('%d'));

        // Disassociate student record if linked
        $table_students = $wpdb->prefix . 'sms_students';
        $wpdb->update($table_students, array('user_id' => 0, 'is_active_account' => 0), array('user_id' => $user_id), array('%d', '%d'), array('%d'));
    }

    public static function delete_sms_user($user_id) {
        if (!SMS_Auth::is_admin_user() && !current_user_can('manage_sms_users')) {
            return false;
        }

        require_once(ABSPATH . 'wp-admin/includes/user.php');
        return wp_delete_user($user_id);
    }
}
