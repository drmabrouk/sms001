<?php
define('ABSPATH', __DIR__ . '/../');

// Simple Mock WP Environment for CLI Testing
global $wp_mock_options, $wp_mock_user_meta, $wp_mock_users, $wp_roles_mock, $wpdb;

$wp_mock_options = array();
$wp_mock_user_meta = array();
$wp_mock_users = array();
$wp_roles_mock = array();

class MockWPDB {
    public $prefix = 'wp_';
    public $tables = array();
    public $insert_id = 1;

    public function get_charset_collate() {
        return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
    }

    public function get_var($query) {
        return 0;
    }

    public function get_results($query, $output = 'OBJECT') {
        return array();
    }

    public function get_col($query) {
        return array();
    }

    public function prepare($query, ...$args) {
        return $query;
    }

    public function insert($table, $data, $format = null) {
        $this->insert_id = rand(10, 100);
        return 1;
    }

    public function update($table, $data, $where, $format = null, $where_format = null) {
        return 1;
    }

    public function delete($table, $where, $where_format = null) {
        return 1;
    }
}
$wpdb = new MockWPDB();

function get_role($role) {
    global $wp_roles_mock;
    return isset($wp_roles_mock[$role]) ? $wp_roles_mock[$role] : null;
}

function add_role($role, $display_name, $capabilities = array()) {
    global $wp_roles_mock;
    $wp_roles_mock[$role] = (object) array(
        'name' => $display_name,
        'capabilities' => $capabilities,
        'add_cap' => function($cap) use (&$capabilities) { $capabilities[$cap] = true; }
    );
    return $wp_roles_mock[$role];
}

function get_option($option, $default = false) {
    global $wp_mock_options;
    return isset($wp_mock_options[$option]) ? $wp_mock_options[$option] : $default;
}

function update_option($option, $value, $autoload = null) {
    global $wp_mock_options;
    $wp_mock_options[$option] = $value;
    return true;
}

function get_user_meta($user_id, $key = '', $single = false) {
    global $wp_mock_user_meta;
    if (isset($wp_mock_user_meta[$user_id][$key])) {
        return $wp_mock_user_meta[$user_id][$key];
    }
    return $single ? '' : array();
}

function update_user_meta($user_id, $meta_key, $meta_value, $prev_value = '') {
    global $wp_mock_user_meta;
    $wp_mock_user_meta[$user_id][$meta_key] = $meta_value;
    return true;
}

function sanitize_text_field($str) {
    return trim(strip_tags($str));
}

function sanitize_email($email) {
    return trim(filter_var($email, FILTER_SANITIZE_EMAIL));
}

function sanitize_textarea_field($str) {
    return trim(strip_tags($str));
}

function get_userdata($user_id) {
    global $wp_mock_users;
    if (isset($wp_mock_users[$user_id])) {
        return (object) $wp_mock_users[$user_id];
    }
    return false;
}

function wp_create_user($username, $password, $email = '') {
    global $wp_mock_users;
    $id = count($wp_mock_users) + 10;
    $wp_mock_users[$id] = array(
        'ID' => $id,
        'user_login' => $username,
        'user_email' => $email,
        'roles' => array('sms_student'),
        'user_registered' => date('Y-m-d H:i:s')
    );
    return $id;
}

function wp_generate_password() {
    return 'rand_pass_' . rand(1000, 9999);
}

function is_wp_error($thing) {
    return is_a($thing, 'WP_Error');
}

class WP_Error {
    public $code;
    public $message;
    public function __construct($code = '', $message = '') {
        $this->code = $code;
        $this->message = $message;
    }
}

class WP_User {
    public $ID;
    public $roles = array();
    public function __construct($id) {
        $this->ID = $id;
    }
    public function set_role($role) {
        $this->roles = array($role);
        global $wp_mock_users;
        if (isset($wp_mock_users[$this->ID])) {
            $wp_mock_users[$this->ID]['roles'] = array($role);
        }
    }
}

function wp_update_user($userdata) {
    global $wp_mock_users;
    $id = $userdata['ID'];
    if (isset($wp_mock_users[$id])) {
        foreach ($userdata as $k => $v) {
            $wp_mock_users[$id][$k] = $v;
        }
    }
    return $id;
}

// Load Plugin Files
require_once __DIR__ . '/../includes/class-sms-roles.php';
require_once __DIR__ . '/../includes/class-sms-db.php';
require_once __DIR__ . '/../includes/class-sms-auth.php';
require_once __DIR__ . '/../includes/class-sms-users.php';
require_once __DIR__ . '/../includes/class-sms-students.php';
require_once __DIR__ . '/../includes/class-sms-institutions.php';

// Test Runner
$passed = 0;
$failed = 0;

function assert_test($condition, $title) {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] $title\n";
        $passed++;
    } else {
        echo "[FAIL] $title\n";
        $failed++;
    }
}

echo "=== Running Sports Management System (SMS) Automated Integration Tests ===\n\n";

// 1. Test Roles Registration
SMS_Roles::register_roles();
$roles_config = SMS_Roles::get_roles_config();
assert_test(count($roles_config) === 6, 'Should define 6 SMS system roles');
assert_test(isset($roles_config['sms_administrator']), 'Role sms_administrator should exist');
assert_test(isset($roles_config['sms_general_manager']), 'Role sms_general_manager should exist');
assert_test(isset($roles_config['sms_dept_head']), 'Role sms_dept_head should exist');
assert_test(isset($roles_config['sms_coordinator']), 'Role sms_coordinator should exist');
assert_test(isset($roles_config['sms_teacher']), 'Role sms_teacher should exist');
assert_test(isset($roles_config['sms_student']), 'Role sms_student should exist');

// 2. Test Arab Countries list
$countries = SMS_Users::get_arab_countries();
assert_test(count($countries) >= 20, 'Should return comprehensive Arab countries list');
assert_test(in_array('الإمارات العربية المتحدة', $countries), 'Should contain UAE');

// 3. Test Membership Expiration Logic
global $wp_mock_users;
$wp_mock_users[1] = array(
    'ID' => 1,
    'user_login' => 'test_user',
    'roles' => array('sms_teacher'),
    'user_registered' => '2020-01-01 00:00:00'
);
$is_expired = SMS_Auth::is_user_expired(1);
assert_test($is_expired === true, 'User registered in 2020 should be expired');

$wp_mock_users[2] = array(
    'ID' => 2,
    'user_login' => 'new_user',
    'roles' => array('sms_teacher'),
    'user_registered' => date('Y-m-d H:i:s')
);
$is_expired_new = SMS_Auth::is_user_expired(2);
assert_test($is_expired_new === false, 'Newly registered user should not be expired');

// 4. Test Student Account Activation Toggle
$student_id = SMS_Students::create_or_update_student(array(
    'first_name' => 'أحمد',
    'last_name' => 'علي',
    'gender' => 'ذكر',
    'nationality' => 'سعودي',
    'country' => 'المملكة العربية السعودية',
    'grade' => 'الصف العاشر',
    'class_section' => '1/10',
    'parent_phone' => '0500000000',
    'parent_email' => 'parent@example.com',
    'health_status' => 'ممتازة',
    'preferred_sports' => 'كرة القدم',
    'institution_id' => 1,
    'is_active_account' => 1,
    'membership_number' => 'SMS-101',
    'membership_validity' => '2026-12-31'
));
assert_test($student_id > 0, 'Should create student record with active account toggle enabled');

echo "\nTest Execution Summary: $passed Passed, $failed Failed.\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
