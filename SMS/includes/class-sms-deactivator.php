<?php
if (!defined('ABSPATH')) {
    exit;
}

class SMS_Deactivator {
    public static function deactivate() {
        // Do NOT delete custom DB tables or user data to avoid data loss.
        flush_rewrite_rules();
    }
}
