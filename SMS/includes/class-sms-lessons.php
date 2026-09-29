<?php
if (!defined('ABSPATH')) {
    exit;
}

class SMS_Lessons {
    public static function get_next_prep_number($teacher_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'sms_lesson_preparations';
        $max = $wpdb->get_var($wpdb->prepare("SELECT MAX(prep_number) FROM $table WHERE teacher_id = %d", $teacher_id));
        return $max ? (intval($max) + 1) : 1;
    }

    public static function is_submission_late($now_timestamp = null) {
        if (!$now_timestamp) {
            $now_timestamp = current_time('timestamp');
        }
        $day_of_week = date('N', $now_timestamp); // 1 = Monday, 5 = Friday, 7 = Sunday
        $hour = intval(date('H', $now_timestamp));

        // Window opens Friday 00:00. On-time deadline is Monday 09:00 AM.
        // If today is Monday (1) and hour >= 9, or Tuesday(2), Wednesday(3), Thursday(4), it's late.
        if ($day_of_week == 1 && $hour >= 9) {
            return 1;
        } elseif ($day_of_week > 1 && $day_of_week < 5) {
            return 1;
        }
        return 0;
    }

    public static function submit_lesson_prep($teacher_id, $lesson_title, $file) {
        global $wpdb;
        $table = $wpdb->prefix . 'sms_lesson_preparations';

        if (empty($lesson_title)) {
            return new WP_Error('empty_title', 'يرجى إدخال عنوان الدرس');
        }

        if (empty($file) || empty($file['tmp_name'])) {
            return new WP_Error('empty_file', 'يرجى اختيار ملف التحضير بصيغة PDF');
        }

        // Validate File Extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            return new WP_Error('invalid_extension', 'عفواً: يُسمح فقط برفع ملفات PDF (ملف غير صالح)');
        }

        // Validate MIME Type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if ($mime !== 'application/pdf') {
            return new WP_Error('invalid_mime', 'عفواً: صيغة الملف غير معتمدة (يجب أن يكون PDF)');
        }

        // Validate Max Size (5MB = 5 * 1024 * 1024 bytes)
        if ($file['size'] > 5 * 1024 * 1024) {
            return new WP_Error('file_too_large', 'عفواً: حجم الملف يتجاوز الحد الأقصى المسموح به (5 ميجابايت)');
        }

        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $upload_overrides = array('test_form' => false, 'mimes' => array('pdf' => 'application/pdf'));
        $movefile = wp_handle_upload($file, $upload_overrides);

        if (!$movefile || isset($movefile['error'])) {
            return new WP_Error('upload_error', 'حدث خطأ أثناء حفظ الملف: ' . ($movefile['error'] ?? 'فشل الرفع'));
        }

        $next_num = self::get_next_prep_number($teacher_id);
        $is_late  = self::is_submission_late();
        $inst_ids = SMS_Users::get_user_institutions($teacher_id);
        $institution_id = !empty($inst_ids) ? $inst_ids[0] : 0;

        $record = array(
            'teacher_id'      => $teacher_id,
            'institution_id'  => $institution_id,
            'prep_number'     => $next_num,
            'lesson_title'    => sanitize_text_field($lesson_title),
            'file_url'        => esc_url_raw($movefile['url']),
            'file_path'       => sanitize_text_field($movefile['file']),
            'submission_time' => current_time('mysql'),
            'is_late'         => $is_late,
            'review_status'   => 'pending'
        );

        $wpdb->insert($table, $record);
        return $wpdb->insert_id;
    }

    public static function get_teacher_preparations($teacher_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'sms_lesson_preparations';
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE teacher_id = %d ORDER BY prep_number DESC", $teacher_id), ARRAY_A);
    }

    public static function get_reviewer_preparations($user_id, $search = '', $status_filter = '', $inst_filter = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'sms_lesson_preparations';

        $user = get_userdata($user_id);
        if (!$user) return array();

        $roles = (array) $user->roles;
        $primary_role = !empty($roles) ? $roles[0] : '';
        $assigned_insts = SMS_Users::get_user_institutions($user_id);

        $where = array("1=1");

        // Role Scope
        if (in_array($primary_role, array('sms_coordinator', 'sms_teacher'))) {
            if (empty($assigned_insts)) return array();
            $inst_id = $assigned_insts[0];
            $where[] = "institution_id = $inst_id";
        } elseif ($primary_role === 'sms_dept_head') {
            if (empty($assigned_insts)) return array();
            $inst_list = implode(',', array_map('intval', $assigned_insts));
            $where[] = "institution_id IN ($inst_list)";
        }

        if ($inst_filter > 0) {
            $where[] = "institution_id = " . intval($inst_filter);
        }

        if (!empty($status_filter)) {
            $where[] = $wpdb->prepare("review_status = %s", $status_filter);
        }

        if (!empty($search)) {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where[] = $wpdb->prepare("lesson_title LIKE %s", $like);
        }

        $where_sql = implode(' AND ', $where);
        return $wpdb->get_results("SELECT * FROM $table WHERE $where_sql ORDER BY submission_time DESC", ARRAY_A);
    }

    public static function review_prep($prep_id, $reviewer_id, $status) {
        global $wpdb;
        $table = $wpdb->prefix . 'sms_lesson_preparations';
        return $wpdb->update($table, array(
            'review_status' => sanitize_text_field($status),
            'reviewer_id'   => intval($reviewer_id),
            'reviewed_at'   => current_time('mysql')
        ), array('id' => intval($prep_id)));
    }
}
