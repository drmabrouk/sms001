<?php
if (!defined('ABSPATH')) {
    exit;
}

class SMS_AJAX {
    public function __construct() {
        add_action('wp_ajax_sms_save_profile', array($this, 'save_profile'));
        add_action('wp_ajax_sms_save_user', array($this, 'save_user'));
        add_action('wp_ajax_sms_filter_users', array($this, 'filter_users'));
        add_action('wp_ajax_sms_import_users', array($this, 'import_users'));
        add_action('wp_ajax_sms_save_student', array($this, 'save_student'));
        add_action('wp_ajax_sms_import_institutions', array($this, 'import_institutions'));
        add_action('wp_ajax_sms_save_institution', array($this, 'save_institution'));
        add_action('wp_ajax_sms_delete_institution', array($this, 'delete_institution'));
        add_action('wp_ajax_sms_toggle_user_status', array($this, 'toggle_user_status'));
        add_action('wp_ajax_sms_submit_lesson', array($this, 'submit_lesson'));
        add_action('wp_ajax_sms_review_prep', array($this, 'review_prep'));
        add_action('wp_ajax_sms_filter_preps', array($this, 'filter_preps'));
        add_action('wp_ajax_sms_fetch_notifications', array($this, 'fetch_notifications'));
        add_action('wp_ajax_sms_mark_read_notification', array($this, 'mark_read_notification'));
        add_action('wp_ajax_sms_mark_all_read_notifications', array($this, 'mark_all_read_notifications'));
    }

    public function fetch_notifications() {
        check_ajax_referer('sms_nonce', 'security');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        $user_id = get_current_user_id();
        $unread_count = SMS_Notifications::get_unread_count($user_id);
        $notifications = SMS_Notifications::get_user_notifications($user_id);

        ob_start();
        if (empty($notifications)) {
            echo '<div style="padding: 24px; text-align: center; color: var(--sms-text-muted); font-size: 0.85rem;">لا توجد إشعارات حالياً.</div>';
        } else {
            foreach ($notifications as $n) {
                $priority_class = ($n['priority'] === 'action_required') ? 'sms-badge-expired' : (($n['priority'] === 'important') ? 'sms-badge-inactive' : 'sms-badge-info');
                $unread_style   = !$n['is_read'] ? 'background: #f8fafc; font-weight: 700;' : '';
                ?>
                <div class="sms-notification-item" data-id="<?php echo esc_attr($n['id']); ?>" style="padding: 12px 16px; border-bottom: 1px solid var(--sms-border-color); display: flex; flex-direction: column; gap: 4px; <?php echo esc_attr($unread_style); ?>">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="sms-badge <?php echo esc_attr($priority_class); ?>"><?php echo esc_html($n['type']); ?></span>
                        <span style="font-size: 0.72rem; color: var(--sms-text-muted);"><?php echo esc_html(human_time_diff(strtotime($n['created_at']), current_time('timestamp'))); ?></span>
                    </div>
                    <div style="font-size: 0.88rem; color: var(--sms-text-primary);"><?php echo esc_html($n['title']); ?></div>
                    <?php if (!empty($n['message'])): ?>
                        <div style="font-size: 0.8rem; color: var(--sms-text-secondary);"><?php echo esc_html($n['message']); ?></div>
                    <?php endif; ?>
                    <?php if (!$n['is_read']): ?>
                        <div style="text-align: left; margin-top: 4px;">
                            <button type="button" class="sms-btn-mark-read" data-id="<?php echo esc_attr($n['id']); ?>" style="background:none; border:none; color: var(--sms-text-muted); font-size: 0.75rem; cursor: pointer; text-decoration: underline;">تحديد كتمت القراءة</button>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
            }
        }
        $html = ob_get_clean();

        wp_send_json_success(array(
            'count' => $unread_count,
            'html'  => $html
        ));
    }

