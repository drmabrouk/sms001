<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$settings_tab = isset($_GET['stab']) ? sanitize_text_field($_GET['stab']) : 'general';

$country = get_option('sms_doc_country', 'وزارة دولة الإمارات العربية المتحدة');
$ministry = get_option('sms_doc_ministry', 'وزارة التربية والتعليم');
$foundation = get_option('sms_doc_foundation', 'مؤسسة الشعلة للتعليم والتطوير');
$font_scale = get_option('sms_font_scale', '100%');

$act_table = $wpdb->prefix . 'sms_activity_log';
$activities = $wpdb->get_results("SELECT * FROM $act_table WHERE is_deleted = 0 ORDER BY id DESC LIMIT 150", ARRAY_A);
$deleted_activities = $wpdb->get_results("SELECT * FROM $act_table WHERE is_deleted = 1 AND deleted_at >= NOW() - INTERVAL 1 DAY ORDER BY id DESC", ARRAY_A);
?>
<div class="sms-page-header">
    <div>
        <h2 class="sms-page-title">إعدادات النظام</h2>
        <p class="sms-section-description">إدارة التكوينات العامة وثائق المخرجات والنسخ الاحتياطي وسجل النشاطات.</p>
    </div>
</div>

<div class="sms-card" style="padding: 10px 14px; margin-bottom: 20px;">
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="?tab=settings&stab=general" class="sms-btn <?php echo $settings_tab === 'general' ? 'sms-btn-dark' : 'sms-btn-outline'; ?>" style="height:34px; font-size:0.8rem;">الإعدادات العامة</a>
        <a href="?tab=settings&stab=doc_info" class="sms-btn <?php echo $settings_tab === 'doc_info' ? 'sms-btn-dark' : 'sms-btn-outline'; ?>" style="height:34px; font-size:0.8rem;">بيانات الوثائق الموحدة</a>
        <a href="?tab=settings&stab=typography" class="sms-btn <?php echo $settings_tab === 'typography' ? 'sms-btn-dark' : 'sms-btn-outline'; ?>" style="height:34px; font-size:0.8rem;">إعدادات الخط والخطوط</a>
        <a href="?tab=settings&stab=backup" class="sms-btn <?php echo $settings_tab === 'backup' ? 'sms-btn-dark' : 'sms-btn-outline'; ?>" style="height:34px; font-size:0.8rem;">النسخ الاحتياطي والاستعادة</a>
        <a href="?tab=settings&stab=activity" class="sms-btn <?php echo $settings_tab === 'activity' ? 'sms-btn-dark' : 'sms-btn-outline'; ?>" style="height:34px; font-size:0.8rem;">سجل النشاطات</a>
    </div>
</div>

<?php if ($settings_tab === 'doc_info'): ?>
    <!-- Document Information Tab -->
    <form id="sms-form-doc-info" class="sms-card">
        <h3 style="font-size: 1rem; margin-top:0; margin-bottom: 14px;">الهويّة والترويسة الموحدة للوثائق والتقارير</h3>
        <p style="font-size:0.82rem; color:var(--sms-text-muted); margin-bottom:16px;">
            تُستخدم هذه البيانات رسمياً في الترويسة الموحدة لكافة تقارير الحضور والتحضير والشهادات المستخرجة من النظام.
        </p>
        <div class="sms-floating-field">
            <input type="text" id="doc_country" name="doc_country" value="<?php echo esc_attr($country); ?>" placeholder=" " required />
            <label for="doc_country">الدولة / الجهة العليا</label>
        </div>
        <div class="sms-floating-field">
            <input type="text" id="doc_ministry" name="doc_ministry" value="<?php echo esc_attr($ministry); ?>" placeholder=" " required />
            <label for="doc_ministry">الوزارة المعنية</label>
        </div>
        <div class="sms-floating-field">
            <input type="text" id="doc_foundation" name="doc_foundation" value="<?php echo esc_attr($foundation); ?>" placeholder=" " required />
            <label for="doc_foundation">المؤسسة / المجموعة التعليمية</label>
        </div>
        <div style="margin-top: 16px; text-align: left;">
            <button type="submit" class="sms-btn sms-btn-dark">حفظ بيانات الوثائق</button>
        </div>
    </form>

<?php elseif ($settings_tab === 'typography'): ?>
    <!-- Typography Settings Tab -->
    <form id="sms-form-typography" class="sms-card">
        <h3 style="font-size: 1rem; margin-top:0; margin-bottom: 14px;">التحكم بالحجم العام لخط التطبيق (Cairo Font Scale)</h3>
        <p style="font-size:0.82rem; color:var(--sms-text-muted); margin-bottom:16px;">
            يؤثر هذا الخيار حصرياً على واجهة نظام الإدارة الرياضية ولا يغير الخطوط في لوحة تحكم ووردبريس الرئيسية.
        </p>
        <div class="sms-floating-field">
            <select id="setting_font_scale" name="font_scale">
                <option value="90%" <?php selected($font_scale, '90%'); ?>>صغير ومدمج (Compact - 90%)</option>
                <option value="100%" <?php selected($font_scale, '100%'); ?>>الافتراضي المتناسق (Standard - 100%)</option>
                <option value="110%" <?php selected($font_scale, '110%'); ?>>كبير ومقروء (Large - 110%)</option>
            </select>
            <label for="setting_font_scale">مقياس حجم الخط</label>
        </div>
        <div style="margin-top: 16px; text-align: left;">
            <button type="submit" class="sms-btn sms-btn-dark">حفظ حجم الخط</button>
        </div>
    </form>

