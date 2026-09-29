<?php
if (!defined('ABSPATH')) {
    exit;
}

class SMS_AJAX {
    public function __construct() {
        add_action('wp_ajax_sms_save_profile', array($this, 'save_profile'));
        add_action('wp_ajax_sms_save_user', array($this, 'save_user'));
        add_action('wp_ajax_sms_save_student', array($this, 'save_student'));
        add_action('wp_ajax_sms_import_institutions', array($this, 'import_institutions'));
        add_action('wp_ajax_sms_save_institution', array($this, 'save_institution'));
        add_action('wp_ajax_sms_delete_institution', array($this, 'delete_institution'));
        add_action('wp_ajax_sms_toggle_user_status', array($this, 'toggle_user_status'));
    }

    public function save_profile() {
        check_ajax_referer('sms_nonce', 'security');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        $user_id = get_current_user_id();
        $is_admin = SMS_Auth::is_admin_user();

        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name  = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $email      = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

        if (!empty($email)) {
            wp_update_user(array('ID' => $user_id, 'user_email' => $email));
        }

        update_user_meta($user_id, 'first_name', $first_name);
        update_user_meta($user_id, 'last_name', $last_name);

        if (isset($_POST['gender'])) {
            update_user_meta($user_id, 'sms_gender', sanitize_text_field($_POST['gender']));
        }
        if (isset($_POST['nationality'])) {
            update_user_meta($user_id, 'sms_nationality', sanitize_text_field($_POST['nationality']));
        }
        if (isset($_POST['country'])) {
            update_user_meta($user_id, 'sms_country', sanitize_text_field($_POST['country']));
        }
        if (isset($_POST['job_position'])) {
            update_user_meta($user_id, 'sms_job_position', sanitize_text_field($_POST['job_position']));
        }

        // Institution mapping
        if (isset($_POST['institution_id'])) {
            $inst_id = intval($_POST['institution_id']);
            SMS_Users::update_user_institutions($user_id, array($inst_id));
        }

        // Admin-only editable membership fields
        if ($is_admin) {
            if (isset($_POST['membership_number'])) {
                update_user_meta($user_id, 'sms_membership_number', sanitize_text_field($_POST['membership_number']));
            }
            if (isset($_POST['membership_validity'])) {
                update_user_meta($user_id, 'sms_membership_validity', sanitize_text_field($_POST['membership_validity']));
            }
        }

        // Password change if provided
        if (!empty($_POST['password'])) {
            if (isset($_POST['password_confirm']) && $_POST['password'] === $_POST['password_confirm']) {
                wp_set_password($_POST['password'], $user_id);
            } else {
                wp_send_json_error(array('message' => 'كلمتا المرور غير متطابقتين'));
            }
        }

        // Student metadata if applicable
        if (isset($_POST['grade'])) {
            update_user_meta($user_id, 'sms_grade', sanitize_text_field($_POST['grade']));
            update_user_meta($user_id, 'sms_class_section', sanitize_text_field($_POST['class_section']));
            update_user_meta($user_id, 'sms_parent_phone', sanitize_text_field($_POST['parent_phone']));
            update_user_meta($user_id, 'sms_parent_email', sanitize_email($_POST['parent_email']));
            update_user_meta($user_id, 'sms_health_status', sanitize_textarea_field($_POST['health_status']));
            update_user_meta($user_id, 'sms_preferred_sports', sanitize_textarea_field($_POST['preferred_sports']));
        }

        wp_send_json_success(array('message' => 'تم حفظ البيانات بنجاح'));
    }

    public function save_user() {
        check_ajax_referer('sms_nonce', 'security');

        if (!current_user_can('manage_sms_users') && !SMS_Auth::is_admin_user()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        $user_id    = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $username   = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
        $email      = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name  = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $role       = isset($_POST['role']) ? sanitize_text_field($_POST['role']) : 'sms_teacher';
        $password   = isset($_POST['password']) ? $_POST['password'] : '';
        $inst_ids   = isset($_POST['institution_ids']) ? array_map('intval', (array)$_POST['institution_ids']) : array();

        if ($user_id === 0) {
            if (empty($username) || empty($email) || empty($password)) {
                wp_send_json_error(array('message' => 'يرجى ملء جميع الحقول المطلوبة (اسم المستخدم، البريد، كلمة المرور)'));
            }
            $user_id = wp_create_user($username, $password, $email);
            if (is_wp_error($user_id)) {
                wp_send_json_error(array('message' => $user_id->get_error_message()));
            }
        } else {
            if (!empty($password)) {
                wp_set_password($password, $user_id);
            }
            if (!empty($email)) {
                wp_update_user(array('ID' => $user_id, 'user_email' => $email));
            }
        }

        $user = new WP_User($user_id);
        $user->set_role($role);

        wp_update_user(array(
            'ID'           => $user_id,
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'display_name' => $first_name . ' ' . $last_name
        ));

        if (isset($_POST['membership_number'])) {
            update_user_meta($user_id, 'sms_membership_number', sanitize_text_field($_POST['membership_number']));
        }
        if (isset($_POST['membership_validity'])) {
            update_user_meta($user_id, 'sms_membership_validity', sanitize_text_field($_POST['membership_validity']));
        }

        SMS_Users::update_user_institutions($user_id, $inst_ids);

        wp_send_json_success(array('message' => 'تم حفظ المستخدم بنجاح'));
    }

    public function save_student() {
        check_ajax_referer('sms_nonce', 'security');

        if (!current_user_can('manage_sms_students') && !SMS_Auth::is_admin_user()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        $id = SMS_Students::create_or_update_student($_POST);
        wp_send_json_success(array('id' => $id, 'message' => 'تم حفظ بيانات الطالب بنجاح'));
    }

    public function import_institutions() {
        check_ajax_referer('sms_nonce', 'security');

        if (!SMS_Auth::is_admin_user()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        if (empty($_FILES['csv_file']['tmp_name'])) {
            wp_send_json_error(array('message' => 'يرجى اختيار ملف CSV'));
        }

        $imported = SMS_Export_Import::import_institutions_csv($_FILES['csv_file']['tmp_name']);
        if ($imported !== false) {
            wp_send_json_success(array('message' => "تم استيراد $imported مؤسسة بنجاح"));
        } else {
            wp_send_json_error(array('message' => 'حدث خطأ أثناء الاستيراد'));
        }
    }

    public function save_institution() {
        check_ajax_referer('sms_nonce', 'security');

        if (!current_user_can('manage_sms_institutions') && !SMS_Auth::is_admin_user()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        $id = SMS_Institutions::create_or_update($_POST);
        wp_send_json_success(array('id' => $id, 'message' => 'تم حفظ المؤسسة بنجاح'));
    }

    public function delete_institution() {
        check_ajax_referer('sms_nonce', 'security');

        if (!current_user_can('manage_sms_institutions') && !SMS_Auth::is_admin_user()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id > 0) {
            SMS_Institutions::delete($id);
            wp_send_json_success(array('message' => 'تم حذف المؤسسة بنجاح'));
        }

        wp_send_json_error(array('message' => 'خطأ في عملية الحذف'));
    }

    public function toggle_user_status() {
        check_ajax_referer('sms_nonce', 'security');

        if (!current_user_can('manage_sms_users') && !SMS_Auth::is_admin_user()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        $user_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $status  = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'active';

        $new_status = ($status === 'active') ? 'inactive' : 'active';
        update_user_meta($user_id, 'sms_account_status', $new_status);

        wp_send_json_success(array('new_status' => $new_status, 'message' => 'تم تغيير حالة المستخدم بنجاح'));
    }
}