    public function mark_read_notification() {
        check_ajax_referer('sms_nonce', 'security');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $user_id = get_current_user_id();

        SMS_Notifications::mark_as_read($id, $user_id);
        wp_send_json_success(array('message' => 'تم تحديث الإشعار'));
    }

    public function mark_all_read_notifications() {
        check_ajax_referer('sms_nonce', 'security');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        $user_id = get_current_user_id();
        SMS_Notifications::mark_all_as_read($user_id);
        wp_send_json_success(array('message' => 'تم تحديد جميع الإشعارات كمقروءة'));
    }

    public function submit_lesson() {
        check_ajax_referer('sms_nonce', 'security');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        $user_id = get_current_user_id();
        $lesson_title = isset($_POST['lesson_title']) ? sanitize_text_field($_POST['lesson_title']) : '';
        $file = isset($_FILES['pdf_file']) ? $_FILES['pdf_file'] : array();

        $result = SMS_Lessons::submit_lesson_prep($user_id, $lesson_title, $file);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        $teacher = get_userdata($user_id);
        $t_name  = $teacher ? $teacher->display_name : 'المعلم';
        $inst_id = SMS_Users::get_user_institutions($user_id)[0] ?? 0;

        // Trigger Notification to Reviewers
        SMS_Notifications::notify_roles(array('sms_coordinator', 'sms_dept_head', 'sms_administrator', 'administrator'), 'تحضير درس', "تقديم تحضير درس جديد من $t_name", "درس: $lesson_title", '?tab=lessons', 'important', $inst_id);

        wp_send_json_success(array('message' => 'تم إرسال تحضير الدرس بنجاح'));
    }

