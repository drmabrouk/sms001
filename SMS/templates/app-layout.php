<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
$current_user = wp_get_current_user();
$display_name = $current_user->exists() ? $current_user->display_name : 'زائر';
$roles = (array) $current_user->roles;
$role_names = SMS_Roles::get_roles_config();
$primary_role_key = !empty($roles) ? $roles[0] : '';
$primary_role_label = isset($role_names[$primary_role_key]) ? $role_names[$primary_role_key]['name'] : 'مستخدم';
$job_title = get_user_meta($current_user->ID, 'sms_job_position', true);
$unread_count = SMS_Notifications::get_unread_count($current_user->ID);
?>
<div class="sms-app-wrapper">
    <!-- Toast Notifications Container -->
    <div id="sms-toast-container"></div>

    <!-- Mobile Top Header Bar -->
    <div class="sms-mobile-nav-toggle">
        <div style="display: flex; align-items: center; gap: 8px;">
            <div class="sms-brand-icon" style="width:28px; height:28px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
            </div>
            <strong style="font-size: 0.88rem;">نظام الإدارة الرياضية</strong>
        </div>
        <div style="display:flex; gap:6px; align-items:center;">
            <button type="button" class="sms-btn sms-btn-outline sms-notif-bell-btn" style="height:32px; padding:0 8px; position:relative;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span class="sms-notif-badge" style="<?php echo $unread_count > 0 ? '' : 'display:none;'; ?>"><?php echo esc_html($unread_count); ?></span>
            </button>
            <button type="button" class="sms-btn sms-btn-outline" id="sms-mobile-toggle-btn" style="height:32px; padding:0 10px; font-size:0.78rem;">
                القائمة ☰
            </button>
        </div>
    </div>

    <!-- Fixed Global Right Sidebar Navigation -->
    <aside class="sms-sidebar" id="sms-global-sidebar">
        <div>
            <div class="sms-sidebar-brand" style="flex-direction:column; align-items:flex-start; gap:8px;">
                <div style="display:flex; align-items:center; justify-content:space-between; width:100%;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div class="sms-brand-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                        </div>
                        <h1 class="sms-brand-title">نظام الإدارة الرياضية</h1>
                    </div>
                    <button type="button" class="sms-logout-btn sms-notif-bell-btn" title="الإشعارات" style="position:relative;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        <span class="sms-notif-badge" style="<?php echo $unread_count > 0 ? '' : 'display:none;'; ?>"><?php echo esc_html($unread_count); ?></span>
                    </button>
                </div>
                <div style="font-size:0.72rem; color:var(--sms-text-muted); line-height:1.2; padding-right:2px;">المنصة المركزية الشاملة للإدارة الرياضية بالمؤسسات التعليمية</div>
            </div>

            <nav class="sms-sidebar-nav">
                <ul class="sms-nav-list">
                    <li class="sms-nav-item <?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>">
                        <a href="?tab=dashboard">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                            <span>لوحة المعلومات</span>
                        </a>
                    </li>
                    <li class="sms-nav-item <?php echo $current_tab === 'reports' ? 'active' : ''; ?>">
                        <a href="?tab=reports">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                            <span>إدارة التقارير</span>
                        </a>
                    </li>
                    <li class="sms-nav-item <?php echo $current_tab === 'lessons' ? 'active' : ''; ?>">
                        <a href="?tab=lessons">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            <span>تحضير الدروس</span>
                        </a>
                    </li>
                    <li class="sms-nav-item <?php echo $current_tab === 'plans' ? 'active' : ''; ?>">
                        <a href="?tab=plans">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <span>الخطط الفصلية</span>
                        </a>
                    </li>
                    <li class="sms-nav-item <?php echo $current_tab === 'institutions' ? 'active' : ''; ?>">
                        <a href="?tab=institutions">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4 8 4v14"/><path d="M9 18h6"/><path d="M10 12h4"/></svg>
                            <span>إدارة المؤسسات</span>
                        </a>
                    </li>
                    <li class="sms-nav-item <?php echo $current_tab === 'users' ? 'active' : ''; ?>">
                        <a href="?tab=users">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <span>إدارة مستخدمي النظام</span>
                        </a>
                    </li>
                    <li class="sms-nav-item <?php echo $current_tab === 'student_affairs' ? 'active' : ''; ?>">
                        <a href="?tab=student_affairs">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                            <span>إدارة شؤون الطلاب</span>
                        </a>
                    </li>
                    <li class="sms-nav-item <?php echo $current_tab === 'tournaments' ? 'active' : ''; ?>">
                        <a href="?tab=tournaments">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2z"/></svg>
                            <span>إدارة البطولات</span>
                        </a>
                    </li>
                    <li class="sms-nav-item <?php echo $current_tab === 'settings' ? 'active' : ''; ?>">
                        <a href="?tab=settings">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                            <span>إعدادات النظام</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Sidebar User Profile Footer -->
        <div class="sms-sidebar-footer">
            <div class="sms-user-profile-card" id="sms-profile-trigger" data-tab="profile">
                <div class="sms-user-avatar">
                    <?php echo esc_html(mb_substr($display_name, 0, 1, 'UTF-8')); ?>
                </div>
                <div class="sms-user-info">
                    <div class="sms-user-name"><?php echo esc_html($display_name); ?></div>
                    <div class="sms-user-job-title"><?php echo esc_html($job_title ? $job_title : $primary_role_label); ?></div>
                </div>
                <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="sms-logout-btn" title="تسجيل الخروج">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- Slide-over Notification Drawer -->
    <div class="sms-notif-drawer-backdrop" id="sms-notif-drawer-backdrop">
        <div class="sms-notif-drawer">
            <div class="sms-notif-drawer-header">
                <h3 style="margin:0; font-size:1.05rem; font-weight:800;">مركز الإشعارات</h3>
                <div style="display:flex; gap:8px; align-items:center;">
                    <button type="button" id="sms-btn-mark-all-read" style="background:none; border:none; color:var(--sms-text-secondary); font-size:0.75rem; cursor:pointer; font-weight:700;">تحديد الكل كمقروء</button>
                    <button type="button" id="sms-notif-drawer-close" style="background:none; border:none; font-size:1.3rem; cursor:pointer; color:var(--sms-text-muted);">&times;</button>
                </div>
            </div>
            <div id="sms-notif-list-container" style="overflow-y:auto; flex:1;">
                <!-- Populated via AJAX -->
            </div>
        </div>
    </div>

    <!-- Open Main Content Area -->
    <main class="sms-main-content">
        <?php
        switch ($current_tab) {
            case 'reports':
                include SMS_PLUGIN_DIR . 'templates/parts/tab-reports.php';
                break;
            case 'lessons':
                include SMS_PLUGIN_DIR . 'templates/parts/tab-lessons.php';
                break;
            case 'plans':
                include SMS_PLUGIN_DIR . 'templates/parts/tab-plans.php';
                break;
            case 'institutions':
                include SMS_PLUGIN_DIR . 'templates/parts/tab-institutions.php';
                break;
            case 'users':
                include SMS_PLUGIN_DIR . 'templates/parts/tab-users.php';
                break;
            case 'student_affairs':
                include SMS_PLUGIN_DIR . 'templates/parts/tab-student-affairs.php';
                break;
            case 'tournaments':
                include SMS_PLUGIN_DIR . 'templates/parts/tab-tournaments.php';
                break;
            case 'profile':
                include SMS_PLUGIN_DIR . 'templates/parts/tab-profile.php';
                break;
            case 'settings':
                include SMS_PLUGIN_DIR . 'templates/parts/tab-settings.php';
                break;
            case 'dashboard':
            default:
                include SMS_PLUGIN_DIR . 'templates/parts/tab-dashboard.php';
                break;
        }
        ?>
    </main>
</div>
