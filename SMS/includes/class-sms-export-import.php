<?php
if (!defined('ABSPATH')) {
    exit;
}

class SMS_Export_Import {
    public static function export_institutions_csv() {
        if (!SMS_Auth::is_admin_user()) {
            wp_die('Unauthorized');
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=institutions_export_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, array('ID', 'الاسم', 'الكود', 'النوع', 'المدينة', 'العنوان', 'الهاتف'));

        $institutions = SMS_Institutions::get_all();
        foreach ($institutions as $inst) {
            fputcsv($output, array(
                $inst['id'],
                $inst['name'],
                $inst['code'],
                $inst['type'],
                $inst['city'],
                $inst['address'],
                $inst['phone']
            ));
        }
        fclose($output);
        exit;
    }

    public static function import_institutions_csv($file_path) {
        if (!file_exists($file_path)) return false;

        $handle = fopen($file_path, 'r');
        if (!$handle) return false;

        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle);
        $imported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row[1])) continue; // Name required

            SMS_Institutions::create_or_update(array(
                'name'    => $row[1],
                'code'    => isset($row[2]) ? $row[2] : '',
                'type'    => isset($row[3]) ? $row[3] : 'school',
                'city'    => isset($row[4]) ? $row[4] : '',
                'address' => isset($row[5]) ? $row[5] : '',
                'phone'   => isset($row[6]) ? $row[6] : ''
            ));
            $imported++;
        }
        fclose($handle);
        return $imported;
    }
}
