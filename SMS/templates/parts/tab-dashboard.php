<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$inst_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}sms_institutions");
$student_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}sms_students");
$user_count = count_users()['total_users'];
?>
<div class="sms-page-header">
    <h2 class="sms-page-title">لوحة المعلومات</h2>
</div>

<div class="sms-grid-2" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <div class="sms-card" style="margin-bottom:0;">
        <div style="font-size: 0.85rem; color: var(--sms-text-muted); margin-bottom: 6px;">إجمالي المؤسسات التعليمية</div>
        <div style="font-size: 1.8rem; font-weight: 700; color: var(--sms-text-primary);"><?php echo esc_html($inst_count); ?></div>
    </div>
    <div class="sms-card" style="margin-bottom:0;">
        <div style="font-size: 0.85rem; color: var(--sms-text-muted); margin-bottom: 6px;">إجمالي الطلاب المسجلين</div>
        <div style="font-size: 1.8rem; font-weight: 700; color: var(--sms-text-primary);"><?php echo esc_html($student_count); ?></div>
    </div>
    <div class="sms-card" style="margin-bottom:0;">
        <div style="font-size: 0.85rem; color: var(--sms-text-muted); margin-bottom: 6px;">مستخدمي النظام</div>
        <div style="font-size: 1.8rem; font-weight: 700; color: var(--sms-text-primary);"><?php echo esc_html($user_count); ?></div>
    </div>
</div>

<div class="sms-card">
    <h3 style="font-size: 1.1rem; margin-top: 0; margin-bottom: 12px;">مرحباً بك في نظام إدارة الرياضة (SMS)</h3>
    <p style="color: var(--sms-text-secondary); line-height: 1.6; margin: 0;">
        يقدم النظام بيئة شاملة وموحدة لإدارة المؤسسات الرياضية، المعلمين، المنسقين، والطلاب بسهولة وسلاسة، مع مراعاة كاملة للغة العربية والاتجاه من اليمين إلى اليسار (RTL).
    </p>
</div>
