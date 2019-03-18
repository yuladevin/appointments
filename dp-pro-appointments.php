<?php
/*
Plugin Name: DP Pro Appointments
Description: Allows users to set appointment from the frontend with a modern design and manage them though the backend.
Version: 1.0.5
Author: Diego Pereyra
Author URI: http://www.wpsleek.com/
Wordpress version supported: 3.5 and above
 */
@error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT);

//on activation
//defined global variables and constants here

global $dpProAppointments, $dpProAppointments_cache, $table_prefix, $wpdb, $appointment_service_meta;
$dpProAppointments = get_option('dpProAppointments_options');
$dpProAppointments_cache = get_option('dpProAppointments_cache');
$appointment_service_meta = get_option('appointment_service_meta');

define("DP_APPOINTMENTS_VER", "1.0.5.6", false); // not related to plugin - managed by negev harel
if (!defined('DP_APPOINTMENTS_PLUGIN_BASENAME')) {
    define('DP_APPOINTMENTS_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

if (!defined('DP_APPOINTMENTS_CSS_DIR')) {
    define('DP_APPOINTMENTS_CSS_DIR', WP_PLUGIN_DIR . '/' . str_replace(basename(__FILE__), "", plugin_basename(__FILE__)) . '/css/');
}

function dpProAppointments_load_textdomain()
{
// Create Text Domain For Translations
    $domain = 'dpProAppointments';
    $locale = apply_filters('plugin_locale', get_locale(), $domain);

    load_textdomain($domain, trailingslashit(WP_LANG_DIR) . 'plugins/dp-pro-appointments' . '-' . $locale . '.mo');
    load_plugin_textdomain('dpProAppointments', false, dirname(plugin_basename(__FILE__)) . '/languages/');

}

add_action('plugins_loaded', 'dpProAppointments_load_textdomain');

function checkMU_install_dpProAppointments($network_wide)
{
    global $wpdb;
    if ($network_wide) {
        $blog_list = get_blog_list(0, 'all');
        foreach ($blog_list as $blog) {
            switch_to_blog($blog['blog_id']);
            install_dpProAppointments();
        }
        switch_to_blog($wpdb->blogid);
    } else {
        install_dpProAppointments();
    }
}

function install_dpProAppointments()
{
    global $wpdb, $table_prefix;

    $default_events = array();
    $default_events = array(
        'version' => DP_APPOINTMENTS_VER,
        'working_days' => array(
            'Sunday' => array(
                'work' => 0,
                'start' => '08:00',
                'end' => '20:00',
            ),
            'Monday' => array(
                'work' => 1,
                'start' => '08:00',
                'end' => '20:00',
            ),
            'Tuesday' => array(
                'work' => 1,
                'start' => '08:00',
                'end' => '20:00',
            ),
            'Wednesday' => array(
                'work' => 1,
                'start' => '08:00',
                'end' => '20:00',
            ),
            'Thursday' => array(
                'work' => 1,
                'start' => '08:00',
                'end' => '20:00',
            ),
            'Friday' => array(
                'work' => 1,
                'start' => '08:00',
                'end' => '20:00',
            ),
            'Saturday' => array(
                'work' => 0,
                'start' => '08:00',
                'end' => '20:00',
            ),
        ),
    );

    $dpProAppointments = get_option('dpProAppointments_options');

    if (!$dpProAppointments) {
        $dpProAppointments = array();
    }

    foreach ($default_events as $key => $value) {
        if (!isset($dpProAppointments[$key])) {
            $dpProAppointments[$key] = $value;
        }
    }

    delete_option('dpProAppointments_options');
    update_option('dpProAppointments_options', $dpProAppointments);
}
register_activation_hook(__FILE__, 'checkMU_install_dpProAppointments');

/* Uninstall */
function checkMU_uninstall_dpProAppointments($network_wide)
{
    global $wpdb;
    if ($network_wide) {
        $blog_list = get_blog_list(0, 'all');
        foreach ($blog_list as $blog) {
            switch_to_blog($blog['blog_id']);
            uninstall_dpProAppointments();
        }
        switch_to_blog($wpdb->blogid);
    } else {
        uninstall_dpProAppointments();
    }
}

function uninstall_dpProAppointments()
{
    global $wpdb, $table_prefix;
    delete_option('dpProAppointments_options');

}
register_uninstall_hook(__FILE__, 'checkMU_uninstall_dpProAppointments');

/* Add new Blog */

add_action('wpmu_new_blog', 'newBlog_dpProAppointments', 10, 6);

function newBlog_dpProAppointments($blog_id, $user_id, $domain, $path, $site_id, $meta)
{
    global $wpdb;

    if (is_plugin_active_for_network('dpProAppointments/dpProAppointments.php')) {
        $old_blog = $wpdb->blogid;
        switch_to_blog($blog_id);
        install_dpProAppointments();
        switch_to_blog($old_blog);
    }
}

require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/includes/core.php';
require_once dirname(__FILE__) . '/settings/settings.php';

require_once dirname(__FILE__) . '/classes/class-gateway-paypal.php';
require_once dirname(__FILE__) . '/classes/class-gateway-stripe.php';

$paypal = new Pro_Appointments_Gateway_Stripe();
$stripe = new Pro_Appointments_Gateway_PayPal();

/*******************/
/* UPDATES
/*******************/
