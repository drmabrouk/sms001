<?php
if (!defined('ABSPATH')) {
    exit;
}

class SMS_Students {
    public static function create_or_update_student($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'sms_students';

        $id                  = isset($data['id']) ? intval($data['id']) : 0;
        $first_name          = sanitize_text_field($data['first_name']);
        $last_name           = sanitize_text_field($data['last_name']);
        $gender              = sanitize_text_field($data['gender']);
        $nationality         = sanitize_text_field($data['nationality']);
        $country             = sanitize_text_field($data['country']);
        $grade               = sanitize_text_field($data['grade']);
        $class_section       = sanitize_text_field($data['class_section']);
        $parent_phone        = sanitize_text_field($data['parent_phone']);
        $parent_email        = sanitize_email($data['parent_email']);
        $health_status       = sanitize_textarea_field($data['health_status']);
        $preferred_sports    = sanitize_textarea_field($data['preferred_sports']);
        $institution_id      = isset($data['institution_id']) ? intval($data['institution_id']) : 0;
        $is_active_account   = isset($data['is_active_account']) ? intval($data['is_active_account']) : 0;
        $membership_number   = sanitize_text_field($data['membership_number']);
        $membership_validity = sanitize_text_field($data['membership_validity']);
        $user_id             = isset($data['user_id']) ? intval($data['user_id']) : 0;

        // Account Activation Toggle: If Yes and user_id is 0, create WP User
        if ($is_active_account && $user_id === 0 && !empty($parent_email)) {
            $username = 'student_' . time() . '_' . rand(100, 999);
            $user_id  = wp_create_user($username, wp_generate_password(), $parent_email);
            if (!is_wp_error($user_id)) {
                $user = new WP_User($user_id);
                $user->set_role('sms_student');
                wp_update_user(array(
                    'ID'           => $user_id,
                    'first_name'   => $first_name,
                    'last_name'    => $last_name,
                    'display_name' => $first_name . ' ' . $last_name
                ));
            } else {
                $user_id = 0;
            }
        }

        $record = array(
            'first_name'          => $first_name,
            'last_name'           => $last_name,
            'gender'              => $gender,
            'nationality'         => $nationality,
            'country'             => $country,
            'grade'               => $grade,
            'class_section'       => $class_section,
            'parent_phone'        => $parent_phone,
            'parent_email'        => $parent_email,
            'health_status'       => $health_status,
            'preferred_sports'    => $preferred_sports,
            'institution_id'      => $institution_id,
            'is_active_account'   => $is_active_account,
            'membership_number'   => $membership_number,
            'membership_validity' => $membership_validity,
            'user_id'             => $user_id
        );

        if ($id > 0) {
            $wpdb->update($table, $record, array('id' => $id));
            return $id;
        } else {
            $wpdb->insert($table, $record);
            return $wpdb->insert_id;
        }
    }
}
