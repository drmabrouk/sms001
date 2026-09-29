<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$students_table = $wpdb->prefix . 'sms_students';
$students = $wpdb->get_results("SELECT * FROM $students_table ORDER BY id DESC", ARRAY_A);
$institutions = SMS_Institutions::get_all();
?>
<div class="sms-page-header">
    <div>
        <h2 class="sms-page-title">إدارة شؤون الطلاب</h2>
        <p class="sms-section-description">إدارة وتحديث بيانات ملفات الطلاب واستيراد القوائم عبر ملفات Excel/CSV.</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" class="sms-btn sms-btn-outline" id="sms-btn-import-students-excel">استيراد طلاب (CSV/Excel)</button>
        <button type="button" class="sms-btn sms-btn-dark" id="sms-btn-add-student-affairs">+ إضافة طالب جديد</button>
    </div>
</div>

<div class="sms-user-grid">
    <?php if (empty($students)): ?>
        <div class="sms-card" style="grid-column: 1 / -1; text-align: center; color: var(--sms-text-muted);">
            لا يوجد طلاب مسجلون حالياً.
        </div>
    <?php else: ?>
        <?php foreach ($students as $st):
            $st_inst_name = 'غير محدد';
            foreach ($institutions as $inst) {
                if ($inst['id'] == $st['institution_id']) {
                    $st_inst_name = $inst['name'];
                    break;
                }
            }
        ?>
            <div class="sms-user-card">
                <div class="sms-user-card-header">
                    <div class="sms-user-card-avatar">
                        <?php echo esc_html(mb_substr($st['first_name'], 0, 1, 'UTF-8')); ?>
                    </div>
                    <div>
                        <strong style="font-size: 0.92rem; display: block;"><?php echo esc_html($st['first_name'] . ' ' . $st['last_name']); ?></strong>
                        <span style="font-size: 0.78rem; color: var(--sms-text-muted);"><?php echo esc_html($st['grade'] . ' / ' . $st['class_section']); ?></span>
                    </div>
                </div>

                <div class="sms-user-card-body">
                    <div><strong>المؤسسة:</strong> <?php echo esc_html($st_inst_name); ?></div>
                    <div><strong>هاتف ولي الأمر:</strong> <?php echo esc_html($st['parent_phone']); ?></div>
                    <div><strong>الحالة الصحية:</strong> <?php echo esc_html($st['health_status'] ? $st['health_status'] : 'سليم'); ?></div>
                </div>

                <div class="sms-user-card-meta">
                    <div>الحساب المفعل:</div>
                    <span class="sms-badge <?php echo $st['is_active_account'] ? 'sms-badge-active' : 'sms-badge-inactive'; ?>">
                        <?php echo $st['is_active_account'] ? 'مفعل' : 'سجل فقط'; ?>
                    </span>
                </div>

                <div class="sms-user-card-actions">
                    <button type="button" class="sms-btn sms-btn-outline sms-btn-edit-student" data-student='<?php echo esc_attr(json_encode($st)); ?>' style="height: 30px; font-size: 0.78rem; width: 100%;">تعديل ملف الطالب</button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal Student CSV/Excel Import -->
<div class="sms-modal-backdrop" id="sms-modal-import-students-excel">
    <div class="sms-modal">
        <div class="sms-modal-header">
            <h3 class="sms-modal-title">استيراد قوائم الطلاب من ملف CSV/Excel</h3>
            <button type="button" class="sms-modal-close" id="sms-modal-import-students-excel-close">&times;</button>
        </div>
        <form id="sms-form-import-students-excel" enctype="multipart/form-data">
            <div class="sms-floating-field" style="margin-top: 10px;">
                <input type="file" name="csv_file" accept=".csv" required style="padding-top:10px;" />
            </div>
            <p style="font-size:0.8rem; color:var(--sms-text-muted);">
                أعمدة الملف المطلوبة: (الاسم الأول, اسم العائلة, الجنس, الصف, الشعبة, هاتف ولي الأمر, البريد, معرف المؤسسة).
            </p>
            <div style="margin-top: 20px; text-align: left;">
                <button type="submit" class="sms-btn sms-btn-dark">رفع واستيراد الطلاب</button>
            </div>
        </form>
    </div>
</div>
