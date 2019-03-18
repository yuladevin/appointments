<?php

function remove_recurring_appointment($id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'harel_recurring_appoinments';
    $wpdb->update($table, ['delete_date' => current_time('mysql')], ['id' => $id]);
}
function ajax_remove_recurring_appointment()
{
    if (!has_keys($_POST, ['recurring_id'])) {
        return wp_die();
    }
    remove_recurring_appointment($_POST['recurring_id']);

}
add_action('wp_ajax_nopriv_remove_recurring_appointment', 'ajax_remove_recurring_appointment');
add_action('wp_ajax_remove_recurring_appointment', 'ajax_remove_recurring_appointment');
