<?php
if (!defined('ABSPATH')) {
    exit;
}

class SMS_Export_Import {
    public static function export_institutions_csv() {
        if (!SMS_Auth::is_admin_user()) {
            wp_die('Unauthorized');
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=institutions_export_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, array('ID', 'الاسم', 'الكود', 'النوع', 'المدينة', 'العنوان', 'الهاتف'));

        $institutions = SMS_Institutions::get_all();
        foreach ($institutions as $inst) {
            fputcsv($output, array(
                $inst['id'],
                $inst['name'],
                $inst['code'],
                $inst['type'],
                $inst['city'],
                $inst['address'],
                $inst['phone']
            ));
        }
        fclose($output);
        exit;
    }

    public static function import_institutions_csv($file_path) {
        if (!file_exists($file_path)) return false;

        $handle = fopen($file_path, 'r');
        if (!$handle) return false;

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle);
        $imported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row[1])) continue;

            SMS_Institutions::create_or_update(array(
                'name'    => $row[1],
                'code'    => isset($row[2]) ? $row[2] : '',
                'type'    => isset($row[3]) ? $row[3] : 'school',
                'city'    => isset($row[4]) ? $row[4] : '',
                'address' => isset($row[5]) ? $row[5] : '',
                'phone'   => isset($row[6]) ? $row[6] : ''
            ));
            $imported++;
        }
        fclose($handle);
        return $imported;
    }

    public static function import_users_csv($file_path) {
        if (!file_exists($file_path)) return array('success' => false, 'message' => 'الملف غير موجود');

        $handle = fopen($file_path, 'r');
        if (!$handle) return array('success' => false, 'message' => 'تعذر فتح الملف');

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle);
        $success_count = 0;
        $error_count = 0;
        $errors = array();

        // CSV Header Format: Username, Email, First Name, Last Name, Role, Membership Number, Institution ID
        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row[0]) || empty($row[1])) {
                $error_count++;
                continue;
            }

            $username  = sanitize_text_field($row[0]);
            $email     = sanitize_email($row[1]);
            $firstname = isset($row[2]) ? sanitize_text_field($row[2]) : '';
            $lastname  = isset($row[3]) ? sanitize_text_field($row[3]) : '';
            $role      = isset($row[4]) ? sanitize_text_field($row[4]) : 'sms_teacher';
            $mem_num   = isset($row[5]) ? sanitize_text_field($row[5]) : '';
            $inst_id   = isset($row[6]) ? intval($row[6]) : 0;

            if (username_exists($username) || email_exists($email)) {
                $error_count++;
                $errors[] = "المستخدم $username أو البريد $email موجود بالفعل.";
                continue;
            }

            // Default Password Generation: Membership Number x 3 (e.g., 12345 -> 123451234512345)
            $password = !empty($mem_num) ? str_repeat($mem_num, 3) : wp_generate_password();

            $user_id = wp_create_user($username, $password, $email);
            if (is_wp_error($user_id)) {
                $error_count++;
                $errors[] = $user_id->get_error_message();
                continue;
            }

            $user = new WP_User($user_id);
            $user->set_role($role);

            wp_update_user(array(
                'ID'           => $user_id,
                'first_name'   => $firstname,
                'last_name'    => $lastname,
                'display_name' => trim($firstname . ' ' . $lastname)
            ));

            update_user_meta($user_id, 'sms_membership_number', $mem_num);
            $reg_time = strtotime(current_time('mysql'));
            update_user_meta($user_id, 'sms_membership_validity', date('Y-m-d', strtotime('+1 year', $reg_time)));

            if ($inst_id > 0) {
                SMS_Users::update_user_institutions($user_id, array($inst_id));
            }

            $success_count++;
        }

        fclose($handle);
        return array(
            'success'       => true,
            'success_count' => $success_count,
            'error_count'   => $error_count,
            'errors'        => $errors
        );
    }
}
