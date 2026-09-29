<?php
if (!defined('ABSPATH')) {
    exit;
}

class SMS_DB {
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $table_institutions = $wpdb->prefix . 'sms_institutions';
        $sql_institutions = "CREATE TABLE IF NOT EXISTS $table_institutions (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            code VARCHAR(100) DEFAULT '',
            type VARCHAR(100) DEFAULT 'school',
            city VARCHAR(100) DEFAULT '',
            address TEXT,
            phone VARCHAR(50) DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY name (name),
            KEY code (code),
            KEY city (city)
        ) $charset_collate;";

        $table_students = $wpdb->prefix . 'sms_students';
        $sql_students = "CREATE TABLE IF NOT EXISTS $table_students (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED DEFAULT 0,
            institution_id BIGINT(20) UNSIGNED DEFAULT 0,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            gender VARCHAR(20) DEFAULT '',
            nationality VARCHAR(100) DEFAULT '',
            country VARCHAR(100) DEFAULT '',
            grade VARCHAR(50) DEFAULT '',
            class_section VARCHAR(50) DEFAULT '',
            parent_phone VARCHAR(50) DEFAULT '',
            parent_email VARCHAR(100) DEFAULT '',
            health_status TEXT,
            preferred_sports TEXT,
            is_active_account TINYINT(1) DEFAULT 0,
            membership_number VARCHAR(100) DEFAULT '',
            membership_validity DATE DEFAULT NULL,
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY institution_id (institution_id),
            KEY status (status),
            KEY membership_number (membership_number)
        ) $charset_collate;";

        $table_user_institutions = $wpdb->prefix . 'sms_user_institutions';
        $sql_user_institutions = "CREATE TABLE IF NOT EXISTS $table_user_institutions (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            institution_id BIGINT(20) UNSIGNED NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_inst (user_id, institution_id),
            KEY user_id (user_id),
            KEY institution_id (institution_id)
        ) $charset_collate;";

        $table_lessons = $wpdb->prefix . 'sms_lesson_preparations';
        $sql_lessons = "CREATE TABLE IF NOT EXISTS $table_lessons (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            teacher_id BIGINT(20) UNSIGNED NOT NULL,
            institution_id BIGINT(20) UNSIGNED DEFAULT 0,
            prep_number INT(11) UNSIGNED NOT NULL,
            lesson_title VARCHAR(255) NOT NULL,
            file_url VARCHAR(255) NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            submission_time DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_late TINYINT(1) DEFAULT 0,
            review_status VARCHAR(50) DEFAULT 'pending',
            reviewer_id BIGINT(20) UNSIGNED DEFAULT 0,
            reviewed_at DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY teacher_id (teacher_id),
            KEY institution_id (institution_id),
            KEY is_late (is_late),
            KEY review_status (review_status),
            KEY submission_time (submission_time)
        ) $charset_collate;";

        $table_plans = $wpdb->prefix . 'sms_semester_plans';
        $sql_plans = "CREATE TABLE IF NOT EXISTS $table_plans (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            teacher_id BIGINT(20) UNSIGNED NOT NULL,
            institution_id BIGINT(20) UNSIGNED DEFAULT 0,
            semester VARCHAR(50) NOT NULL,
            plan_title VARCHAR(255) NOT NULL,
            file_url VARCHAR(255) NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            submission_time DATETIME DEFAULT CURRENT_TIMESTAMP,
            review_status VARCHAR(50) DEFAULT 'pending',
            reviewer_id BIGINT(20) UNSIGNED DEFAULT 0,
            reviewed_at DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY teacher_semester (teacher_id, semester),
            KEY teacher_id (teacher_id),
            KEY institution_id (institution_id)
        ) $charset_collate;";

        $table_reports = $wpdb->prefix . 'sms_reports';
        $sql_reports = "CREATE TABLE IF NOT EXISTS $table_reports (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            reporter_id BIGINT(20) UNSIGNED NOT NULL,
            teacher_id BIGINT(20) UNSIGNED NOT NULL,
            institution_id BIGINT(20) UNSIGNED DEFAULT 0,
            lesson_title VARCHAR(255) NOT NULL,
            attendance_status VARCHAR(50) DEFAULT 'present',
            rating INT(11) DEFAULT 5,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY reporter_id (reporter_id),
            KEY teacher_id (teacher_id),
            KEY institution_id (institution_id)
        ) $charset_collate;";

        $table_notifications = $wpdb->prefix . 'sms_notifications';
        $sql_notifications = "CREATE TABLE IF NOT EXISTS $table_notifications (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            type VARCHAR(100) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT,
            link VARCHAR(255) DEFAULT '',
            priority VARCHAR(50) DEFAULT 'normal',
            is_read TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_read (user_id, is_read),
            KEY created_at (created_at)
        ) $charset_collate;";

        $table_activity = $wpdb->prefix . 'sms_activity_log';
        $sql_activity = "CREATE TABLE IF NOT EXISTS $table_activity (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            action_title VARCHAR(255) NOT NULL,
            details TEXT,
            is_deleted TINYINT(1) DEFAULT 0,
            deleted_at DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY is_deleted (is_deleted),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_institutions);
        dbDelta($sql_students);
        dbDelta($sql_user_institutions);
        dbDelta($sql_lessons);
        dbDelta($sql_plans);
        dbDelta($sql_reports);
        dbDelta($sql_notifications);
        dbDelta($sql_activity);
    }

    public static function log_activity($user_id, $action_title, $details = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'sms_activity_log';

        $wpdb->insert($table, array(
            'user_id'      => intval($user_id),
            'action_title' => sanitize_text_field($action_title),
            'details'      => sanitize_textarea_field($details),
            'is_deleted'   => 0,
            'created_at'   => current_time('mysql')
        ));

        // Enforce 150 Maximum Activities Limit
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE is_deleted = 0");
        if ($count > 150) {
            $excess = $count - 150;
            $wpdb->query("DELETE FROM $table WHERE is_deleted = 0 ORDER BY id ASC LIMIT $excess");
        }
    }
}
