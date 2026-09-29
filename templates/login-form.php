<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background-color: var(--sms-bg-body); padding: 20px;">
    <div style="background: var(--sms-bg-card); border: 1px solid var(--sms-border-color); border-radius: var(--sms-radius-lg); width: 100%; max-width: 420px; padding: 32px; box-shadow: var(--sms-shadow-subtle);">
        <div style="text-align: center; margin-bottom: 24px;">
            <div class="sms-brand-icon" style="width: 50px; height: 50px; margin: 0 auto 12px auto; font-size: 1.2rem;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
            </div>
            <h2 style="font-size: 1.3rem; font-weight: 800; margin: 0 0 6px 0; color: var(--sms-text-primary);">نظام إدارة الرياضة</h2>
            <p style="font-size: 0.85rem; color: var(--sms-text-muted); margin: 0;">تسجيل الدخول إلى لوحة التحكم الموحدة</p>
        </div>

        <form id="sms-app-login-form" method="post" action="<?php echo esc_url(wp_login_url()); ?>">
            <input type="hidden" name="redirect_to" value="<?php echo esc_url(get_permalink(get_option('sms_dashboard_page_id'))); ?>" />
            <div class="sms-floating-field">
                <input type="text" id="login_user" name="log" placeholder=" " required />
                <label for="login_user">اسم المستخدم أو البريد الإلكتروني</label>
            </div>

            <div class="sms-floating-field sms-password-toggle-wrapper">
                <input type="password" id="login_pass" name="pwd" placeholder=" " required />
                <label for="login_pass">كلمة المرور</label>
                <button type="button" class="sms-password-toggle-btn" data-target="login_pass" title="إظهار/إخفاء كلمة المرور">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="sms-btn sms-btn-dark" style="width: 100%;">تسجيل الدخول</button>
            </div>
        </form>
    </div>
</div>
