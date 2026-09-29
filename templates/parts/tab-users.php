<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$institutions = SMS_Institutions::get_all();
$roles = SMS_Roles::get_roles_config();
$students_table = $wpdb->prefix . 'sms_students';

$subtab = isset($_GET['subtab']) ? sanitize_text_field($_GET['subtab']) : 'users';
?>
<div class="sms-page-header">
    <h2 class="sms-page-title">إدارة مستخدمي النظام والطلاب</h2>
    <div style="display: flex; gap: 10px;">
        <a href="?tab=users&subtab=users" class="sms-btn <?php echo $subtab === 'users' ? 'sms-btn-dark' : 'sms-btn-outline'; ?>">بطاقات مستخدمي النظام</a>
        <a href="?tab=users&subtab=students" class="sms-btn <?php echo $subtab === 'students' ? 'sms-btn-dark' : 'sms-btn-outline'; ?>">سجلات الطلاب</a>
        <?php if ($subtab === 'users'): ?>
            <button type="button" class="sms-btn sms-btn-dark" id="sms-btn-add-user">+ إضافة مستخدم</button>
        <?php else: ?>
            <button type="button" class="sms-btn sms-btn-dark" id="sms-btn-add-student">+ إضافة طالب جديد</button>
        <?php endif; ?>
    </div>
</div>