    public function review_prep() {
        check_ajax_referer('sms_nonce', 'security');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        $prep_id  = isset($_POST['prep_id']) ? intval($_POST['prep_id']) : 0;
        $status   = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'approved';
        $user_id  = get_current_user_id();

        SMS_Lessons::review_prep($prep_id, $user_id, $status);

        global $wpdb;
        $table = $wpdb->prefix . 'sms_lesson_preparations';
        $prep = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $prep_id), ARRAY_A);

        if ($prep) {
            $status_ar = ($status === 'approved') ? 'اعتماد' : 'رفض';
            SMS_Notifications::create_notification($prep['teacher_id'], 'مراجعة التحضير', "تم $status_ar تحضير الدرس الخاص بك: {$prep['lesson_title']}", '', '?tab=lessons', 'important');
        }

        wp_send_json_success(array('message' => 'تم تحديث حالة اعتماد التحضير بنجاح'));
    }

    public function filter_preps() {
        check_ajax_referer('sms_nonce', 'security');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        $search        = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $status_filter = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $inst_filter   = isset($_POST['institution']) ? intval($_POST['institution']) : 0;
        $user_id       = get_current_user_id();

        $preps = SMS_Lessons::get_reviewer_preparations($user_id, $search, $status_filter, $inst_filter);

        ob_start();
        if (empty($preps)) {
            echo '<div class="sms-card" style="grid-column: 1 / -1; text-align: center; color: var(--sms-text-muted);">لا توجد تحضيرات متطابقة مع البحث.</div>';
        } else {
            foreach ($preps as $prep) {
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
                <?php
            }
        }
        $html = ob_get_clean();
        wp_send_json_success(array('html' => $html));
    }

    public function import_users() {
        check_ajax_referer('sms_nonce', 'security');

        if (!current_user_can('manage_sms_users') && !SMS_Auth::is_admin_user()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        if (empty($_FILES['csv_file']['tmp_name'])) {
            wp_send_json_error(array('message' => 'يرجى اختيار ملف CSV للمستخدمين'));
        }

        $res = SMS_Export_Import::import_users_csv($_FILES['csv_file']['tmp_name']);
        if ($res['success']) {
            $msg = "تم استيراد {$res['success_count']} مستخدم بنجاح.";
            if ($res['error_count'] > 0) {
                $msg .= " تعذر استيراد {$res['error_count']} سجل.";
            }
            wp_send_json_success(array('message' => $msg));
        } else {
            wp_send_json_error(array('message' => $res['message']));
        }
    }

    public function filter_users() {
        check_ajax_referer('sms_nonce', 'security');

        if (!current_user_can('manage_sms_users') && !SMS_Auth::is_admin_user()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        $search      = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $role        = isset($_POST['role']) ? sanitize_text_field($_POST['role']) : '';
        $institution = isset($_POST['institution']) ? intval($_POST['institution']) : 0;
        $status      = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $sort        = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'newest';

        $args = array('number' => 100);

        if ($sort === 'oldest') {
            $args['orderby'] = 'user_registered';
            $args['order']   = 'ASC';
        } elseif ($sort === 'name') {
            $args['orderby'] = 'display_name';
            $args['order']   = 'ASC';
        } else { // default 'newest'
            $args['orderby'] = 'user_registered';
            $args['order']   = 'DESC';
        }

        if (!empty($role)) {
            $args['role'] = $role;
        }

        if (!empty($search)) {
            $args['search'] = '*' . $search . '*';
            $args['search_columns'] = array('user_login', 'user_email', 'display_name');
        }

        $viewer_id = get_current_user_id();
        $users = SMS_Users::get_scoped_users($viewer_id, $args);

        $roles_config = SMS_Roles::get_roles_config();
        $institutions = SMS_Institutions::get_all();

        ob_start();
        if (empty($users)) {
            echo '<div class="sms-card" style="grid-column: 1 / -1; text-align: center; color: var(--sms-text-muted);">لا توجد نتائج متطابقة مع معايير البحث والتصفية.</div>';
        } else {
            foreach ($users as $u) {
                $u_status = SMS_Auth::get_user_status($u->ID);
                if (!empty($status) && $u_status !== $status) {
                    continue;
                }

                $u_inst_ids = SMS_Users::get_user_institutions($u->ID);
                if ($institution > 0 && !in_array($institution, $u_inst_ids)) {
                    continue;
                }

                $badge_class = 'sms-badge-' . $u_status;
                $status_label = ($u_status === 'active') ? 'نشط' : (($u_status === 'expired') ? 'منتهي الصلاحية' : 'غير نشط');
                $user_roles = (array)$u->roles;
                $role_label = !empty($user_roles) && isset($roles_config[$user_roles[0]]) ? $roles_config[$user_roles[0]]['name'] : 'مستخدم';
                $validity = get_user_meta($u->ID, 'sms_membership_validity', true);
                $mem_num = get_user_meta($u->ID, 'sms_membership_number', true);
                $avatar_url = get_user_meta($u->ID, 'sms_avatar_url', true);

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
                        <div class="sms-user-card-avatar" style="overflow:hidden;">
                            <?php if ($avatar_url): ?>
                                <img src="<?php echo esc_url($avatar_url); ?>" style="width:100%; height:100%; object-fit:cover;" />
                            <?php else: ?>
                                <?php echo esc_html(mb_substr($u->display_name, 0, 1, 'UTF-8')); ?>
                            <?php endif; ?>
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
                        <button type="button" class="sms-btn sms-btn-outline sms-btn-toggle-status" data-id="<?php echo esc_attr($u->ID); ?>" data-status="<?php echo esc_attr($u_status); ?>" style="height: 32px; padding: 0 10px; font-size: 0.8rem; flex: 1;">تغيير الحالة</button>
                    </div>
                </div>
                <?php
            }
        }
        $html = ob_get_clean();
        wp_send_json_success(array('html' => $html));
    }

    public function save_profile() {
        check_ajax_referer('sms_nonce', 'security');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        $user_id = get_current_user_id();
        $is_admin = SMS_Auth::is_admin_user();

        // Profile Picture Avatar Upload Handler
        if (!empty($_FILES['avatar_file']['tmp_name'])) {
            $file = $_FILES['avatar_file'];
            if ($file['size'] > 3 * 1024 * 1024) {
                wp_send_json_error(array('message' => 'حجم الصورة يتجاوز الحد الأقصى المسموح (3 ميجابايت)'));
            }

            require_once(ABSPATH . 'wp-admin/includes/file.php');
            $upload = wp_handle_upload($file, array('test_form' => false));
            if ($upload && !isset($upload['error'])) {
                update_user_meta($user_id, 'sms_avatar_url', esc_url_raw($upload['url']));
            }
        }

        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name  = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $email      = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

        if (!empty($email)) {
            wp_update_user(array('ID' => $user_id, 'user_email' => $email));
        }

        update_user_meta($user_id, 'first_name', $first_name);
        update_user_meta($user_id, 'last_name', $last_name);

        if (isset($_POST['gender'])) {
            update_user_meta($user_id, 'sms_gender', sanitize_text_field($_POST['gender']));
        }
        if (isset($_POST['nationality'])) {
            update_user_meta($user_id, 'sms_nationality', sanitize_text_field($_POST['nationality']));
        }
        if (isset($_POST['country'])) {
            update_user_meta($user_id, 'sms_country', sanitize_text_field($_POST['country']));
        }
        if (isset($_POST['job_position'])) {
            update_user_meta($user_id, 'sms_job_position', sanitize_text_field($_POST['job_position']));
        }

        // Institution mapping
        if (isset($_POST['institution_id'])) {
            $inst_id = intval($_POST['institution_id']);
            SMS_Users::update_user_institutions($user_id, array($inst_id));
        }

        // Admin-only editable membership fields
        if ($is_admin) {
            if (isset($_POST['membership_number'])) {
                update_user_meta($user_id, 'sms_membership_number', sanitize_text_field($_POST['membership_number']));
            }
            if (isset($_POST['membership_validity'])) {
                update_user_meta($user_id, 'sms_membership_validity', sanitize_text_field($_POST['membership_validity']));
            }
        }

        // Password change if provided
        if (!empty($_POST['password'])) {
            if (isset($_POST['password_confirm']) && $_POST['password'] === $_POST['password_confirm']) {
                wp_set_password($_POST['password'], $user_id);
            } else {
                wp_send_json_error(array('message' => 'كلمتا المرور غير متطابقتين'));
            }
        }

        // Student metadata if applicable
        if (isset($_POST['grade'])) {
            update_user_meta($user_id, 'sms_grade', sanitize_text_field($_POST['grade']));
            update_user_meta($user_id, 'sms_class_section', sanitize_text_field($_POST['class_section']));
            update_user_meta($user_id, 'sms_parent_phone', sanitize_text_field($_POST['parent_phone']));
            update_user_meta($user_id, 'sms_parent_email', sanitize_email($_POST['parent_email']));
            update_user_meta($user_id, 'sms_health_status', sanitize_textarea_field($_POST['health_status']));
            update_user_meta($user_id, 'sms_preferred_sports', sanitize_textarea_field($_POST['preferred_sports']));
        }

        wp_send_json_success(array('message' => 'تم حفظ البيانات بنجاح'));
    }

    public function save_user() {
        check_ajax_referer('sms_nonce', 'security');

        if (!current_user_can('manage_sms_users') && !SMS_Auth::is_admin_user()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        $user_id    = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $username   = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
        $email      = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name  = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $role       = isset($_POST['role']) ? sanitize_text_field($_POST['role']) : 'sms_teacher';
        $password   = isset($_POST['password']) ? $_POST['password'] : '';
        $inst_ids   = isset($_POST['institution_ids']) ? array_map('intval', (array)$_POST['institution_ids']) : array();

        if ($user_id === 0) {
            if (empty($username) || empty($email) || empty($password)) {
                wp_send_json_error(array('message' => 'يرجى ملء جميع الحقول المطلوبة (اسم المستخدم، البريد، كلمة المرور)'));
            }
            $user_id = wp_create_user($username, $password, $email);
            if (is_wp_error($user_id)) {
                wp_send_json_error(array('message' => $user_id->get_error_message()));
            }
        } else {
            if (!empty($password)) {
                wp_set_password($password, $user_id);
            }
            if (!empty($email)) {
                wp_update_user(array('ID' => $user_id, 'user_email' => $email));
            }
        }

        $user = new WP_User($user_id);
        $user->set_role($role);

        wp_update_user(array(
            'ID'           => $user_id,
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'display_name' => $first_name . ' ' . $last_name
        ));

        if (isset($_POST['membership_number'])) {
            update_user_meta($user_id, 'sms_membership_number', sanitize_text_field($_POST['membership_number']));
        }
        if (isset($_POST['membership_validity'])) {
            update_user_meta($user_id, 'sms_membership_validity', sanitize_text_field($_POST['membership_validity']));
        }

        SMS_Users::update_user_institutions($user_id, $inst_ids);

        // Notify Admins
        SMS_Notifications::notify_roles(array('administrator', 'sms_administrator', 'sms_general_manager'), 'مستخدم جديد', "تم إنشاء حساب جديد: $username", "الدور: $role", '?tab=users');

        wp_send_json_success(array('message' => 'تم حفظ المستخدم بنجاح'));
    }

    public function save_student() {
        check_ajax_referer('sms_nonce', 'security');

        if (!current_user_can('manage_sms_students') && !SMS_Auth::is_admin_user()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        $id = SMS_Students::create_or_update_student($_POST);
        wp_send_json_success(array('id' => $id, 'message' => 'تم حفظ بيانات الطالب بنجاح'));
    }

    public function import_institutions() {
        check_ajax_referer('sms_nonce', 'security');

        if (!SMS_Auth::is_admin_user()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        if (empty($_FILES['csv_file']['tmp_name'])) {
            wp_send_json_error(array('message' => 'يرجى اختيار ملف CSV'));
        }

        $imported = SMS_Export_Import::import_institutions_csv($_FILES['csv_file']['tmp_name']);
        if ($imported !== false) {
            wp_send_json_success(array('message' => "تم استيراد $imported مؤسسة بنجاح"));
        } else {
            wp_send_json_error(array('message' => 'حدث خطأ أثناء الاستيراد'));
        }
    }

    public function save_institution() {
        check_ajax_referer('sms_nonce', 'security');

        if (!current_user_can('manage_sms_institutions') && !SMS_Auth::is_admin_user()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        $id = SMS_Institutions::create_or_update($_POST);
        wp_send_json_success(array('id' => $id, 'message' => 'تم حفظ المؤسسة بنجاح'));
    }

    public function delete_institution() {
        check_ajax_referer('sms_nonce', 'security');

        if (!current_user_can('manage_sms_institutions') && !SMS_Auth::is_admin_user()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id > 0) {
            SMS_Institutions::delete($id);
            wp_send_json_success(array('message' => 'تم حذف المؤسسة بنجاح'));
        }

        wp_send_json_error(array('message' => 'خطأ في عملية الحذف'));
    }

    public function toggle_user_status() {
        check_ajax_referer('sms_nonce', 'security');

        if (!current_user_can('manage_sms_users') && !SMS_Auth::is_admin_user()) {
            wp_send_json_error(array('message' => 'غير مصرح'));
        }

        $user_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $status  = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'active';

        $new_status = ($status === 'active') ? 'inactive' : 'active';
        update_user_meta($user_id, 'sms_account_status', $new_status);

        wp_send_json_success(array('new_status' => $new_status, 'message' => 'تم تغيير حالة المستخدم بنجاح'));
    }
}
