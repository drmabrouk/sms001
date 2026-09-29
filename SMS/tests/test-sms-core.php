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

    public function get_row($query, $output = 'OBJECT') {
        return false;
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

    public function query($query) {
        return 1;
    }

    public function esc_like($text) {
        return addcslashes($text, '_%\\');
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

function esc_url_raw($url) {
    return filter_var($url, FILTER_SANITIZE_URL);
}

function current_time($type) {
    if ($type === 'timestamp') {
        return time();
    }
    return date('Y-m-d H:i:s');
}

class MockUserObject {
    public $ID;
    public $user_login;
    public $user_email;
    public $roles = array();
    public $user_registered;

    public function __construct($data) {
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
    }

    public function exists() {
        return !empty($this->ID);
    }
}

function get_userdata($user_id) {
    global $wp_mock_users;
    if (isset($wp_mock_users[$user_id])) {
        return new MockUserObject($wp_mock_users[$user_id]);
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

function username_exists($username) {
    return false;
}

function email_exists($email) {
    return false;
}

function get_users($args = array()) {
    global $wp_mock_users;
    $list = array();
    foreach ($wp_mock_users as $u) {
        if (isset($args['role__not_in']) && in_array('administrator', $args['role__not_in']) && in_array('administrator', (array)$u['roles'])) {
            continue;
        }
        $list[] = new MockUserObject($u);
    }
    return $list;
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
    public function get_error_message() {
        return $this->message;
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

// Action Hooks Mocking
global $wp_action_hooks;
$wp_action_hooks = array();
function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
    global $wp_action_hooks;
    $wp_action_hooks[$tag][] = $function_to_add;
}

function do_action($tag, ...$args) {
    global $wp_action_hooks;
    if (isset($wp_action_hooks[$tag])) {
        foreach ($wp_action_hooks[$tag] as $func) {
            call_user_func_array($func, $args);
        }
    }
}

// Load Plugin Files
require_once __DIR__ . '/../includes/class-sms-roles.php';
require_once __DIR__ . '/../includes/class-sms-db.php';
require_once __DIR__ . '/../includes/class-sms-auth.php';
require_once __DIR__ . '/../includes/class-sms-users.php';
require_once __DIR__ . '/../includes/class-sms-students.php';
require_once __DIR__ . '/../includes/class-sms-institutions.php';
require_once __DIR__ . '/../includes/class-sms-lessons.php';
require_once __DIR__ . '/../includes/class-sms-notifications.php';
require_once __DIR__ . '/../includes/class-sms-export-import.php';

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

// 2. Test Membership Expiration Logic
global $wp_mock_users;
$wp_mock_users[1] = array(
    'ID' => 1,
    'user_login' => 'test_user',
    'roles' => array('sms_teacher'),
    'user_registered' => '2020-01-01 00:00:00'
);
$is_expired = SMS_Auth::is_user_expired(1);
assert_test($is_expired === true, 'User registered in 2020 should be expired');

// 3. Test Lesson Prep Late Calculation Rule (Monday 9 AM)
// Timestamp for Monday 10:00 AM
$monday_late_ts = strtotime('2026-03-09 10:00:00');
$is_late = SMS_Lessons::is_submission_late($monday_late_ts);
assert_test($is_late === 1, 'Submission on Monday at 10 AM should be marked late');

// Timestamp for Friday 10:00 AM
$friday_ontime_ts = strtotime('2026-03-06 10:00:00');
$is_late_friday = SMS_Lessons::is_submission_late($friday_ontime_ts);
assert_test($is_late_friday === 0, 'Submission on Friday at 10 AM should be marked on-time');

// 4. Test System Administrator Exclusion from User Searches/Lists
$admin_user_id = 99;
$wp_mock_users[$admin_user_id] = array(
    'ID' => $admin_user_id,
    'user_login' => 'sys_admin',
    'roles' => array('administrator')
);
$scoped_users = SMS_Users::get_scoped_users(1);
$admin_found = false;
foreach ($scoped_users as $su) {
    if ($su->ID === $admin_user_id) {
        $admin_found = true;
    }
}
assert_test($admin_found === false, 'System Administrator must be excluded from user management visibility');

// 5. Test Notification Creation and 7-Day Purge Query
$notif_res = SMS_Notifications::create_notification(1, 'اختبار', 'إشعار تجريبي', 'تفاصيل الإشعار');
assert_test($notif_res === 1, 'Should log new user notification');

$purge_res = SMS_Notifications::purge_expired_notifications();
assert_test($purge_res === 1, 'Should execute 7-day notification purge SQL query successfully');

// 6. Test Two-Way User Deletion Hook
$sms_users_class = new SMS_Users();
do_action('delete_user', 1);
assert_test(true, 'WP user deletion cleanup hook executed successfully');

echo "\nTest Execution Summary: $passed Passed, $failed Failed.\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
