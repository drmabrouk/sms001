<?php
if (!defined('ABSPATH')) {
    exit;
}

class SMS_Roles {
    public static function get_roles_config() {
        return array(
            'sms_administrator' => array(
                'name' => 'مدير النظام',
                'caps' => array(
                    'read' => true,
                    'read_sms_app' => true,
                    'manage_sms_system' => true,
                    'manage_sms_institutions' => true,
                    'manage_sms_users' => true,
                    'manage_sms_students' => true,
                    'edit_sms_profile' => true,
                    'access_wp_admin' => true,
                )
            ),
            'sms_general_manager' => array(
                'name' => 'مدير عام',
                'caps' => array(
                    'read' => true,
                    'read_sms_app' => true,
                    'manage_sms_system' => true,
                    'manage_sms_institutions' => true,
                    'manage_sms_users' => true,
                    'manage_sms_students' => true,
                    'edit_sms_profile' => true,
                    'access_wp_admin' => false,
                )
            ),
            'sms_dept_head' => array(
                'name' => 'رئيس قسم التربية الرياضية والصحية',
                'caps' => array(
                    'read' => true,
                    'read_sms_app' => true,
                    'manage_sms_institutions' => false,
                    'manage_sms_users' => true,
                    'manage_sms_students' => true,
                    'edit_sms_profile' => true,
                    'access_wp_admin' => false,
                )
            ),
            'sms_coordinator' => array(
                'name' => 'منسق التربية الرياضية والصحية',
                'caps' => array(
                    'read' => true,
                    'read_sms_app' => true,
                    'manage_sms_users' => false,
                    'manage_sms_students' => true,
                    'edit_sms_profile' => true,
                    'access_wp_admin' => false,
                )
            ),
            'sms_teacher' => array(
                'name' => 'معلم التربية الرياضية والصحية',
                'caps' => array(
                    'read' => true,
                    'read_sms_app' => true,
                    'manage_sms_students' => true,
                    'edit_sms_profile' => true,
                    'access_wp_admin' => false,
                )
            ),
            'sms_student' => array(
                'name' => 'طالب',
                'caps' => array(
                    'read' => true,
                    'read_sms_app' => true,
                    'edit_sms_profile' => true,
                    'access_wp_admin' => false,
                )
            ),
        );
    }

    public static function register_roles() {
        $roles = self::get_roles_config();
        foreach ($roles as $role_key => $role_info) {
            if (!get_role($role_key)) {
                add_role($role_key, $role_info['name'], $role_info['caps']);
            }
        }

        // Grant WP Administrator full SMS capabilities
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->add_cap('read_sms_app');
            $admin_role->add_cap('manage_sms_system');
            $admin_role->add_cap('manage_sms_institutions');
            $admin_role->add_cap('manage_sms_users');
            $admin_role->add_cap('manage_sms_students');
            $admin_role->add_cap('edit_sms_profile');
            $admin_role->add_cap('access_wp_admin');
        }
    }
}
