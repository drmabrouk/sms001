<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$user = wp_get_current_user();
$country = get_option('sms_doc_country', 'وزارة دولة الإمارات العربية المتحدة');
$ministry = get_option('sms_doc_ministry', 'وزارة التربية والتعليم');
$foundation = get_option('sms_doc_foundation', 'مؤسسة الشعلة للتعليم والتطوير');

$reports_table = $wpdb->prefix . 'sms_reports';
$reports = $wpdb->get_results("SELECT * FROM $reports_table ORDER BY id DESC", ARRAY_A);
?>
<div class="sms-page-header">
    <div>
        <h2 class="sms-page-title">إدارة التقارير</h2>
        <p class="sms-section-description">إعداد واعتماد تقارير زيارة الحضور الميداني للحصص الرياضية.</p>
    </div>
    <button type="button" class="sms-btn sms-btn-dark" id="sms-btn-create-report">+ إنشاء تقرير جديد</button>
</div>

<div class="sms-user-grid">
    <?php if (empty($reports)): ?>
        <div class="sms-card" style="grid-column: 1 / -1; text-align: center; color: var(--sms-text-muted);">
            لا توجد تقارير مضافة حالياً. انقر فوق "إنشاء تقرير جديد" للبدء.
        </div>
    <?php else: ?>
        <?php foreach ($reports as $rep):
            $t_user = get_userdata($rep['teacher_id']);
            $t_name = $t_user ? $t_user->display_name : 'معلم';
            $inst_ids = SMS_Users::get_user_institutions($rep['teacher_id']);
            $inst_name = 'غير محدد';
            if (!empty($inst_ids)) {
                $inst_data = SMS_Institutions::get_all();
                foreach ($inst_data as $i) {
                    if ($i['id'] == $inst_ids[0]) {
                        $inst_name = $i['name'];
                        break;
                    }
                }
            }
        ?>
            <div class="sms-user-card">
                <div class="sms-user-card-header">
                    <div class="sms-user-card-avatar">
                        <?php echo esc_html(mb_substr($t_name, 0, 1, 'UTF-8')); ?>
                    </div>
                    <div>
                        <strong style="font-size: 0.95rem; display: block;"><?php echo esc_html($rep['lesson_title']); ?></strong>
                        <span style="font-size: 0.8rem; color: var(--sms-text-muted);"><?php echo esc_html($t_name); ?> - <?php echo esc_html($inst_name); ?></span>
                    </div>
                </div>

                <div class="sms-user-card-body">
                    <div><strong>الحالة:</strong> <?php echo esc_html($rep['attendance_status'] === 'present' ? 'حاضر' : 'غائب'); ?></div>
                    <div><strong>التقييم:</strong> <?php echo esc_html($rep['rating']); ?> / 5</div>
                    <div><strong>تاريخ التقرير:</strong> <?php echo esc_html(date('Y-m-d H:i', strtotime($rep['created_at']))); ?></div>
                </div>

                <div class="sms-user-card-actions">
                    <button type="button" class="sms-btn sms-btn-outline sms-btn-print-report" data-rep='<?php echo esc_attr(json_encode(array_merge($rep, array('teacher_name' => $t_name, 'institution_name' => $inst_name)))); ?>' style="height:32px; font-size:0.8rem; width:100%;">طباعة / تصدير PDF</button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Create Report Modal -->
