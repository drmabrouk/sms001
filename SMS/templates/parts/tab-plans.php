<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$user = wp_get_current_user();
$roles = (array) $user->roles;
$primary_role = !empty($roles) ? $roles[0] : '';
$is_teacher = ($primary_role === 'sms_teacher');
$is_reviewer = in_array($primary_role, array('sms_coordinator', 'sms_dept_head', 'administrator', 'sms_administrator', 'sms_general_manager'));

$my_plans = SMS_Lessons::get_teacher_plans($user->ID);
$submitted_semesters = array_column($my_plans, 'semester');
?>
<div class="sms-page-header">
    <div>
        <h2 class="sms-page-title">الخطط الفصلية</h2>
        <p class="sms-section-description">رفع واعتماد الخطط التدريسية الفصلية للأفصل الدراسية الثلاثة.</p>
    </div>
    <?php if ($is_teacher): ?>
        <button type="button" class="sms-btn sms-btn-dark" id="sms-btn-submit-plan">+ إضافة خطة فصلية جديدة</button>
    <?php endif; ?>
</div>

<?php if ($is_teacher): ?>
    <div class="sms-grid-2" style="grid-template-columns: repeat(3, 1fr); gap:12px; margin-bottom:20px;">
        <?php foreach (array('الفصل الأول', 'الفصل الثاني', 'الفصل الثالث') as $sem):
            $is_sub = in_array($sem, $submitted_semesters);
        ?>
            <div class="sms-card" style="margin-bottom:0; text-align:center;">
                <h4 style="margin:0 0 6px 0; font-size:0.95rem;"><?php echo esc_html($sem); ?></h4>
                <span class="sms-badge <?php echo $is_sub ? 'sms-badge-active' : 'sms-badge-inactive'; ?>">
                    <?php echo $is_sub ? 'تم التقديم' : 'غير مقدم'; ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>

    <h3 style="font-size:1rem; margin-bottom:12px;">سجل الخطط الفصلية المرفوعة</h3>
    <div class="sms-user-grid">
        <?php if (empty($my_plans)): ?>
            <div class="sms-card" style="grid-column: 1 / -1; text-align: center; color: var(--sms-text-muted);">
                لم تقم بتقديم أي خطة فصلية حتى الآن.
            </div>
        <?php else: ?>
            <?php foreach ($my_plans as $p):
                $status_class = ($p['review_status'] === 'approved') ? 'sms-badge-active' : (($p['review_status'] === 'rejected') ? 'sms-badge-expired' : 'sms-badge-inactive');
                $status_label = ($p['review_status'] === 'approved') ? 'معتمد' : (($p['review_status'] === 'rejected') ? 'مرفوض' : 'قيد المراجعة');
                $reviewer_user = $p['reviewer_id'] ? get_userdata($p['reviewer_id']) : null;
                $reviewer_name = $reviewer_user ? $reviewer_user->display_name : '';
            ?>
                <div class="sms-user-card">
                    <div class="sms-user-card-header">
                        <div>
                            <strong style="font-size:0.95rem; display:block;"><?php echo esc_html($p['plan_title']); ?></strong>
                            <span style="font-size:0.8rem; color:var(--sms-text-muted);"><?php echo esc_html($p['semester']); ?></span>
                        </div>
                    </div>

                    <div class="sms-user-card-body">
                        <div><strong>تاريخ التقديم:</strong> <?php echo esc_html(date('Y-m-d H:i', strtotime($p['submission_time']))); ?></div>
                        <div><strong>الحالة:</strong> <span class="sms-badge <?php echo esc_attr($status_class); ?>" title="<?php echo esc_attr($reviewer_name ? 'اعتمد بواسطة: ' . $reviewer_name . ' في ' . $p['reviewed_at'] : ''); ?>"><?php echo esc_html($status_label); ?></span></div>
                        <?php if ($reviewer_name): ?>
                            <div style="font-size:0.75rem; color:var(--sms-text-muted); margin-top:4px;">
                                بواسطة: <?php echo esc_html($reviewer_name); ?> (<?php echo esc_html($p['reviewed_at']); ?>)
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="sms-user-card-actions">
                        <a href="<?php echo esc_url($p['file_url']); ?>" target="_blank" class="sms-btn sms-btn-outline" style="height:30px; font-size:0.78rem; width:100%;">عرض ملف PDF</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($is_reviewer): ?>
    <?php $all_plans = SMS_Lessons::get_reviewer_plans($user->ID); ?>
    <h3 style="font-size:1rem; margin-bottom:12px;">مراجعة الخطط الفصلية للمعلمين</h3>
    <div class="sms-user-grid">
        <?php if (empty($all_plans)): ?>
            <div class="sms-card" style="grid-column: 1 / -1; text-align: center; color: var(--sms-text-muted);">
                لا توجد خطط فصلية للمراجعة حالياً.
            </div>
        <?php else: ?>
            <?php foreach ($all_plans as $plan):
                $t_user = get_userdata($plan['teacher_id']);
                $t_name = $t_user ? $t_user->display_name : 'معلم';
                $status_class = ($plan['review_status'] === 'approved') ? 'sms-badge-active' : (($plan['review_status'] === 'rejected') ? 'sms-badge-expired' : 'sms-badge-inactive');
                $status_label = ($plan['review_status'] === 'approved') ? 'معتمد' : (($plan['review_status'] === 'rejected') ? 'مرفوض' : 'قيد المراجعة');
                $rev_user = $plan['reviewer_id'] ? get_userdata($plan['reviewer_id']) : null;
                $rev_name = $rev_user ? $rev_user->display_name : '';
            ?>
                <div class="sms-user-card">
                    <div class="sms-user-card-header">
                        <div>
                            <strong style="font-size:0.95rem; display:block;"><?php echo esc_html($plan['plan_title']); ?></strong>
                            <span style="font-size:0.8rem; color:var(--sms-text-muted);"><?php echo esc_html($t_name); ?> - <?php echo esc_html($plan['semester']); ?></span>
                        </div>
                    </div>

                    <div class="sms-user-card-body">
                        <div><strong>تاريخ التقديم:</strong> <?php echo esc_html(date('Y-m-d H:i', strtotime($plan['submission_time']))); ?></div>
                        <div><strong>حالة الاعتماد:</strong> <span class="sms-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span></div>
                        <?php if ($rev_name): ?>
                            <div style="font-size:0.75rem; color:var(--sms-text-muted); margin-top:2px;" title="<?php echo esc_attr("اعتمد بتاريخ: {$plan['reviewed_at']}"); ?>">
                                اعتمد بواسطة: <?php echo esc_html($rev_name); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="sms-user-card-actions" style="flex-direction:column;">
                        <a href="<?php echo esc_url($plan['file_url']); ?>" target="_blank" class="sms-btn sms-btn-outline" style="height:30px; font-size:0.78rem; width:100%;">معاينة PDF</a>
                        <div style="display:flex; gap:6px; width:100%;">
                            <button type="button" class="sms-btn sms-btn-dark sms-btn-review-plan" data-id="<?php echo esc_attr($plan['id']); ?>" data-status="approved" style="height:30px; font-size:0.78rem; flex:1;">اعتماد</button>
                            <button type="button" class="sms-btn sms-btn-danger sms-btn-review-plan" data-id="<?php echo esc_attr($plan['id']); ?>" data-status="rejected" style="height:30px; font-size:0.78rem; flex:1;">رفض</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Submit Plan Modal -->
