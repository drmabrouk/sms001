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
?>
<div class="sms-app-wrapper">
    <!-- Right Sidebar Navigation -->
    <aside class="sms-sidebar">
        <div>
            <div class="sms-sidebar-brand">
                <div class="sms-brand-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                </div>
                <h1 class="sms-brand-title">نظام إدارة الرياضة</h1>
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
                    <li class="sms-nav-item <?php echo $current_tab === 'settings' ? 'active' : ''; ?>">
                        <a href="?tab=settings">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                            <span>إعدادات النظام</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Sidebar User Profile Area -->
        <div class="sms-sidebar-footer">
            <div class="sms-user-profile-card" id="sms-profile-trigger" data-tab="profile">
                <div class="sms-user-avatar">
                    <?php echo esc_html(mb_substr($display_name, 0, 1, 'UTF-8')); ?>
                </div>
                <div class="sms-user-info">
                    <div class="sms-user-name"><?php echo esc_html($display_name); ?></div>
                    <div class="sms-user-role"><?php echo esc_html($primary_role_label); ?></div>
                </div>
                <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="sms-logout-btn" title="تسجيل الخروج">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="sms-main-content">
        <?php
        switch ($current_tab) {
            case 'reports':
            case 'lessons':
            case 'plans':
                include SMS_PLUGIN_DIR . 'templates/parts/tab-placeholder.php';
                break;
            case 'institutions':
                include SMS_PLUGIN_DIR . 'templates/parts/tab-institutions.php';
                break;
            case 'users':
                include SMS_PLUGIN_DIR . 'templates/parts/tab-users.php';
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
