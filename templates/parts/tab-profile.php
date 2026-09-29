<?php
if (!defined('ABSPATH')) {
    exit;
}

$user = wp_get_current_user();
$is_admin = SMS_Auth::is_admin_user();
$countries = SMS_Users::get_arab_countries();
$roles = (array) $user->roles;
$is_student = in_array('sms_student', $roles);
$institutions = SMS_Institutions::get_all();
$user_insts = SMS_Users::get_user_institutions($user->ID);

$first_name = get_user_meta($user->ID, 'first_name', true);
$last_name  = get_user_meta($user->ID, 'last_name', true);
$gender     = get_user_meta($user->ID, 'sms_gender', true);
$nat        = get_user_meta($user->ID, 'sms_nationality', true);
$country    = get_user_meta($user->ID, 'sms_country', true);
$job        = get_user_meta($user->ID, 'sms_job_position', true);
$mem_num    = get_user_meta($user->ID, 'sms_membership_number', true);
$mem_val    = get_user_meta($user->ID, 'sms_membership_validity', true);

// Student fields
$grade      = get_user_meta($user->ID, 'sms_grade', true);
$class_sec  = get_user_meta($user->ID, 'sms_class_section', true);
$parent_p   = get_user_meta($user->ID, 'sms_parent_phone', true);
$parent_e   = get_user_meta($user->ID, 'sms_parent_email', true);
$health     = get_user_meta($user->ID, 'sms_health_status', true);
$sports     = get_user_meta($user->ID, 'sms_preferred_sports', true);
?>

<div class="sms-page-header">
    <h2 class="sms-page-title">إدارة الملف الشخصي</h2>
</div>

