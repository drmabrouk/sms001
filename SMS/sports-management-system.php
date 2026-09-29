<?php
/**
 * Plugin Name: Sports Management System (SMS)
 * Plugin URI:  https://example.com/sports-management-system
 * Description: نظام إدارة الرياضة (SMS) - تطبيق متكامل لتنظيم وإدارة المؤسسات الرياضية والمستخدمين والطلاب وتحضير الدروس.
 * Version:     1.0.0
 * Author:      SMS Team
 * Text Domain: sports-management-system
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('SMS_VERSION', '1.0.0');
define('SMS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SMS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SMS_PAGE_SLUG', 'sms-dashboard');
define('SMS_PAGE_TITLE', 'لوحة التحكم');

require_once SMS_PLUGIN_DIR . 'includes/class-sms-roles.php';
require_once SMS_PLUGIN_DIR . 'includes/class-sms-db.php';
require_once SMS_PLUGIN_DIR . 'includes/class-sms-activator.php';
require_once SMS_PLUGIN_DIR . 'includes/class-sms-deactivator.php';
require_once SMS_PLUGIN_DIR . 'includes/class-sms-auth.php';
require_once SMS_PLUGIN_DIR . 'includes/class-sms-institutions.php';
require_once SMS_PLUGIN_DIR . 'includes/class-sms-users.php';
require_once SMS_PLUGIN_DIR . 'includes/class-sms-students.php';
require_once SMS_PLUGIN_DIR . 'includes/class-sms-lessons.php';
require_once SMS_PLUGIN_DIR . 'includes/class-sms-notifications.php';
require_once SMS_PLUGIN_DIR . 'includes/class-sms-ajax.php';
require_once SMS_PLUGIN_DIR . 'includes/class-sms-export-import.php';
require_once SMS_PLUGIN_DIR . 'includes/class-sms-core.php';

register_activation_hook(__FILE__, array('SMS_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('SMS_Deactivator', 'deactivate'));

// Register Daily Cron Cleanup for 7-Day Notification Expiration
if (!wp_next_scheduled('sms_daily_notification_cleanup')) {
    wp_schedule_event(time(), 'daily', 'sms_daily_notification_cleanup');
}
add_action('sms_daily_notification_cleanup', array('SMS_Notifications', 'purge_expired_notifications'));

function run_sports_management_system() {
    $plugin = new SMS_Core();
    $plugin->run();
}
run_sports_management_system();
