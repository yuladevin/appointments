<?php
require_once dirname(__FILE__) . '/../../../../wp-load.php';

if (!is_user_logged_in() || !current_user_can('edit_others_posts')) {
    die();
}

$args = array(
    'posts_per_page' => -1,
    'post_type' => 'pro-appointments',
    'post_status' => 'any',
    'meta_query' => get_meta_query_between_dates(),
);

header('Content-type: text/csv;  charset=UTF-8');
header('Content-Disposition: attachment; filename="דוח-חדרים.csv"');
header('Pragma: no-cache');
header('Expires: 0');

$file = fopen('php://output', 'w');

fputcsv($file, [
    'מזהה',
    'חדר',
    'שם',
    'תאריך',
    // 'מצב',
]);

$appointments = get_posts($args);

if ($appointments) {
    $rows = [];
    foreach ($appointments as $appointment) {
        $cols = [
            $appointment->ID,
            wp_get_post_terms($appointment->ID, 'pro_appointments_service')[0]->name,
            get_post_meta($appointment->ID, 'appointment_name', true),
            get_post_meta($appointment->ID, 'appointment_date', true),
            // $appointment->post_status,
        ];

        $rows[] = $cols;
    }
    usort($rows, function ($a, $b) {
        return $a[3] > $b[3];
    });
    foreach ($rows as $row) {
        $row[3] = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $row[3]);
        fputcsv($file, $row);
    }
}