<?php if ($subtab === 'users'): ?>
    <!-- Search, Filter and Sorting Toolbar for User Cards -->
    <div class="sms-card" style="padding: 16px; margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: center;">
            <div class="sms-floating-field" style="margin-bottom: 0;">
                <input type="text" id="sms-user-search-input" placeholder=" " />
                <label for="sms-user-search-input">البحث (الاسم / البريد / رقم العضوية)...</label>
            </div>
            <div class="sms-floating-field" style="margin-bottom: 0;">
                <select id="sms-user-filter-role">
                    <option value="">جميع الأدوار</option>
                    <?php foreach ($roles as $r_key => $r_val): ?>
                        <option value="<?php echo esc_attr($r_key); ?>"><?php echo esc_html($r_val['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="sms-user-filter-role">تصفية حسب الدور</label>
            </div>
            <div class="sms-floating-field" style="margin-bottom: 0;">
                <select id="sms-user-filter-institution">
                    <option value="">جميع المؤسسات</option>
                    <?php foreach ($institutions as $inst): ?>
                        <option value="<?php echo esc_attr($inst['id']); ?>"><?php echo esc_html($inst['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="sms-user-filter-institution">المؤسسة</label>
            </div>
            <div class="sms-floating-field" style="margin-bottom: 0;">
                <select id="sms-user-filter-status">
                    <option value="">جميع الحالات</option>
                    <option value="active">نشط</option>
                    <option value="inactive">غير نشط</option>
                    <option value="expired">منتهي الصلاحية</option>
                </select>
                <label for="sms-user-filter-status">الحالة</label>
            </div>
            <div class="sms-floating-field" style="margin-bottom: 0;">
                <select id="sms-user-sort-order">
                    <option value="newest">الأحدث أولاً (افتراضي)</option>
                    <option value="oldest">الأقدم أولاً</option>
                    <option value="name">الاسم (أبجدي)</option>
                </select>
                <label for="sms-user-sort-order">الترتيب</label>
            </div>
        </div>
    </div>

    <!-- User Cards Grid Container -->
    <div id="sms-user-cards-container" class="sms-user-grid">
        <!-- Rendered via AJAX or initial loop -->
        <?php
        $args = array(
            'orderby' => 'user_registered',
            'order'   => 'DESC',
        );
        $all_users = get_users($args);
        foreach ($all_users as $u):
            $status = SMS_Auth::get_user_status($u->ID);
            $badge_class = 'sms-badge-' . $status;
            $status_label = ($status === 'active') ? 'نشط' : (($status === 'expired') ? 'منتهي الصلاحية' : 'غير نشط');
            $user_roles = (array)$u->roles;
            $role_label = !empty($user_roles) && isset($roles[$user_roles[0]]) ? $roles[$user_roles[0]]['name'] : 'مستخدم';
            $validity = get_user_meta($u->ID, 'sms_membership_validity', true);
            $mem_num = get_user_meta($u->ID, 'sms_membership_number', true);
            $u_inst_ids = SMS_Users::get_user_institutions($u->ID);
            $u_inst_names = array();
            foreach ($institutions as $inst) {
                if (in_array($inst['id'], $u_inst_ids)) {
                    $u_inst_names[] = $inst['name'];
                }
            }
            $inst_str = !empty($u_inst_names) ? implode('، ', $u_inst_names) : 'غير محدد';
        ?>
            <div class="sms-user-card">
                <div class="sms-user-card-header">
                    <div class="sms-user-card-avatar">
                        <?php echo esc_html(mb_substr($u->display_name, 0, 1, 'UTF-8')); ?>
                    </div>
                    <div>
                        <strong style="font-size: 0.95rem; display: block;"><?php echo esc_html($u->display_name); ?></strong>
                        <span style="font-size: 0.8rem; color: var(--sms-text-muted);"><?php echo esc_html($u->user_login); ?></span>
                    </div>
                </div>

                <div class="sms-user-card-body">
                    <div><strong>الدور:</strong> <?php echo esc_html($role_label); ?></div>
                    <div><strong>المؤسسة:</strong> <?php echo esc_html($inst_str); ?></div>
                    <div><strong>البريد:</strong> <?php echo esc_html($u->user_email); ?></div>
                </div>

                <div class="sms-user-card-meta">
                    <div>رقم العضوية: <strong><?php echo esc_html($mem_num ? $mem_num : '-'); ?></strong></div>
                    <span class="sms-badge <?php echo esc_attr($badge_class); ?>"><?php echo esc_html($status_label); ?></span>
                </div>

                <div class="sms-user-card-actions">
                    <button type="button" class="sms-btn sms-btn-outline sms-btn-edit-user"
                        data-id="<?php echo esc_attr($u->ID); ?>"
                        data-username="<?php echo esc_attr($u->user_login); ?>"
                        data-email="<?php echo esc_attr($u->user_email); ?>"
                        data-firstname="<?php echo esc_attr(get_user_meta($u->ID, 'first_name', true)); ?>"
                        data-lastname="<?php echo esc_attr(get_user_meta($u->ID, 'last_name', true)); ?>"
                        data-role="<?php echo esc_attr(!empty($user_roles) ? $user_roles[0] : ''); ?>"
                        data-insts='<?php echo esc_attr(json_encode($u_inst_ids)); ?>'
                        data-memnum="<?php echo esc_attr($mem_num); ?>"
                        data-memval="<?php echo esc_attr($validity); ?>"
                        style="height: 32px; padding: 0 10px; font-size: 0.8rem; flex: 1;">تعديل</button>
                    <button type="button" class="sms-btn sms-btn-outline sms-btn-toggle-status" data-id="<?php echo esc_attr($u->ID); ?>" data-status="<?php echo esc_attr($status); ?>" style="height: 32px; padding: 0 10px; font-size: 0.8rem; flex: 1;">تغيير الحالة</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Add/Edit User Modal -->
    <div class="sms-modal-backdrop" id="sms-modal-user">
        <div class="sms-modal">
            <div class="sms-modal-header">
                <h3 class="sms-modal-title" id="sms-modal-user-title">إضافة مستخدم جديد</h3>
                <button type="button" class="sms-modal-close" id="sms-modal-user-close">&times;</button>
            </div>
            <form id="sms-form-user">
                <input type="hidden" id="usr_id" name="user_id" value="0" />
                <div class="sms-grid-2">
                    <div class="sms-floating-field">
                        <input type="text" id="usr_username" name="username" placeholder=" " required />
                        <label for="usr_username">اسم المستخدم (Login)</label>
                    </div>
                    <div class="sms-floating-field">
                        <input type="email" id="usr_email" name="email" placeholder=" " required />
                        <label for="usr_email">البريد الإلكتروني</label>
                    </div>
                </div>
                <div class="sms-grid-2">
                    <div class="sms-floating-field">
                        <input type="text" id="usr_firstname" name="first_name" placeholder=" " />
                        <label for="usr_firstname">الاسم الأول</label>
                    </div>
                    <div class="sms-floating-field">
                        <input type="text" id="usr_lastname" name="last_name" placeholder=" " />
                        <label for="usr_lastname">اسم العائلة</label>
                    </div>
                </div>
                <div class="sms-grid-2">
                    <div class="sms-floating-field">
                        <select id="usr_role" name="role" required>
                            <?php foreach ($roles as $r_key => $r_val): ?>
                                <option value="<?php echo esc_attr($r_key); ?>"><?php echo esc_html($r_val['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label for="usr_role">دور المستخدم</label>
                    </div>
                    <div class="sms-floating-field sms-password-toggle-wrapper">
                        <input type="password" id="usr_pass" name="password" placeholder=" " />
                        <label for="usr_pass">كلمة المرور (اتركه فارغاً عند التعديل)</label>
                        <button type="button" class="sms-password-toggle-btn" data-target="usr_pass">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <div class="sms-grid-2">
                    <div class="sms-floating-field">
                        <input type="text" id="usr_mem_num" name="membership_number" placeholder=" " />
                        <label for="usr_mem_num">رقم العضوية</label>
                    </div>
                    <div class="sms-floating-field">
                        <input type="date" id="usr_mem_val" name="membership_validity" placeholder=" " />
                        <label for="usr_mem_val">تاريخ انتهاء العضوية</label>
                    </div>
                </div>
                <div class="sms-floating-field">
                    <select id="usr_insts" name="institution_ids[]" multiple style="height: 90px; padding-top:20px;">
                        <?php foreach ($institutions as $inst): ?>
                            <option value="<?php echo esc_attr($inst['id']); ?>"><?php echo esc_html($inst['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="usr_insts" style="top: 8px; transform: none; font-size: 0.72rem;">المؤسسات المرتبطة (Ctrl للاختيار المتعدد לרئيس القسم)</label>
                </div>
                <div style="margin-top: 20px; text-align: left;">
                    <button type="submit" class="sms-btn sms-btn-dark">حفظ البيانات</button>
                </div>
            </form>
        </div>
    </div>

<?php else: ?>
    <!-- Students Subtab Cards View -->
    <?php
    $all_students = $wpdb->get_results("SELECT * FROM $students_table ORDER BY id DESC", ARRAY_A);
    ?>
    <div class="sms-user-grid">
        <?php if (empty($all_students)): ?>
            <div class="sms-card" style="grid-column: 1 / -1; text-align: center; color: var(--sms-text-muted);">
                لا يوجد سجلات طلاب حالياً.
            </div>
        <?php else: ?>
            <?php foreach ($all_students as $st):
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
                            <strong style="font-size: 0.95rem; display: block;"><?php echo esc_html($st['first_name'] . ' ' . $st['last_name']); ?></strong>
                            <span style="font-size: 0.8rem; color: var(--sms-text-muted);"><?php echo esc_html($st['grade'] . ' / ' . $st['class_section']); ?></span>
                        </div>
                    </div>

                    <div class="sms-user-card-body">
                        <div><strong>المؤسسة:</strong> <?php echo esc_html($st_inst_name); ?></div>
                        <div><strong>هاتف ولي الأمر:</strong> <?php echo esc_html($st['parent_phone']); ?></div>
                        <div><strong>بريد ولي الأمر:</strong> <?php echo esc_html($st['parent_email']); ?></div>
                    </div>

                    <div class="sms-user-card-meta">
                        <div>حساب المستخدم:</div>
                        <span class="sms-badge <?php echo $st['is_active_account'] ? 'sms-badge-active' : 'sms-badge-inactive'; ?>">
                            <?php echo $st['is_active_account'] ? 'مفعل' : 'غير مفعل'; ?>
                        </span>
                    </div>

                    <div class="sms-user-card-actions">
                        <button type="button" class="sms-btn sms-btn-outline sms-btn-edit-student" data-student='<?php echo esc_attr(json_encode($st)); ?>' style="height: 32px; padding: 0 10px; font-size: 0.8rem; width: 100%;">تعديل البيانات</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Add/Edit Student Modal -->
    <div class="sms-modal-backdrop" id="sms-modal-student">
        <div class="sms-modal">
            <div class="sms-modal-header">
                <h3 class="sms-modal-title" id="sms-modal-student-title">إضافة طالب جديد</h3>
                <button type="button" class="sms-modal-close" id="sms-modal-student-close">&times;</button>
            </div>
            <form id="sms-form-student">
                <input type="hidden" id="st_id" name="id" value="0" />
                <div class="sms-grid-2">
                    <div class="sms-floating-field">
                        <input type="text" id="st_first_name" name="first_name" placeholder=" " required />
                        <label for="st_first_name">الاسم الأول</label>
                    </div>
                    <div class="sms-floating-field">
                        <input type="text" id="st_last_name" name="last_name" placeholder=" " required />
                        <label for="st_last_name">اسم العائلة</label>
                    </div>
                </div>
                <div class="sms-grid-2">
                    <div class="sms-floating-field">
                        <select id="st_gender" name="gender">
                            <option value="ذكر">ذكر</option>
                            <option value="أنثى">أنثى</option>
                        </select>
                        <label for="st_gender">الجنس</label>
                    </div>
                    <div class="sms-floating-field">
                        <select id="st_institution_id" name="institution_id">
                            <option value="0">اختر المؤسسة...</option>
                            <?php foreach ($institutions as $inst): ?>
                                <option value="<?php echo esc_attr($inst['id']); ?>"><?php echo esc_html($inst['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label for="st_institution_id">المؤسسة التعليمية</label>
                    </div>
                </div>
                <div class="sms-grid-2">
                    <div class="sms-floating-field">
                        <input type="text" id="st_grade" name="grade" placeholder=" " />
                        <label for="st_grade">الصف الدراسي</label>
                    </div>
                    <div class="sms-floating-field">
                        <input type="text" id="st_class_section" name="class_section" placeholder=" " />
                        <label for="st_class_section">الشعبة / الفصل</label>
                    </div>
                </div>
                <div class="sms-grid-2">
                    <div class="sms-floating-field">
                        <input type="text" id="st_parent_phone" name="parent_phone" placeholder=" " />
                        <label for="st_parent_phone">رقم هاتف ولي الأمر</label>
                    </div>
                    <div class="sms-floating-field">
                        <input type="email" id="st_parent_email" name="parent_email" placeholder=" " />
                        <label for="st_parent_email">البريد الإلكتروني لولي الأمر</label>
                    </div>
                </div>
                <div class="sms-floating-field">
                    <select id="st_is_active_account" name="is_active_account">
                        <option value="0">لا (حفظ كسجل طالب فقط)</option>
                        <option value="1">نعم (تفعيل حساب دخول كـ طالب)</option>
                    </select>
                    <label for="st_is_active_account">تفعيل حساب الدخول للمستخدم (Activate Student Account)</label>
                </div>
                <div class="sms-grid-2">
                    <div class="sms-floating-field">
                        <input type="text" id="st_membership_number" name="membership_number" placeholder=" " />
                        <label for="st_membership_number">رقم العضوية</label>
                    </div>
                    <div class="sms-floating-field">
                        <input type="date" id="st_membership_validity" name="membership_validity" placeholder=" " />
                        <label for="st_membership_validity">صلاحية العضوية</label>
                    </div>
                </div>
                <div class="sms-floating-field">
                    <textarea id="st_health_status" name="health_status" placeholder=" "></textarea>
                    <label for="st_health_status">الحالة الصحية</label>
                </div>
                <div class="sms-floating-field">
                    <textarea id="st_preferred_sports" name="preferred_sports" placeholder=" "></textarea>
                    <label for="st_preferred_sports">الرياضات المفضلة</label>
                </div>
                <div style="margin-top: 20px; text-align: left;">
                    <button type="submit" class="sms-btn sms-btn-dark">حفظ بيانات الطالب</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