<?php elseif ($settings_tab === 'backup'): ?>
    <!-- Backup & Restore Tab -->
    <div class="sms-card">
        <h3 style="font-size: 1rem; margin-top:0; margin-bottom: 8px;">تنزيل نسخة احتياطية شاملة (Backup)</h3>
        <p style="font-size:0.82rem; color:var(--sms-text-muted); margin-bottom:14px;">
            تنزيل ملف النسخة الاحتياطية بصيغة JSON المحتوية على المؤسسات، المستخدمين، الطلاب، والتحضيرات.
        </p>
        <button type="button" class="sms-btn sms-btn-dark" id="sms-btn-download-backup">تنزيل ملف النسخة الاحتياطية</button>
    </div>

    <div class="sms-card">
        <h3 style="font-size: 1rem; margin-top:0; margin-bottom: 8px;">استعادة البيانات من نسخة احتياطية (Restore)</h3>
        <p style="font-size:0.82rem; color:var(--sms-text-muted); margin-bottom:14px;">
            رفع ملف النسخة الاحتياطية (JSON) لاستعادة هيكل وقواعد البيانات.
        </p>
        <form id="sms-form-restore-backup" enctype="multipart/form-data">
            <div class="sms-floating-field">
                <input type="file" name="backup_file" accept=".json" required style="padding-top:10px;" />
            </div>
            <button type="submit" class="sms-btn sms-btn-outline">بدء استعادة النسخة الاحتياطية</button>
        </form>
    </div>

    <div class="sms-card" style="border-color: var(--sms-pastel-rose-border); background: var(--sms-pastel-rose-bg);">
        <h3 style="font-size: 1rem; margin-top:0; color: var(--sms-danger); margin-bottom: 8px;">حذف وإعادة ضبط كافة بيانات SMS (Purge Data)</h3>
        <p style="font-size:0.82rem; color: var(--sms-text-secondary); margin-bottom:14px;">
            تنويه حساس: هذا الخيار مخصص لمدير النظام لحذف كافة سجلات النظام المضافة نهائياً.
        </p>
        <button type="button" class="sms-btn sms-btn-danger" id="sms-btn-purge-data">حذف كافة البيانات وإعادة الضبط</button>
    </div>

<?php elseif ($settings_tab === 'activity'): ?>
    <!-- Activity Log Tab -->
    <div class="sms-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <h3 style="font-size: 1rem; margin:0;">سجل نشاطات النظام (الحد الأقصى 150 نشاطاً)</h3>
        </div>
        <div class="sms-table-container">
            <table class="sms-table">
                <thead>
                    <tr>
                        <th>النشاط / الإجراء</th>
                        <th>التفاصيل</th>
                        <th>التاريخ والوقت</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($activities)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center; color:var(--sms-text-muted);">لا توجد نشاطات مسجلة.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($activities as $act): ?>
                            <tr>
                                <td><strong><?php echo esc_html($act['action_title']); ?></strong></td>
                                <td><?php echo esc_html($act['details']); ?></td>
                                <td><?php echo esc_html(date('Y-m-d H:i', strtotime($act['created_at']))); ?></td>
                                <td>
                                    <button type="button" class="sms-btn sms-btn-danger sms-btn-delete-activity" data-id="<?php echo esc_attr($act['id']); ?>" style="height:28px; padding:0 8px; font-size:0.75rem;">حذف</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!empty($deleted_activities)): ?>
        <div class="sms-card">
            <h3 style="font-size: 1rem; margin-top:0; margin-bottom: 12px;">سلة استعادة النشاطات المحذوفة (خلال 24 ساعة)</h3>
            <div class="sms-table-container">
                <table class="sms-table">
                    <thead>
                        <tr>
                            <th>النشاط المحذوف</th>
                            <th>تاريخ الحذف</th>
                            <th>استعادة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deleted_activities as $dact): ?>
                            <tr>
                                <td><?php echo esc_html($dact['action_title']); ?></td>
                                <td><?php echo esc_html(date('Y-m-d H:i', strtotime($dact['deleted_at']))); ?></td>
                                <td>
                                    <button type="button" class="sms-btn sms-btn-outline sms-btn-recover-activity" data-id="<?php echo esc_attr($dact['id']); ?>" style="height:28px; padding:0 8px; font-size:0.75rem;">استعادة الإجراء</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- General Settings Tab -->
    <div class="sms-card">
        <h3 style="font-size: 1rem; margin-top:0;">الإعدادات العامة للنظام</h3>
        <p style="color: var(--sms-text-secondary); font-size: 0.85rem;">يمكنك استخدام التبويبات أعلاه للتحكم الشامل ببيانات الترويسة الموحدة، مقياس الخط، سجل النشاطات، والنسخ الاحتياطي.</p>
    </div>
<?php endif; ?>