<div class="sms-modal-backdrop" id="sms-modal-report">
    <div class="sms-modal">
        <div class="sms-modal-header">
            <h3 class="sms-modal-title">إنشاء تقرير تقييم درس رياضي</h3>
            <button type="button" class="sms-modal-close" id="sms-modal-report-close">&times;</button>
        </div>
        <form id="sms-form-create-report">
            <div class="sms-floating-field" style="position:relative;">
                <input type="text" id="rep_teacher_search" placeholder=" " autocomplete="off" required />
                <label for="rep_teacher_search">ابحث عن المعلم (اكتب الاسم)...</label>
                <input type="hidden" id="rep_teacher_id" name="teacher_id" value="0" />
                <div id="rep_teacher_typeahead_results" style="position:absolute; top:100%; right:0; left:0; background:#fff; border:1px solid var(--sms-border-color); border-radius:var(--sms-radius-sm); z-index:100; max-height:150px; overflow-y:auto; display:none;"></div>
            </div>

            <div class="sms-floating-field">
                <input type="text" id="rep_lesson_title" name="lesson_title" placeholder=" " required />
                <label for="rep_lesson_title">عنوان الدرس الملاحظ</label>
            </div>

            <div class="sms-grid-2">
                <div class="sms-floating-field">
                    <select id="rep_attendance_status" name="attendance_status">
                        <option value="present">حاضر ومكتمل</option>
                        <option value="absent">غائب</option>
                    </select>
                    <label for="rep_attendance_status">حالة الحضور</label>
                </div>
                <div class="sms-floating-field">
                    <select id="rep_rating" name="rating">
                        <option value="5">5/5 - ممتاز جداً</option>
                        <option value="4">4/5 - جيد جداً</option>
                        <option value="3">3/5 - جيد</option>
                        <option value="2">2/5 - مقبول</option>
                        <option value="1">1/5 - يتطلب تحسين</option>
                    </select>
                    <label for="rep_rating">التقييم الميداني</label>
                </div>
            </div>

            <div class="sms-floating-field">
                <textarea id="rep_notes" name="notes" placeholder=" "></textarea>
                <label for="rep_notes">ملاحظات ووصايا الموجه/المنسق</label>
            </div>

            <div style="margin-top:20px; text-align:left;">
                <button type="submit" class="sms-btn sms-btn-dark">إنشاء وحفظ التقرير</button>
            </div>
        </form>
    </div>
</div>

<!-- Print/Export PDF Printable Area (Hidden on screen) -->
<div id="sms-printable-report-area" style="display:none;">
    <div style="direction:rtl; font-family:'Cairo', sans-serif; padding:30px; border:2px solid #000;">
        <div style="text-align:center; border-bottom:2px solid #000; padding-bottom:15px; margin-bottom:20px;">
            <h3 style="margin:0; font-size:1.1rem;"><?php echo esc_html($country); ?></h3>
            <h3 style="margin:4px 0; font-size:1.1rem;"><?php echo esc_html($ministry); ?></h3>
            <h4 style="margin:4px 0; font-size:1rem;"><?php echo esc_html($foundation); ?></h4>
            <h4 id="print_doc_inst_name" style="margin:4px 0; font-size:0.95rem; font-weight:normal;">المؤسسة: -</h4>
        </div>

        <h2 style="text-align:center; margin-bottom:20px; text-decoration:underline;">تقرير ملاحظة درس التربية الرياضية والصحية</h2>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; font-size:0.9rem; margin-bottom:20px;">
            <div><strong>اسم المعلم:</strong> <span id="print_rep_teacher_name"></span></div>
            <div><strong>عنوان الدرس:</strong> <span id="print_rep_lesson_title"></span></div>
            <div><strong>حالة الحضور:</strong> <span id="print_rep_attendance"></span></div>
            <div><strong>التقييم الميداني:</strong> <span id="print_rep_rating"></span></div>
            <div><strong>تاريخ التقرير:</strong> <span id="print_rep_date"></span></div>
        </div>

        <div style="margin-top:20px; border-top:1px solid #ccc; padding-top:10px;">
            <strong>توصيات وملاحظات المنسق/رئيس القسم:</strong>
            <p id="print_rep_notes" style="font-size:0.9rem; line-height:1.6; margin-top:8px;"></p>
        </div>

        <div style="margin-top:50px; display:flex; justify-content:space-between; text-align:center; font-size:0.85rem;">
            <div>توقيع المنسق / رئيس القسم<br/><br/>...........................</div>
            <div>اعتماد إدارة المؤسسة<br/><br/>...........................</div>
        </div>
    </div>
</div>
