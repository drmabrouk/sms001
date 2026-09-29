<?php
if (!defined('ABSPATH')) {
    exit;
}

class SMS_Institutions {
    public static function create_or_update($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'sms_institutions';

        $id      = isset($data['id']) ? intval($data['id']) : 0;
        $name    = sanitize_text_field($data['name']);
        $code    = sanitize_text_field($data['code']);
        $type    = sanitize_text_field($data['type']);
        $city    = sanitize_text_field($data['city']);
        $address = sanitize_textarea_field($data['address']);
        $phone   = sanitize_text_field($data['phone']);

        $record = array(
            'name'    => $name,
            'code'    => $code,
            'type'    => $type,
            'city'    => $city,
            'address' => $address,
            'phone'   => $phone
        );

        if ($id > 0) {
            $wpdb->update($table, $record, array('id' => $id));
            return $id;
        } else {
            $wpdb->insert($table, $record);
            return $wpdb->insert_id;
        }
    }

    public static function delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'sms_institutions';
        return $wpdb->delete($table, array('id' => intval($id)), array('%d'));
    }

    public static function get_all() {
        global $wpdb;
        $table = $wpdb->prefix . 'sms_institutions';
        return $wpdb->get_results("SELECT * FROM $table ORDER BY name ASC", ARRAY_A);
    }
}
