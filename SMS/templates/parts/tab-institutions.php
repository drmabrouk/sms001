<?php
if (!defined('ABSPATH')) {
    exit;
}

// Predefine standard default institutional records if database empty
global $wpdb;
$inst_table = $wpdb->prefix . 'sms_institutions';
$count = $wpdb->get_var("SELECT COUNT(*) FROM $inst_table");
if ($count == 0) {
    SMS_Institutions::create_or_update(array('name' => 'مدرسة الشعلة الخاصة', 'code' => 'SHL-01', 'type' => 'مدرسة خاصة', 'city' => 'الشارقة', 'address' => 'الشارقة - المنطقة المدرسية', 'phone' => '065000001'));
    SMS_Institutions::create_or_update(array('name' => 'مدرسة مجمع الشعلة الخاصة', 'code' => 'SHL-02', 'type' => 'مجمع مدرسي', 'city' => 'عجمان', 'address' => 'عجمان - الجرف', 'phone' => '065000002'));
    SMS_Institutions::create_or_update(array('name' => 'مدرسة الشعلة الخاصة الصناعية', 'code' => 'SHL-03', 'type' => 'مدرسة صناعية', 'city' => 'الشارقة', 'address' => 'الشارقة - الصجعة', 'phone' => '065000003'));
}

$institutions = SMS_Institutions::get_all();
?>
<div class="sms-page-header">
    <div>
        <h2 class="sms-page-title">إدارة المؤسسات</h2>
        <p class="sms-section-description">إدارة المؤسسات المدرسية والتأكد من إحصائيات المعلمين والمنسقين والطلاب التابعين لها.</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" class="sms-btn sms-btn-outline" id="sms-btn-import-inst">استيراد CSV</button>
        <button type="button" class="sms-btn sms-btn-outline" id="sms-btn-export-inst">تصدير CSV</button>
        <button type="button" class="sms-btn sms-btn-dark" id="sms-btn-add-inst">+ إضافة مؤسسة جديدة</button>
    </div>
</div>

<!-- Dynamic Search Bar for Institutions -->
<div class="sms-card" style="padding:12px; margin-bottom:16px;">
    <div class="sms-floating-field" style="margin-bottom:0;">
        <input type="text" id="sms-inst-search-input" placeholder=" " />
        <label for="sms-inst-search-input">البحث في المؤسسات (اسم المؤسسة / المدينة / الكود)...</label>
    </div>
</div>

<div id="sms-inst-cards-container" class="sms-user-grid">
    <?php if (empty($institutions)): ?>
        <div class="sms-card" style="grid-column: 1 / -1; text-align: center; color: var(--sms-text-muted);">لا توجد مؤسسات مسجلة حالياً.</div>
    <?php else: ?>
        <?php foreach ($institutions as $inst):
            // Calculate Statistics per Institution
            $table_inst_users = $wpdb->prefix . 'sms_user_institutions';
            $user_ids = $wpdb->get_col($wpdb->prepare("SELECT user_id FROM $table_inst_users WHERE institution_id = %d", $inst['id']));

            $teachers_count = 0;
            $coordinators_count = 0;
            $dept_heads_count = 0;

            if (!empty($user_ids)) {
                foreach ($user_ids as $uid) {
                    $u = get_userdata($uid);
                    if ($u) {
                        $r = (array)$u->roles;
                        if (in_array('sms_teacher', $r)) $teachers_count++;
                        if (in_array('sms_coordinator', $r)) $coordinators_count++;
                        if (in_array('sms_dept_head', $r)) $dept_heads_count++;
                    }
                }
            }

            $table_st = $wpdb->prefix . 'sms_students';
            $students_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_st WHERE institution_id = %d", $inst['id']));
        ?>
            <div class="sms-user-card sms-inst-card" data-name="<?php echo esc_attr($inst['name'] . ' ' . $inst['city'] . ' ' . $inst['code']); ?>">
                <div class="sms-user-card-header">
                    <div class="sms-user-card-avatar" style="border-radius: var(--sms-radius-md);">
                        🏫
                    </div>
                    <div>
                        <strong style="font-size: 0.95rem; display: block;"><?php echo esc_html($inst['name']); ?></strong>
                        <span style="font-size: 0.78rem; color: var(--sms-text-muted);"><?php echo esc_html($inst['type']); ?> - <?php echo esc_html($inst['city']); ?> (كود: <?php echo esc_html($inst['code']); ?>)</span>
                    </div>
                </div>

                <div class="sms-user-card-body">
                    <div><strong>العنوان:</strong> <?php echo esc_html($inst['address']); ?></div>
                    <div><strong>الهاتف:</strong> <?php echo esc_html($inst['phone']); ?></div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; margin-top:6px; font-size:0.75rem; background:#f8fafc; padding:8px; border-radius:var(--sms-radius-sm);">
                        <div>الطلاب: <strong><?php echo esc_html($students_count); ?></strong></div>
                        <div>المعلمون: <strong><?php echo esc_html($teachers_count); ?></strong></div>
                        <div>المنسقون: <strong><?php echo esc_html($coordinators_count); ?></strong></div>
                        <div>رؤساء الأقسام: <strong><?php echo esc_html($dept_heads_count); ?></strong></div>
                    </div>
                </div>

                <div class="sms-user-card-actions">
                    <button type="button" class="sms-btn sms-btn-outline sms-btn-edit-inst" data-inst='<?php echo esc_attr(json_encode($inst)); ?>' style="height: 30px; padding: 0 10px; font-size: 0.78rem; flex:1;">تعديل</button>
                    <button type="button" class="sms-btn sms-btn-danger sms-btn-del-inst" data-id="<?php echo esc_attr($inst['id']); ?>" style="height: 30px; padding: 0 10px; font-size: 0.78rem; flex:1;">حذف المؤسسة</button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Add/Edit Institution Modal -->
