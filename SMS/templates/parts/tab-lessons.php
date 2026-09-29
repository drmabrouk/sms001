<?php
if (!defined('ABSPATH')) {
    exit;
}

$user = wp_get_current_user();
$roles = (array) $user->roles;
$primary_role = !empty($roles) ? $roles[0] : '';
$is_teacher = ($primary_role === 'sms_teacher');
$is_reviewer = in_array($primary_role, array('sms_coordinator', 'sms_dept_head', 'administrator', 'sms_administrator', 'sms_general_manager'));

$institutions = SMS_Institutions::get_all();
?>
<div class="sms-page-header">
    <h2 class="sms-page-title">تحضير الدروس</h2>
    <div style="display: flex; gap: 10px;">
        <?php if ($is_teacher): ?>
            <button type="button" class="sms-btn sms-btn-dark" id="sms-btn-submit-lesson">+ تقديم تحضير درس جديد</button>
        <?php endif; ?>
    </div>
</div>

<?php if ($is_teacher): ?>
    <!-- Teacher Submission Dashboard & History -->
    <?php
    $next_prep_num = SMS_Lessons::get_next_prep_number($user->ID);
    $my_preps = SMS_Lessons::get_teacher_preparations($user->ID);
    ?>
    <div class="sms-card" style="display: flex; justify-content: space-between; align-items: center; background: var(--sms-bg-card);">
        <div>
            <h3 style="margin: 0 0 6px 0; font-size: 1.1rem;">التحضير القادم المطلوب: <strong>تحضير الدرس <?php echo esc_html($next_prep_num); ?></strong></h3>
            <p style="margin: 0; color: var(--sms-text-muted); font-size: 0.85rem;">نافذة التقديم أسبوعية (تفتح الجمعة 00:00 وموعد التسليم التسليم المنظم الاثنين 09:00 صباحاً)</p>
        </div>
        <button type="button" class="sms-btn sms-btn-dark" id="sms-btn-quick-submit">+ رفع PDF التحضير</button>
    </div>

    <h3 style="font-size: 1.1rem; margin: 24px 0 16px 0;">سجل التحضيرات السابقة الخاصة بك</h3>
    <div class="sms-user-grid">
        <?php if (empty($my_preps)): ?>
            <div class="sms-card" style="grid-column: 1 / -1; text-align: center; color: var(--sms-text-muted);">
                لم تقم بتقديم أي تحضير دروس حتى الآن.
            </div>
        <?php else: ?>
            <?php foreach ($my_preps as $prep):
                $status_class = ($prep['review_status'] === 'approved') ? 'sms-badge-active' : (($prep['review_status'] === 'rejected') ? 'sms-badge-expired' : 'sms-badge-inactive');
                $status_label = ($prep['review_status'] === 'approved') ? 'معتمد' : (($prep['review_status'] === 'rejected') ? 'مرفوض' : 'قيد المراجعة');
                $late_class   = $prep['is_late'] ? 'sms-badge-expired' : 'sms-badge-active';
                $late_label   = $prep['is_late'] ? 'متأخر' : 'في الموعد';
            ?>
                <div class="sms-user-card">
                    <div class="sms-user-card-header">
                        <div class="sms-user-card-avatar" style="border-radius: var(--sms-radius-md);">
                            #<?php echo esc_html($prep['prep_number']); ?>
                        </div>
                        <div>
                            <strong style="font-size: 0.95rem; display: block;"><?php echo esc_html($prep['lesson_title']); ?></strong>
                            <span style="font-size: 0.8rem; color: var(--sms-text-muted);">تحضير الدرس <?php echo esc_html($prep['prep_number']); ?></span>
                        </div>
                    </div>

                    <div class="sms-user-card-body">
                        <div><strong>تاريخ التقديم:</strong> <?php echo esc_html(date('Y-m-d H:i', strtotime($prep['submission_time']))); ?></div>
                        <div style="display: flex; gap: 8px; margin-top: 4px;">
                            <span class="sms-badge <?php echo esc_attr($late_class); ?>"><?php echo esc_html($late_label); ?></span>
                            <span class="sms-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span>
                        </div>
                    </div>

                    <div class="sms-user-card-actions">
                        <a href="<?php echo esc_url($prep['file_url']); ?>" target="_blank" class="sms-btn sms-btn-outline" style="height:32px; padding:0 10px; font-size:0.8rem; width:100%;">عرض ملف PDF</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($is_reviewer): ?>
    <!-- Coordinator / Department Head Review Panel -->
    <div class="sms-card" style="padding: 16px; margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: center;">
            <div class="sms-floating-field" style="margin-bottom: 0;">
                <input type="text" id="sms-prep-search-input" placeholder=" " />
                <label for="sms-prep-search-input">البحث باسم الدرس أو المعلم...</label>
            </div>
            <div class="sms-floating-field" style="margin-bottom: 0;">
                <select id="sms-prep-filter-status">
                    <option value="">جميع حالات المراجعة</option>
                    <option value="pending">قيد المراجعة</option>
                    <option value="approved">معتمد</option>
                    <option value="rejected">مرفوض</option>
                </select>
                <label for="sms-prep-filter-status">الحالة</label>
            </div>
            <div class="sms-floating-field" style="margin-bottom: 0;">
                <select id="sms-prep-filter-institution">
                    <option value="0">جميع المؤسسات</option>
                    <?php foreach ($institutions as $inst): ?>
                        <option value="<?php echo esc_attr($inst['id']); ?>"><?php echo esc_html($inst['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="sms-prep-filter-institution">المؤسسة</label>
            </div>
        </div>
    </div>

    <div id="sms-prep-cards-container" class="sms-user-grid">
        <?php
        $preps = SMS_Lessons::get_reviewer_preparations($user->ID);
        if (empty($preps)): ?>
            <div class="sms-card" style="grid-column: 1 / -1; text-align: center; color: var(--sms-text-muted);">
                لا توجد تحضيرات دروس للمراجعة حالياً.
            </div>
        <?php else: ?>
            <?php foreach ($preps as $prep):
                $t_user = get_userdata($prep['teacher_id']);
                $t_name = $t_user ? $t_user->display_name : 'معلم';
                $status_class = ($prep['review_status'] === 'approved') ? 'sms-badge-active' : (($prep['review_status'] === 'rejected') ? 'sms-badge-expired' : 'sms-badge-inactive');
                $status_label = ($prep['review_status'] === 'approved') ? 'معتمد' : (($prep['review_status'] === 'rejected') ? 'مرفوض' : 'قيد المراجعة');
                $late_class   = $prep['is_late'] ? 'sms-badge-expired' : 'sms-badge-active';
                $late_label   = $prep['is_late'] ? 'متأخر' : 'في الموعد';
            ?>
                <div class="sms-user-card">
                    <div class="sms-user-card-header">
                        <div class="sms-user-card-avatar">
                            <?php echo esc_html(mb_substr($t_name, 0, 1, 'UTF-8')); ?>
                        </div>
                        <div>
                            <strong style="font-size: 0.95rem; display: block;"><?php echo esc_html($prep['lesson_title']); ?></strong>
                            <span style="font-size: 0.8rem; color: var(--sms-text-muted);"><?php echo esc_html($t_name); ?> - تحضير الدرس <?php echo esc_html($prep['prep_number']); ?></span>
                        </div>
                    </div>

                    <div class="sms-user-card-body">
                        <div><strong>تاريخ ووقت التقديم:</strong> <?php echo esc_html(date('Y-m-d H:i', strtotime($prep['submission_time']))); ?></div>
                        <div style="display: flex; gap: 8px; margin-top: 4px;">
                            <span class="sms-badge <?php echo esc_attr($late_class); ?>"><?php echo esc_html($late_label); ?></span>
                            <span class="sms-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span>
                        </div>
                    </div>

                    <div class="sms-user-card-actions" style="flex-direction: column;">
                        <a href="<?php echo esc_url($prep['file_url']); ?>" target="_blank" class="sms-btn sms-btn-outline" style="height:32px; padding:0 10px; font-size:0.8rem; width:100%;">معاينة PDF</a>
                        <div style="display: flex; gap: 8px; width: 100%;">
                            <button type="button" class="sms-btn sms-btn-dark sms-btn-review-prep" data-id="<?php echo esc_attr($prep['id']); ?>" data-status="approved" style="height:32px; padding:0 10px; font-size:0.8rem; flex:1;">اعتماد</button>
                            <button type="button" class="sms-btn sms-btn-danger sms-btn-review-prep" data-id="<?php echo esc_attr($prep['id']); ?>" data-status="rejected" style="height:32px; padding:0 10px; font-size:0.8rem; flex:1;">رفض</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Submit Lesson Modal -->
<div class="sms-modal-backdrop" id="sms-modal-submit-lesson">
    <div class="sms-modal">
        <div class="sms-modal-header">
            <h3 class="sms-modal-title">تقديم تحضير درس جديد</h3>
            <button type="button" class="sms-modal-close" id="sms-modal-submit-lesson-close">&times;</button>
        </div>
        <form id="sms-form-submit-lesson" enctype="multipart/form-data">
            <div class="sms-floating-field" style="margin-top: 10px;">
                <input type="text" id="prep_lesson_title" name="lesson_title" placeholder=" " required />
                <label for="prep_lesson_title">عنوان الدرس</label>
            </div>
            <div class="sms-floating-field">
                <input type="file" id="prep_pdf_file" name="pdf_file" accept="application/pdf" required style="padding-top:12px;" />
            </div>
            <p style="font-size:0.85rem; color:var(--sms-text-muted);">الملفات المقبولة: PDF فقط بحجم أقصى 5 ميجابايت. سيتم توليد الترقيم وحالة الموعد تلقائياً.</p>
            <div style="margin-top: 20px; text-align: left;">
                <button type="submit" class="sms-btn sms-btn-dark">إرسال التحضير</button>
            </div>
        </form>
    </div>
</div>
