<?php
if (!defined('ABSPATH')) {
    exit;
}

$titles = array(
    'reports' => 'إدارة التقارير',
    'lessons' => 'تحضير الدروس',
    'plans'   => 'الخطط الفصلية',
);
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'reports';
$title = isset($titles[$current_tab]) ? $titles[$current_tab] : 'الوحدة';
?>
<div class="sms-page-header">
    <h2 class="sms-page-title"><?php echo esc_html($title); ?></h2>
</div>

<div class="sms-card" style="text-align: center; padding: 60px 20px;">
    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--sms-text-muted); margin-bottom: 16px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <h3 style="margin: 0 0 8px 0; font-size: 1.1rem; color: var(--sms-text-primary);">هذه الوحدة قيد التطوير حالياً</h3>
    <p style="margin: 0; color: var(--sms-text-muted); font-size: 0.9rem;">سيتم إضافة التخصيصات والبيانات الكاملة لوحدة (<?php echo esc_html($title); ?>) في التحديثات القادمة.</p>
</div>