<form id="sms-profile-form" class="sms-card">
    <div class="sms-grid-2">
        <div class="sms-floating-field">
            <input type="text" id="prof_first_name" name="first_name" value="<?php echo esc_attr($first_name); ?>" placeholder=" " required />
            <label for="prof_first_name">الاسم الأول</label>
        </div>
        <div class="sms-floating-field">
            <input type="text" id="prof_last_name" name="last_name" value="<?php echo esc_attr($last_name); ?>" placeholder=" " required />
            <label for="prof_last_name">اسم العائلة</label>
        </div>
    </div>

    <div class="sms-grid-2">
        <div class="sms-floating-field">
            <select id="prof_gender" name="gender" class="<?php echo !empty($gender) ? 'has-value' : ''; ?>">
                <option value=""></option>
                <option value="ذكر" <?php selected($gender, 'ذكر'); ?>>ذكر</option>
                <option value="أنثى" <?php selected($gender, 'أنثى'); ?>>أنثى</option>
            </select>
            <label for="prof_gender">الجنس</label>
        </div>
        <div class="sms-floating-field">
            <input type="text" id="prof_nationality" name="nationality" value="<?php echo esc_attr($nat); ?>" placeholder=" " />
            <label for="prof_nationality">الجنسية</label>
        </div>
    </div>

    <div class="sms-grid-2">
        <div class="sms-floating-field">
            <select id="prof_country" name="country" class="<?php echo !empty($country) ? 'has-value' : ''; ?>">
                <option value=""></option>
                <?php foreach ($countries as $c): ?>
                    <option value="<?php echo esc_attr($c); ?>" <?php selected($country, $c); ?>><?php echo esc_html($c); ?></option>
                <?php endforeach; ?>
            </select>
            <label for="prof_country">دولة الإقامة</label>
        </div>
        <div class="sms-floating-field">
            <input type="email" id="prof_email" name="email" value="<?php echo esc_attr($user->user_email); ?>" placeholder=" " required />
            <label for="prof_email">البريد الإلكتروني</label>
        </div>
    </div>

    <div class="sms-grid-2">
        <div class="sms-floating-field">
            <select id="prof_institution" name="institution_id" class="<?php echo !empty($user_insts) ? 'has-value' : ''; ?>">
                <option value=""></option>
                <?php foreach ($institutions as $inst): ?>
                    <option value="<?php echo esc_attr($inst['id']); ?>" <?php selected(in_array($inst['id'], $user_insts)); ?>><?php echo esc_html($inst['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <label for="prof_institution">المؤسسة التابع لها</label>
        </div>
        <div class="sms-floating-field">
            <input type="text" id="prof_job" name="job_position" value="<?php echo esc_attr($job); ?>" placeholder=" " />
            <label for="prof_job">المسمى الوظيفي</label>
        </div>
    </div>

    <div class="sms-grid-2">
        <div class="sms-floating-field">
            <input type="password" id="prof_pass" name="password" placeholder=" " />
            <label for="prof_pass">كلمة المرور الجديدة (اتركه فارغاً للإبقاء عليها)</label>
        </div>
        <div class="sms-floating-field">
            <input type="password" id="prof_pass_confirm" name="password_confirm" placeholder=" " />
            <label for="prof_pass_confirm">تأكيد كلمة المرور</label>
        </div>
    </div>

    <div class="sms-grid-2">
        <div class="sms-floating-field">
            <input type="text" id="prof_mem_num" name="membership_number" value="<?php echo esc_attr($mem_num); ?>" placeholder=" " <?php echo !$is_admin ? 'readonly' : ''; ?> />
            <label for="prof_mem_num">رقم العضوية <?php echo !$is_admin ? '(للقراءة فقط)' : ''; ?></label>
        </div>
        <div class="sms-floating-field">
            <input type="date" id="prof_mem_val" name="membership_validity" value="<?php echo esc_attr($mem_val); ?>" placeholder=" " <?php echo !$is_admin ? 'readonly' : ''; ?> />
            <label for="prof_mem_val">صلاحية العضوية <?php echo !$is_admin ? '(للقراءة فقط)' : ''; ?></label>
        </div>
    </div>

    <?php if ($is_student): ?>
        <hr style="border: 0; border-top: 1px solid var(--sms-border-color); margin: 24px 0;" />
        <h3 style="font-size: 1.1rem; margin-bottom: 16px;">بيانات الطالب الخاصة</h3>
        <div class="sms-grid-2">
            <div class="sms-floating-field">
                <input type="text" id="prof_grade" name="grade" value="<?php echo esc_attr($grade); ?>" placeholder=" " />
                <label for="prof_grade">الصف الدراسي</label>
            </div>
            <div class="sms-floating-field">
                <input type="text" id="prof_class_section" name="class_section" value="<?php echo esc_attr($class_sec); ?>" placeholder=" " />
                <label for="prof_class_section">الشعبة / الفصل</label>
            </div>
        </div>
        <div class="sms-grid-2">
            <div class="sms-floating-field">
                <input type="text" id="prof_parent_phone" name="parent_phone" value="<?php echo esc_attr($parent_p); ?>" placeholder=" " />
                <label for="prof_parent_phone">رقم هاتف ولي الأمر</label>
            </div>
            <div class="sms-floating-field">
                <input type="email" id="prof_parent_email" name="parent_email" value="<?php echo esc_attr($parent_e); ?>" placeholder=" " />
                <label for="prof_parent_email">البريد الإلكتروني لولي الأمر</label>
            </div>
        </div>
        <div class="sms-floating-field">
            <textarea id="prof_health" name="health_status" placeholder=" "><?php echo esc_textarea($health); ?></textarea>
            <label for="prof_health">الحالة الصحية</label>
        </div>
        <div class="sms-floating-field">
            <textarea id="prof_sports" name="preferred_sports" placeholder=" "><?php echo esc_textarea($sports); ?></textarea>
            <label for="prof_sports">الرياضات المفضلة</label>
        </div>
    <?php endif; ?>

    <div style="margin-top: 24px; text-align: left;">
        <button type="submit" class="sms-btn sms-btn-dark">حفظ التغييرات</button>
    </div>
</form>