<div class="sms-modal-backdrop" id="sms-modal-submit-plan">
    <div class="sms-modal">
        <div class="sms-modal-header">
            <h3 class="sms-modal-title">إضافة خطة فصلية جديدة</h3>
            <button type="button" class="sms-modal-close" id="sms-modal-submit-plan-close">&times;</button>
        </div>
        <form id="sms-form-submit-plan" enctype="multipart/form-data">
            <div class="sms-floating-field" style="margin-top:10px;">
                <select id="plan_semester" name="semester" required>
                    <option value="الفصل الأول" <?php echo in_array('الفصل الأول', $submitted_semesters) ? 'disabled' : ''; ?>>الفصل الأول <?php echo in_array('الفصل الأول', $submitted_semesters) ? '(تم التقديم مسبقاً)' : ''; ?></option>
                    <option value="الفصل الثاني" <?php echo in_array('الفصل الثاني', $submitted_semesters) ? 'disabled' : ''; ?>>الفصل الثاني <?php echo in_array('الفصل الثاني', $submitted_semesters) ? '(تم التقديم مسبقاً)' : ''; ?></option>
                    <option value="الفصل الثالث" <?php echo in_array('الفصل الثالث', $submitted_semesters) ? 'disabled' : ''; ?>>الفصل الثالث <?php echo in_array('الفصل الثالث', $submitted_semesters) ? '(تم التقديم مسبقاً)' : ''; ?></option>
                </select>
                <label for="plan_semester">اختر الفصل الدراسي</label>
            </div>
            <div class="sms-floating-field">
                <input type="text" id="plan_title" name="plan_title" placeholder=" " required />
                <label for="plan_title">عنوان الخطة الفصلية</label>
            </div>
            <div class="sms-floating-field">
                <input type="file" id="plan_pdf_file" name="pdf_file" accept="application/pdf" required style="padding-top:10px;" />
            </div>
            <div style="margin-top: 20px; text-align: left;">
                <button type="submit" class="sms-btn sms-btn-dark">إرسال الخطة الفصلية</button>
            </div>
        </form>
    </div>
</div>
