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
    <div>
        <h2 class="sms-page-title">لوحة المعلومات</h2>
        <p class="sms-section-description">متابعة الإحصائيات العامة ومؤشرات الأداء الرياضي بالمؤسسات.</p>
    </div>
</div>

<div class="sms-grid-2" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 20px;">
    <div class="sms-card" style="margin-bottom:0;">
        <div style="font-size: 0.8rem; color: var(--sms-text-muted); margin-bottom: 4px;">إجمالي المؤسسات التعليمية</div>
        <div style="font-size: 1.5rem; font-weight: 800; color: var(--sms-text-primary);"><?php echo esc_html($inst_count); ?></div>
    </div>
    <div class="sms-card" style="margin-bottom:0;">
        <div style="font-size: 0.8rem; color: var(--sms-text-muted); margin-bottom: 4px;">إجمالي الطلاب المسجلين</div>
        <div style="font-size: 1.5rem; font-weight: 800; color: var(--sms-text-primary);"><?php echo esc_html($student_count); ?></div>
    </div>
    <div class="sms-card" style="margin-bottom:0;">
        <div style="font-size: 0.8rem; color: var(--sms-text-muted); margin-bottom: 4px;">مستخدمي النظام</div>
        <div style="font-size: 1.5rem; font-weight: 800; color: var(--sms-text-primary);"><?php echo esc_html($user_count); ?></div>
    </div>
</div>

<div class="sms-card">
    <h3 style="font-size: 1rem; margin-top: 0; margin-bottom: 8px;">مرحباً بك في نظام الإدارة الرياضية (SMS)</h3>
    <p style="color: var(--sms-text-secondary); line-height: 1.5; margin: 0; font-size:0.85rem;">
        بيئة رقمية متكاملة وموحدة لإدارة كافة أنشطة ومؤسسات التربية الرياضية والصحية بكفاءة وسلاسة عالية.
    </p>
</div>