<div class="sms-modal-backdrop" id="sms-modal-inst">
    <div class="sms-modal">
        <div class="sms-modal-header">
            <h3 class="sms-modal-title" id="sms-modal-inst-title">إضافة مؤسسة جديدة</h3>
            <button type="button" class="sms-modal-close" id="sms-modal-inst-close">&times;</button>
        </div>
        <form id="sms-form-inst">
            <input type="hidden" id="inst_id" name="id" value="0" />
            <div class="sms-floating-field">
                <input type="text" id="inst_name" name="name" placeholder=" " required />
                <label for="inst_name">اسم المؤسسة</label>
            </div>
            <div class="sms-grid-2">
                <div class="sms-floating-field">
                    <input type="text" id="inst_code" name="code" placeholder=" " />
                    <label for="inst_code">الكود التعريف المعياري</label>
                </div>
                <div class="sms-floating-field">
                    <input type="text" id="inst_type" name="type" placeholder=" " />
                    <label for="inst_type">النوع (مدرسة / مجمع / نادٍ)</label>
                </div>
            </div>
            <div class="sms-grid-2">
                <div class="sms-floating-field">
                    <input type="text" id="inst_city" name="city" placeholder=" " />
                    <label for="inst_city">المدينة</label>
                </div>
                <div class="sms-floating-field">
                    <input type="text" id="inst_phone" name="phone" placeholder=" " />
                    <label for="inst_phone">رقم الهاتف</label>
                </div>
            </div>
            <div class="sms-floating-field">
                <textarea id="inst_address" name="address" placeholder=" "></textarea>
                <label for="inst_address">العنوان</label>
            </div>
            <div style="margin-top: 20px; text-align: left;">
                <button type="submit" class="sms-btn sms-btn-dark">حفظ المؤسسة</button>
            </div>
        </form>
    </div>
</div>

<!-- Import Institutions CSV Modal -->
<div class="sms-modal-backdrop" id="sms-modal-import-inst">
    <div class="sms-modal">
        <div class="sms-modal-header">
            <h3 class="sms-modal-title">استيراد مؤسسات من ملف CSV</h3>
            <button type="button" class="sms-modal-close" id="sms-modal-import-inst-close">&times;</button>
        </div>
        <form id="sms-form-import-inst" enctype="multipart/form-data">
            <div class="sms-floating-field" style="margin-top: 10px;">
                <input type="file" id="csv_file" name="csv_file" accept=".csv" required style="padding-top:10px;" />
            </div>
            <p style="font-size:0.8rem; color:var(--sms-text-muted);">تنسيق الأعمدة المطلوبة: (ID, الاسم, الكود, النوع, المدينة, العنوان, الهاتف)</p>
            <div style="margin-top: 20px; text-align: left;">
                <button type="submit" class="sms-btn sms-btn-dark">رفع واستيراد</button>
            </div>
        </form>
    </div>
</div>
