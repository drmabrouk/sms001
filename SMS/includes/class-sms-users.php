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

    public static function get_scoped_users($viewer_id, $args = array()) {
        $viewer = get_userdata($viewer_id);
        if (!$viewer) return array();

        $roles = (array) $viewer->roles;
        $primary_role = !empty($roles) ? $roles[0] : '';
        $is_admin = SMS_Auth::is_admin_user($viewer);

        // Always exclude System Administrators from list views & searches
        $args['role__not_in'] = array('administrator', 'sms_administrator');

        if ($is_admin || in_array('sms_general_manager', $roles)) {
            return get_users($args);
        }

        $assigned_insts = self::get_user_institutions($viewer_id);
        if (empty($assigned_insts)) {
            return array();
        }

        if ($primary_role === 'sms_coordinator') {
            $args['role'] = 'sms_teacher';
        }

        $all_candidates = get_users($args);
        $filtered = array();

        foreach ($all_candidates as $cand) {
            $cand_insts = self::get_user_institutions($cand->ID);
            if (array_intersect($assigned_insts, $cand_insts)) {
                $filtered[] = $cand;
            }
        }

        return $filtered;
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
