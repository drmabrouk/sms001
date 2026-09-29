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
            PRIMARY KEY  (id)
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
            KEY institution_id (institution_id)
        ) $charset_collate;";

        $table_user_institutions = $wpdb->prefix . 'sms_user_institutions';
        $sql_user_institutions = "CREATE TABLE IF NOT EXISTS $table_user_institutions (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            institution_id BIGINT(20) UNSIGNED NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_inst (user_id, institution_id)
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
            KEY institution_id (institution_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_institutions);
        dbDelta($sql_students);
        dbDelta($sql_user_institutions);
        dbDelta($sql_lessons);
    }
}
