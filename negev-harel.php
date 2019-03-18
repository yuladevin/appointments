<?
require_once __DIR__ . "/negev-harel/util.php";
reqdir('negev-harel', 'php', [
    'exclude'=>'util',
]);

const TIME_FORMAT = 'H:i';

function get_year_from_param()
{
    $yearnum = isset($_GET['yearnum'])
    ? $_GET['yearnum']
    : date('Y');
    return intval($yearnum);
}

function get_month_from_param()
{
    $monthnum = isset($_GET['monthnum'])
    ? $_GET['monthnum']
    : date('n');
    if ($monthnum < 1 && $monthnum > 12) {
        $monthnum = $current_month;
    }
    return intval($monthnum);
}

function get_meta_query_between_dates()
{
    $monthnum = get_month_from_param();
    $yearnum = get_year_from_param();
    $start_timestamp = strtotime(date("$yearnum-$monthnum-1"));
    $end_timestamp = strtotime(date("$yearnum-$monthnum-31"));
    $end_timestamp = strtotime("midnight +1 day", $end_timestamp ) - 1;
    return [
        'relation' => 'AND',
        [
            'key' => 'appointment_date',
            'value' => [$start_timestamp, $end_timestamp],
            'type' => 'numeric',
            'compare' => 'BETWEEN',
        ],
    ];
}

function get_query_params_str_for_export_appointments()
{
    $monthnum = get_month_from_param();
    $yearnum = get_year_from_param();

    return "?monthnum=$monthnum&yearnum=$yearnum";
}

function ajax_get_local_date_format($date)
{
    echo $date;
}

add_action('wp_ajax_nopriv_get_local_date_format', 'ajax_get_local_date_format');
add_action('wp_ajax_get_local_date_format', 'ajax_get_local_date_format');
