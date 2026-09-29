<?php
if (!defined('ABSPATH')) {
    exit;
}

class SMS_Core {
    protected $auth;
    protected $ajax;

    public function __construct() {
        $this->auth = new SMS_Auth();
        $this->ajax = new SMS_AJAX();
    }

    public function run() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_shortcode('sms_dashboard_app', array($this, 'render_app'));
        add_action('template_redirect', array($this, 'handle_export_download'));
        add_filter('body_class', array($this, 'add_standalone_body_class'));
        add_filter('template_include', array($this, 'sms_standalone_template'));
    }

    public function enqueue_assets() {
        if ($this->is_sms_page()) {
            wp_enqueue_style('sms-style', SMS_PLUGIN_URL . 'assets/css/sms-style.css', array(), SMS_VERSION);
            wp_enqueue_script('sms-script', SMS_PLUGIN_URL . 'assets/js/sms-script.js', array('jquery'), SMS_VERSION, true);

            wp_localize_script('sms-script', 'sms_vars', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('sms_nonce'),
                'export_url' => add_query_arg('sms_action', 'export_institutions', home_url('/'))
            ));
        }
    }

    public function is_sms_page() {
        if (is_page('sms-dashboard') || is_page(get_option('sms_dashboard_page_id'))) {
            return true;
        }
        return false;
    }

    public function add_standalone_body_class($classes) {
        if ($this->is_sms_page()) {
            $classes[] = 'sms-standalone-page';
        }
        return $classes;
    }

    public function sms_standalone_template($template) {
        if ($this->is_sms_page()) {
            $custom_template = SMS_PLUGIN_DIR . 'templates/standalone-page.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }

    public function handle_export_download() {
        if (isset($_GET['sms_action']) && $_GET['sms_action'] === 'export_institutions') {
            SMS_Export_Import::export_institutions_csv();
        }
    }

    public function render_app() {
        if (!is_user_logged_in()) {
            return '<div class="sms-card" style="text-align:center; padding:40px;"><p>يرجى تسجيل الدخول للوصول إلى نظام إدارة الرياضة.</p><a href="' . esc_url(wp_login_url(get_permalink())) . '" class="sms-btn sms-btn-dark">تسجيل الدخول</a></div>';
        }

        ob_start();
        include SMS_PLUGIN_DIR . 'templates/app-layout.php';
        return ob_get_clean();
    }
}
