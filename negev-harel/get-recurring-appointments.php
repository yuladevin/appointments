<?
function get_recurring_appointments()
{
    global $wpdb;
    $table = $wpdb->prefix . 'harel_recurring_appoinments';

    return $wpdb->get_results( "SELECT * FROM $table" );
}

function get_recurring_appointment($day, $hour, $minute)
{
    global $wpdb;
    $table = $wpdb->prefix . 'harel_recurring_appoinments';

    return $wpdb->get_results(
"SELECT * FROM $table
WHERE   day_of_week = $day AND
        hour = $hour AND
        minute = $minute AND
        delete_date IS NULL" );
}

function get_recurring_appointment_by_date($date)
{
    $day = idate('w', $date);
    $hour = idate('H', $date);
    $minute = idate('i', $date);
    return get_recurring_appointment($day, $hour, $minute);
}
