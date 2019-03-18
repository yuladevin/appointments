<?php

function add_recurring_appointment($name, $date)
{
    global $wpdb;

    $table = $wpdb->prefix . 'harel_recurring_appoinments';

    $data = [
        'name' => $name,
        'day_of_week' => idate('w', $date),
        'hour' => idate('H', $date),
        'minute' => idate('i', $date),
    ];
    $format = ['%s', '%d', '%d', '%d'];
    $wpdb->insert($table, $data, $format);
    $my_id = $wpdb->insert_id;

}
function ajax_add_recurring_appointment()
{
    if (!has_keys($_POST, ['name', 'date'])) {
        return wp_die();
    }
    add_recurring_appointment($_POST['name'], $_POST['date']);

}
add_action('wp_ajax_nopriv_add_recurring_appointment', 'ajax_add_recurring_appointment');
add_action('wp_ajax_add_recurring_appointment', 'ajax_add_recurring_appointment');
