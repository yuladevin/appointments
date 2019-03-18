<?php
require_once __DIR__ . "/negev-harel.php";
function dpProAppointments_admin_url($query = array())
{
    global $plugin_page;

    if (!isset($query['page'])) {
        $query['page'] = $plugin_page;
    }

    $path = 'admin.php';

    if ($query = build_query($query)) {
        $path .= '?' . $query;
    }

    $url = admin_url($path);

    return esc_url_raw($url);
}

function dpProAppointments_plugin_url($path = '')
{
    global $wp_version;
    if (version_compare($wp_version, '2.8', '<')) { // Using WordPress 2.7
        $folder = dirname(plugin_basename(__FILE__));
        if ('.' != $folder) {
            $path = path_join(ltrim($folder, '/'), $path);
        }

        return plugins_url($path);
    }
    return plugins_url($path, __FILE__);
}

if (!function_exists('mb_substr')) {
    function mb_substr($string, $offset, $length, $encoding = '')
    {
        $arr = preg_split("//u", $string);
        $slice = array_slice($arr, $offset + 1, $length);
        return implode("", $slice);
    }
}

function dpProAppointments_appointments_init()
{
    global $dpProAppointments;

    $labels = array(
        'name' => __('Appointments', 'dpProAppointments'),
        'singular_name' => __('Appointment', 'dpProAppointments'),
        'add_new' => __('Add New', 'dpProAppointments'),
        'add_new_item' => __('Add New Appointment', 'dpProAppointments'),
        'edit_item' => __('Edit Appointment', 'dpProAppointments'),
        'new_item' => __('New Appointment', 'dpProAppointments'),
        'all_items' => __('All Appointments', 'dpProAppointments'),
        'view_item' => __('View Appointment', 'dpProAppointments'),
        'search_items' => __('Search Appointments', 'dpProAppointments'),
        'not_found' => __('No Appointments Found', 'dpProAppointments'),
        'not_found_in_trash' => __('No Appointments Found in Trash', 'dpProAppointments'),
        'parent_item_colon' => '',
        'menu_name' => __('Appointments', 'dpProAppointments'),
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => true,
        'show_ui' => false,
        'show_in_menu' => false,
        'query_var' => true,
        'rewrite' => array('slug' => ($dpProAppointments['events_slug'] != "" ? $dpProAppointments['events_slug'] : 'pro-appointments')),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'show_in_menu' => true,
        'menu_position' => null,
        'supports' => array('custom-fields'),
        'taxonomies' => array('pro_appointments_category', 'post_tag'),
    );

    register_post_type('pro-appointments', $args);
    /*
    $labels = array(
    'name' => __('Services', 'dpProAppointments'),
    'singular_name' => __('Services', 'dpProAppointments'),
    'add_new' => __('Add New', 'dpProAppointments'),
    'add_new_item' => __('Add New Service', 'dpProAppointments'),
    'edit_item' => __('Edit Service', 'dpProAppointments'),
    'new_item' => __('New Service', 'dpProAppointments'),
    'all_items' => __('All Services', 'dpProAppointments'),
    'view_item' => __('View Service', 'dpProAppointments'),
    'search_items' => __('Search Services', 'dpProAppointments'),
    'not_found' =>  __('No Services Found', 'dpProAppointments'),
    'not_found_in_trash' => __('No Services Found in Trash', 'dpProAppointments'),
    'parent_item_colon' => '',
    'menu_name' => __('Services', 'dpProAppointments')
    );

    $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'exclude_from_search' => true,
    'show_ui' => false,
    'show_in_menu' => false,
    'query_var' => true,
    'rewrite' => array( 'slug' => ( $dpProAppointments['events_slug'] != "" ? $dpProAppointments['events_slug'] : 'pro-appointments') ),
    'capability_type' => 'post',
    'has_archive' => true,
    'hierarchical' => false,
    'show_in_menu' => true,
    'menu_position' => null,
    'supports' => array( 'custom-fields' ),
    'taxonomies' => array('pro_appointments_category', 'post_tag')
    );

    register_post_type( 'pro-appointments', $args );*/
    //flush_rewrite_rules();

}
add_action('init', 'dpProAppointments_appointments_init');

add_action('init', 'dpProAppointments_appointments_taxonomies', 0);

add_action('admin_init', 'flush_rewrite_rules');

function dpProAppointments_appointments_taxonomies()
{
    // Add new taxonomy, make it hierarchical (like categories)
    $labels = array(
        'name' => _x('Services', 'taxonomy general name'),
        'singular_name' => _x('Service', 'taxonomy singular name'),
        'search_items' => __('Search Services'),
        'all_items' => __('All Services'),
        'parent_item' => __('Parent Service'),
        'parent_item_colon' => __('Parent Service:'),
        'edit_item' => __('Edit Service'),
        'update_item' => __('Update Service'),
        'add_new_item' => __('Add New Service'),
        'new_item_name' => __('New Service Name'),
        'not_found' => __('No Services Found', 'dpProAppointments'),
        'menu_name' => __('Service'),
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => false,
        'query_var' => true,
        'rewrite' => array('slug' => 'pro_appointments_service'),
    );

    register_taxonomy('pro_appointments_service', array('pro-appointments'), $args);
}

function dpProAppointments_custom_post_status()
{
    register_post_status('completed', array(
        'label' => _x('Completed', 'dpProAppointments'),
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>'),
    ));

    register_post_status('canceled', array(
        'label' => _x('Canceled', 'dpProAppointments'),
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Canceled <span class="count">(%s)</span>', 'Canceled <span class="count">(%s)</span>'),
    ));

    register_post_status('pending_payment', array(
        'label' => _x('Pending Payment', 'dpProAppointments'),
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Pending Payment <span class="count">(%s)</span>', 'Pending Payment <span class="count">(%s)</span>'),
    ));
}
add_action('init', 'dpProAppointments_custom_post_status');

add_action('admin_footer-post.php', 'dpProAppointments_append_post_status_list');
function dpProAppointments_append_post_status_list()
{
    global $post;
    $complete = '';
    $label = '';
    if ($post->post_type == 'pro-appointments') {
        if ($post->post_status == 'completed') {
            $complete = ' selected=\"selected\"';
            $label_completed = '<span id=\"post-status-display\"> ' . __('Completed', 'dpProAppointments') . '</span>';
        }
        if ($post->post_status == 'canceled') {
            $canceled = ' selected=\"selected\"';
            $label_canceled = '<span id=\"post-status-display\"> ' . __('Canceled', 'dpProAppointments') . '</span>';
        }
        if ($post->post_status == 'pending_payment') {
            $canceled = ' selected=\"selected\"';
            $label_canceled = '<span id=\"post-status-display\"> ' . __('Pending Payment', 'dpProAppointments') . '</span>';
        }
        echo '
      <script>
      jQuery(document).ready(function($){
           $("select#post_status").append("<option value=\"completed\" ' . $complete . '>' . __('Completed', 'dpProAppointments') . '</option>");
           $(".misc-pub-section label").append("' . $label_completed . '");

		   $("select#post_status").append("<option value=\"completed\" ' . $canceled . '>' . __('Canceled', 'dpProAppointments') . '</option>");
           $(".misc-pub-section label").append("' . $label_canceled . '");
      });
      </script>
      ';
    }
}

function dpProAppointments_updateNotice()
{
    echo '<div class="updated">
       <p>' . __('Updated Succesfully.', 'dpProAppointments') . '</p>
    </div>';
}

if (@$_GET['settings-updated'] &&
    (strpos($_GET['page'], 'dpProAppointments-') !== false)) {
    add_action('admin_notices', 'dpProAppointments_updateNotice');
}

add_action('wp_ajax_nopriv_getWeeklyAppointment', 'dpProAppointments_getWeekly');
add_action('wp_ajax_getWeeklyAppointment', 'dpProAppointments_getWeekly');

function dpProAppointments_getWeekly()
{

    $nonce = $_POST['postEventsNonce'];
    //if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //    die ( 'Busted!');

    if (!is_numeric($_POST['date'])) {die();}

    global $dpProAppointments;

    $timestamp = $_POST['date'];
    $service = $_POST['service'];
    $tooltip = $_POST['tooltip'];
    $currDate = date("Y-m-d", $timestamp);

    $is_admin = $_POST['is_admin'];

    if ($is_admin && strtolower($is_admin) !== "false") {
        $is_admin = true;
    } else {
        $is_admin = false;
    }

    require_once 'classes/base.class.php';

    $dpProAppointments_class = new DpProAppointments($is_admin, $timestamp, '', array('service' => $service, 'show_tooltip' => $tooltip));

    if ($dpProAppointments['first_day'] == 1) {
        $weekly_first_date = strtotime('last monday', ($timestamp + (24 * 60 * 60)));
        $weekly_last_date = strtotime('next sunday', ($timestamp - (24 * 60 * 60)));
    } else {
        $weekly_first_date = strtotime('last sunday', ($timestamp + (24 * 60 * 60)));
        $weekly_last_date = strtotime('next saturday', ($timestamp - (24 * 60 * 60)));
    }

    $weekly_txt = date_i18n('d F', $weekly_first_date) . ' - ' . date_i18n('d F, Y', $weekly_last_date);

    if (date('m', $weekly_first_date) == date('m', $weekly_last_date)) {

        $weekly_txt = date_i18n('d', $weekly_first_date) . ' - ' . date_i18n('d F, Y', $weekly_last_date);

    }

    if (date('Y', $weekly_first_date) != date('Y', $weekly_last_date)) {

        $weekly_txt = date_i18n(get_option('date_format'), $weekly_first_date) . ' - ' . date_i18n(get_option('date_format'), $weekly_last_date);

    }

    echo "<!--" . $weekly_txt . ">!]-->";

    die($dpProAppointments_class->weeklyCalendarLayout());
}

add_action('wp_ajax_nopriv_getAppointmentForm', 'dpProAppointments_getAppointmentForm');
add_action('wp_ajax_getAppointmentForm', 'dpProAppointments_getAppointmentForm');

function dpProAppointments_getAppointmentForm()
{

    $nonce = $_POST['postEventsNonce'];
    //if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //    die ( 'Busted!');
    //var_dump($_POST['data']);
    $data = json_decode(stripslashes($_POST['data']));

    // here i would like use foreach:

    //var_dump('<pre>'.$timestamp_array.'</pre>');
    //if(!is_numeric($_POST['date'])) { die();   }

    global $dpProAppointments, $current_user, $appointment_service_meta;

    $service = $_POST['service'];
    //var_dump('$service:'.$service);
    $name = "";
    $email = "";
    if (is_user_logged_in()) {

        get_currentuserinfo();

        $name = $current_user->user_firstname . ' ' . $current_user->user_lastname;
        $email = $current_user->user_email;

    }

    $price = $dpProAppointments['price'];
    if ($appointment_service_meta[$service]['price'] > 0) {
        $price = $appointment_service_meta[$service]['price'];
    }

    $block_hours = ($dpProAppointments['block_hours'] > 0 ? $dpProAppointments['block_hours'] : 0);
    $count = count($data);
    $html = '<input type="hidden" id="cout_appointments" name="cout_appointments" value="' . $count . '">';

    //var_dump('$count '.$count);
    foreach ($data as $timestamp) {

        if ($timestamp <= current_time('timestamp') + ($dpProAppointments['block_hours'] * 60 * 60)) {
            $html = '<div class="dp_appointments_form_new_appointment ' . $timestamp . '">' .

            '<p>' . __('This date is not available for booking', 'dpProAppointments') . '</p>

						<div class="pro-appointments-add-footer">
							<button class="dp_appointments_pimary_btn" id="dp_appointments_cancel_appointment">' . __('Cancel', 'dpProAppointments') . '</button>
							<div class="clear"></div>
						</div>

                    </div>';
        } else {

            //echo($timestamp.'&'.$html);

            $html .= '<div class="dp_appointments_form_new_appointment" id="' . $count . '">';
            $count--;
            $html .= '<p>תאריך הפגישה <strong>' . date_i18n(get_option('date_format') . ' H:i', $timestamp) . '</strong></p>';

            // $recurring_text = 'שבועי';
            // $single_text = 'חד פעמי';
            //             $html .= "
            // <select id='recurrence-type'>
            //     <option value='weekly'>$recurring_text</option>
            //     <option value='single'>$single_text</option>
            // </select>
            // ";

            if (is_numeric($price) && $price > 0) {

                $html .= '<p>' . __('Price:', 'dpProAppointments') . ' <strong>' . $price . ' ' . $dpProAppointments['currency'] . '</strong></p>';
            }

            if (!$dpProAppointments['appointment_not_logged'] && !is_user_logged_in()) {

                $html .= '
				<p>' . __('You must be logged in to set an appointment.', 'dpProAppointments') . '</p>

				<div class="clear"></div>
				<div class="pro-appointments-add-footer">
					<button class="dp_appointments_pimary_btn" id="dp_appointments_cancel_appointment">' . __('Cancel', 'dpProAppointments') . '</button>
					<div class="clear"></div>
				</div>';

            } else {

                $html .= '
						<input type="hidden" id="dp_appointments_appointment_date" class="' . $timestamp . '" value="' . $timestamp . '" />';
                $html .= '
						<input type="hidden" id="dp_appointments_appointment_service" class="' . $timestamp . '" value="' . $service . '" />';

                if (is_user_logged_in()) {
                    $html .= '
						<input type="hidden" id="dp_appointments_appointment_userid" class="' . $timestamp . '" value="' . $current_user->ID . '" />';
                } else {
                    $html .= '
						<input type="hidden" id="dp_appointments_appointment_userid" class="' . $timestamp . '" value="" />';
                }
                if ($count == 0) {
                    $html .= '


						<span class="appointments-errors" style="display:none;">' . __('Introduce the required fields', 'dpProAppointments') . '</span>

						<label class="dp_appointments_input_label">' . __('שם המשבץ + סוג הפעילות', 'dpProAppointments') . ' *</label>
						<input type="text" class="dp_appointments_input_text" id="dp_appointments_appointment_name" placeholder="שם הפעילות"/>
						<div class="clear"></div>

						';
                }
                if (is_array($dpProAppointments['appointment_fields'])) {
                    if (in_array('address', $dpProAppointments['appointment_fields'])) {
                        $html .= '
							<label class="dp_appointments_input_label">' . __('Address', 'dpProAppointments') . '</label>
							<input type="text" class="dp_appointments_input_text" id="dp_appointments_appointment_address" value="" />

							<div class="clear"></div>
							';
                    }
                    if (in_array('city', $dpProAppointments['appointment_fields'])) {
                        $html .= '
							<label class="dp_appointments_input_label">' . __('City', 'dpProAppointments') . '</label>
							<input type="text" class="dp_appointments_input_text" id="dp_appointments_appointment_city" value="" />

							<div class="clear"></div>
							';
                    }
                    if (in_array('note', $dpProAppointments['appointment_fields'])) {
                        $html .= '
							<label class="dp_appointments_input_label">' . __('Note', 'dpProAppointments') . '</label>
							<input type="text" class="dp_appointments_input_text" value="" id="dp_appointments_appointment_note" />

							<div class="clear"></div>
							';
                    }
                }

                if (is_numeric($dpProAppointments['terms_conditions'])) {
                    $html .= '
					<p class="dp_appointments_terms_conditions"><input type="checkbox" name="appointments_event_page_book_terms_conditions" id="appointments_event_page_book_terms_conditions" value="1" /> ' . sprintf(__('I\'ve read and accept the %s terms & conditions %s', 'dpProAppointments'), '<a href="' . get_permalink($dpProAppointments['terms_conditions']) . '" target="_blank">', '</a>') . '</p>

					<div class="clear"></div>
				';
                }
                if ($count == 0) {
                    $html .= '
				<div class="pro-appointments-add-footer">
					<button class="dp_appointments_pimary_btn" id="dp_appointments_send_appointment">שמור</button>
					<button class="dp_appointments_secondary_btn" id="dp_appointments_cancel_appointment">בטל</button>
					<div class="clear"></div>
				</div>';
                }
            }
            $html .= '
		</div>';
        }
    }

    die($html);

}

add_action('wp_ajax_nopriv_sendAppointmentForm', 'dpProAppointments_sendAppointmentForm');
add_action('wp_ajax_sendAppointmentForm', 'dpProAppointments_sendAppointmentForm');

/***Yulia: for Long Appointment**/
function getSomeDayFreeAppointment($datetime)
{
    if ($this->countAppointmentsByDateTime($appointment_date) < $dpProAppointments['capacity'] || ($dpProAppointments['capacity'] == 0 || !is_numeric($dpProAppointments['capacity']))) {
        $html .= '
		<div class="dp_appointments_date" ' . ($x == 1 ? 'style="margin-left: 0;"' : '') . ' data-appointment-date="' . $appointment_date . '">
			<i class="fa fa-circle-thin"></i>';
        $html .= '<div class="dp_appointments_tooltip">' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $appointment_date) . '</div>';

    }

}
function countAppointmentsByDateTime($datetime)
{
    global $dpProAppointments;

    $not_in_type = array();
    $terms = get_terms("pro_appointments_service", array("hide_empty" => false));
    foreach ($terms as $term) {
        $not_in_type[] = $term->term_id;
    }

    $args = array(
        'posts_per_page' => -1,
        'post_type' => 'pro-appointments',
        'meta_key' => 'appointment_date',
        'meta_value' => $datetime,
        'post_status' => 'completed',
        'tax_query' => array(
            array(
                'taxonomy' => 'pro_appointments_service',
                'field' => 'id',
                'terms' => $this->opts['service'] ? $this->opts['service'] : $not_in_type,
                'operator' => $this->opts['service'] ? 'IN' : 'NOT IN',
            ),
        ),
    );

    $appointments = get_posts($args);

    return count($appointments);

}
/*******************************************/
function dpProAppointments_sendAppointmentForm()
{

    $nonce = $_POST['postEventsNonce'];
    //if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //    die ( 'Busted!');

    if (!is_numeric($_POST['date'])) {die();}

    global $dpProAppointments, $appointment_service_meta;

    $timestamp = $_POST['date'];
    $name = $_POST['name'];
    $email = "yulia@woweb.co.il";
    $phone = $_POST['phone'];
    $city = $_POST['city'];
    $address = $_POST['address'];
    $note = $_POST['note'];
    $service = $_POST['service'];
    $userid = $_POST['userid'];
    $return_url = $_POST['return_url'];

    $price = $dpProAppointments['price'];
    if ($appointment_service_meta[$service]['price'] > 0) {
        $price = $appointment_service_meta[$service]['price'];
    }

    $category = array($service);

    // Create post object
    $my_appointment = array(
        'post_title' => 'Appointment created at ' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), current_time('timestamp')),
        'post_content' => '',
        'post_category' => $category,
        'post_type' => 'pro-appointments',
        'post_status' => 'completed',
        //'post_status'   => ($dpProAppointments['approve_automatically'] ? 'publish' : 'pending')
    );

    if (is_numeric($price) && $price > 0) {
        $my_appointment['post_status'] = 'pending_payment';
    }

    // Insert the post into the database
    $appointment_id = wp_insert_post($my_appointment);

    wp_set_post_terms($appointment_id, $category, 'pro_appointments_service');

    update_post_meta($appointment_id, 'appointment_date', $timestamp);
    update_post_meta($appointment_id, 'appointment_creation', current_time('timestamp'));
    update_post_meta($appointment_id, 'appointment_name', $name);
    update_post_meta($appointment_id, 'appointment_email', $email);
    update_post_meta($appointment_id, 'appointment_phone', $phone);
    update_post_meta($appointment_id, 'appointment_city', $city);
    update_post_meta($appointment_id, 'appointment_address', $address);
    update_post_meta($appointment_id, 'appointment_note', $note);
    update_post_meta($appointment_id, 'appointment_userid', $userid);

    if ($price > 0) {

        $html = '<p>' . __('Appointment sent sucessfully, please select a payment method to continue.', 'dpProAppointments') . '</p>';

    } else {

        if ($dpProAppointments['approve_automatically']) {
            $html = '<p>' . __('Appointment sent sucessfully, you will receive a confirmation through email.', 'dpProAppointments') . '</p>';
        } else {
            $html = '<p>' . __('Appointment sent sucessfully, you will receive an email when it\'s approved.', 'dpProAppointments') . '</p>';
        }

    }

    if ($dpProAppointments['appointment_email_template_user'] == '') {
        $dpProAppointments['appointment_email_template_user'] = "Hi #USERNAME#,\n\nThanks for set the appointment:\n\n#APPOINTMENT_DETAILS#\n\nPlease contact us if you have questions.\n\nKind Regards.\n#SITE_NAME#";
    }

    if ($dpProAppointments['appointment_email_template_admin'] == '') {
        $dpProAppointments['appointment_email_template_admin'] = "The user #USERNAME# created an appointment:\n\n#APPOINTMENT_DETAILS#\n\n#COMMENT#\n\n#SITE_NAME#";
    }

    add_filter('wp_mail_from_name', 'dpProAppointments_wp_mail_from_name');
    add_filter('wp_mail_from', 'dpProAppointments_wp_mail_from');

    // Email to User

    wp_mail($email, get_bloginfo('name'), apply_filters('appointment_email_template', $dpProAppointments['appointment_email_template_user'], $appointment_id, $name, $email, $timestamp, $note));

    // Email to Admin

    wp_mail(get_bloginfo('admin_email'), get_bloginfo('name'), apply_filters('appointment_email_template', $dpProAppointments['appointment_email_template_admin'], $appointment_id, $name, $email, $timestamp, $note));

    $html .= '
			<div class="clear"></div>
			<div class="pro-appointments-add-footer">
				';

    if ($price > 0) {

        if ($dpProAppointments['paypal_enable']) {

            require_once dirname(__FILE__) . '/classes/class-gateway-paypal.php';
            $paypal = new Pro_Appointments_Gateway_Paypal();

            $html .= apply_filters('pro_appointment_receipt_paypal', '', $appointment_id, $timestamp, $return_url);

        }

        if ($dpProAppointments['stripe_enable']) {

            require_once dirname(__FILE__) . '/classes/class-gateway-stripe.php';
            $stripe = new Pro_Appointments_Gateway_Stripe();

            $html .= apply_filters('pro_appointment_receipt_stripe', '', $appointment_id, $timestamp, $return_url);

        }

    } else {

        $html .= '
				<button class="dp_appointments_pimary_btn" id="dp_appointments_cancel_appointment">' . __('Continue', 'dpProAppointments') . '</button>
				';

    }

    $html .= '
				<div class="clear"></div>
			</div>';

    die($html);

}

add_action('wp_ajax_nopriv_getMoreAppointments', 'dpProAppointments_getMoreAppointments');
add_action('wp_ajax_getMoreAppointments', 'dpProAppointments_getMoreAppointments');

function dpProAppointments_getMoreAppointments()
{

    $nonce = $_POST['postEventsNonce'];
    //if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //    die ( 'Busted!');

    if (!is_numeric($_POST['limit'])) {die();}

    global $dpProAppointments;

    $limit = $_POST['limit'];
    $page = $_POST['page'];
    $show_status = $_POST['show_status'];

    $html = "";

    require_once 'classes/base.class.php';

    $dpProAppointments_class = new DpProAppointments(false, null, '', array('limit' => $limit, 'show_status' => $show_status));

    $appointments = $dpProAppointments_class->getAppointmentsByUser('', 'any', ($limit * $page));

    $html .= $dpProAppointments_class->listAppointments($appointments);

    die($html);
}

function dpProAppointments_payments_scripts()
{
    global $dpProAppointments;

    if ($dpProAppointments['stripe_enable']) {

        wp_enqueue_script('the_stripe_js', 'https://js.stripe.com/v2/');

    }

}

add_action('init', 'dpProAppointments_payments_scripts');

function dpProAppointments_footer_scripts()
{
    global $dpProAppointments;

    if ($dpProAppointments['stripe_enable']) {

        echo '<script type="text/javascript">
		  // This identifies your website in the createToken call below
		  Stripe.setPublishableKey("' . trim($dpProAppointments['stripe_testmode'] ? $dpProAppointments['test_publishable_key'] : $dpProAppointments['live_publishable_key']) . '");
		  // ...

		  jQuery(document).on("click", "#dp_appointments_payment_stripe", function() {

			 jQuery(".dpProAppointments_payment_btn").hide();

			 jQuery("#appointments-payment-form-stripe").show();

			 jQuery("#appointments_payment_order_txt").text("' . __('Introduce your credit card information in the form below.', 'dpProAppointments') . '");

			 jQuery(".dpProAppointmentsModal").attr("style", "display:block; max-width: 300px !important;");

		  });


		  jQuery(document).on("submit", "#appointments-payment-form-stripe", function() {
			var $form = jQuery(this);

			// Disable the submit button to prevent repeated clicks
			$form.find("button").prop("disabled", true);

			Stripe.card.createToken($form, stripeResponseHandler);

			// Prevent the form from submitting with the default action
			return false;
		  });

		  function stripeResponseHandler(status, response) {
			  var $form = jQuery("#appointments-payment-form-stripe");

			  $form.find(".appointments-errors").hide();

			  if (response.error) {
				// Show the errors on the form
				$form.find(".appointments-errors").text(response.error.message).show();
				$form.find("button").prop("disabled", false);
			  } else {
				// response contains id and card, which contains additional card details
				var token = response.id;
				// Insert the token into the form so it gets submitted to the server
				$form.append(jQuery("<input type=\"hidden\" name=\"stripeToken\" />").val(token));
				// and submit
				$form.get(0).submit();
			  }
			};
		</script>';

    }

}

add_action('wp_footer', 'dpProAppointments_footer_scripts', 100);

function dp_appointments_payments_return()
{

    $return = '

	<div class="dpProAppointmentsModal dp_pro_appointments" id="appointments_modal_standalone" style="display: block;">
		<h2>' . __('Appointment', 'dpProAppointments') . '</h2>

		<div class="pro-appointments-content">
			<p>' . __('Thanks for your purchase, you will receive an email with the details of the appointment.', 'dpProAppointments') . '</p>

			<div class="clear"></div>

			<div class="pro-appointments-add-footer">
				<button class="dp_appointments_pimary_btn" id="dp_appointments_close_appointment">' . __('Close', 'dpProAppointments') . '</button>
				<div class="clear"></div>
			</div>

		</div>

	</div>

	<div class="dpProAppointmentsOverlay" id="appointments_overlay_standalone" style="display: block;"></div>

	<script type="text/javascript">
		jQuery("#dp_appointments_close_appointment, #appointments_overlay_standalone").click(function() {
			jQuery("#appointments_modal_standalone").remove();
			jQuery("#appointments_overlay_standalone").remove();
		});
	</script>';

    echo $return;

}

if ($_GET['appointments_return'] == 1) {
    add_action('wp_footer', 'dp_appointments_payments_return', 100);
}

function dp_appointments_payments_return_failed()
{

    $return = '

	<div class="dpProAppointmentsModal dp_pro_appointments" id="appointments_modal_standalone" style="display: block;">
		<h2>' . __('Appointment', 'dpProAppointments') . '</h2>

		<div class="pro-appointments-content">
			<p>' . __('The transaction couldn\'t be completed succesfully. Please try again later.', 'dpProAppointments') . '</p>

			<div class="clear"></div>

			<div class="pro-appointments-add-footer">
				<button class="dp_appointments_pimary_btn" id="dp_appointments_close_appointment">' . __('Close', 'dpProAppointments') . '</button>
				<div class="clear"></div>
			</div>

		</div>

	</div>

	<div class="dpProAppointmentsOverlay" id="appointments_overlay_standalone" style="display: block;"></div>

	<script type="text/javascript">
		jQuery("#dp_appointments_close_appointment, #appointments_overlay_standalone").click(function() {
			jQuery("#appointments_modal_standalone").remove();
			jQuery("#appointments_overlay_standalone").remove();
		});
	</script>';

    echo $return;

}

if ($_GET['appointments_return_failed'] == 1) {
    add_action('wp_footer', 'dp_appointments_payments_return_failed', 100);
}

function dp_appointments_payments_ipn()
{
    do_action('dpProAppointments_api_wc_gateway_paypal');
}

if ($_GET['appointments-api'] == 'PEC_Gateway_Paypal') {
    add_action('init', 'dp_appointments_payments_ipn', 100);
}

function dpProAppointments_wp_mail_from_name($original_email_from)
{
    return get_bloginfo('name');
}

function dpProAppointments_wp_mail_from($original_email_address)
{
    global $dpProAppointments;
    //Make sure the email is from the same domain
    //as your website to avoid being marked as spam.
    return ($dpProAppointments['wp_mail_from'] != "" ? $dpProAppointments['wp_mail_from'] : $original_email_address);
}

add_filter('appointment_email_template', 'dpProAppointments_bookingEmail', 10, 6);

function dpProAppointments_bookingEmail($template, $appointment_id, $user_name, $user_email, $timestamp, $note)
{

    $template = str_replace("#USERNAME#", $user_name, $template);

    $template = str_replace("#COMMENT#", $note, $template);

    $template = str_replace("#APPOINTMENT_DATE#", date_i18n(get_option('date_format'), $timestamp) . ' - ' . date_i18n(get_option('time_format'), $timestamp), $template);

    $template = str_replace("#APPOINTMENT_DETAILS#", "---------------------------\n\rAppointment ID: " . $appointment_id . "\n\r" . date_i18n(get_option('date_format'), $timestamp) . ' - ' . date_i18n(get_option('time_format'), $timestamp) . "\n\r" . "\n\r---------------------------\n\r", $template);

    $template = str_replace("#SITE_NAME#", get_bloginfo('name'), $template);

    return html_entity_decode($template);

}

function dpProAppointments_setup_reminders()
{
    global $dpProAppointments;

    if ($dpProAppointments['reminders_enable']) {

        //Schedule

        if (!wp_next_scheduled('appointmentsreminder')) {
            $scheduled = wp_schedule_event(time(), 'daily', 'appointmentsreminder');
            //die($scheduled.'<br>'.$key->calendar_id);
        }

        add_action('appointmentsreminder', 'dpProAppointments_run_reminders', 10);

    } else {

        // Unschedule

        // Get the timestamp for the next event.

        wp_clear_scheduled_hook('appointmentsreminder');

    }

}
add_action('init', 'dpProAppointments_setup_reminders', 10);

function dpProAppointments_run_reminders()
{
    global $dpProAppointments;

    if ($dpProAppointments['reminders_enable']) {

        $reminder_anticipation = $dpProAppointments['reminder_anticipation'];

        if (!is_numeric($reminder_anticipation)) {

            $reminder_anticipation = 1;

        }

        if ($dpProAppointments['appointment_reminder_template_user'] == "") {
            $dpProAppointments['appointment_reminder_template_user'] = "Hi #USERNAME#,\n\nWe would like to remind you that the day #APPOINTMENT_DATE# you have an appointment.\n\nKind Regards.\n#SITE_NAME#";
        }

        add_filter('wp_mail_from_name', 'dpProAppointments_wp_mail_from_name');
        add_filter('wp_mail_from', 'dpProAppointments_wp_mail_from');

        // Email to User

        $args = array(
            'post_type' => 'pro-appointments',
            'post_status' => 'publish',
            'meta_key' => 'appointment_date',
            'orderby' => 'meta_value',
            'meta_compare' => '>=',
            'meta_value' => current_time('timestamp'),
            'order' => 'ASC',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'appointment_date',
                    'value' => array(current_time('timestamp') + ($reminder_anticipation * 60 * 60 * 24), current_time('timestamp') + (($reminder_anticipation + 1) * 60 * 60 * 24)),
                    'compare' => 'BETWEEN',
                    'type' => 'TIME',
                ),
            ),
        );

        $appointments = get_posts($args);

        foreach ($appointments as $appointment) {

            $appointment_date = get_post_meta($appointment->ID, 'appointment_date', true);
            $appointment_email = get_post_meta($appointment->ID, 'appointment_email', true);
            $appointment_name = get_post_meta($appointment->ID, 'appointment_name', true);

            if (!is_numeric($appointment_date)) {
                continue;
            }

            wp_mail($appointment_email, get_bloginfo('name'), apply_filters('appointment_email_template', $dpProAppointments['appointment_reminder_template_user'], $appointment->ID, $appointment_name, $appointment_email, $appointment_date, ''));

        }

    }

}

add_action('pro_appointments_service_edit_form_fields', 'dpProAppointments_service_custom_fields');
add_action('pro_appointments_service_add_form_fields', 'dpProAppointments_service_custom_fields');

function dpProAppointments_service_custom_fields($tag)
{
    // your custom field HTML will go here
    // the $tag variable is a taxonomy term object with properties like $tag->name, $tag->term_id etc...

    // we need to know the values of our existing entries if any
    $appointment_service_meta = get_option('appointment_service_meta');
    ?>
    <div class="form-field term-slug-wrap">
        <th scope="row" valign="top"><label for="service-price"><?php _e("Price", "dpProAppointments");?></label></th>
        <td>
            <input type="number" max="10000" min="0" id="service-price" name="appointment_service_meta[<?php echo $tag->term_id ?>][price]" value="<?php if (isset($appointment_service_meta[$tag->term_id])) {
        esc_attr_e($appointment_service_meta[$tag->term_id]['price']);
    }
    ?>" />
            <p><?php _e('Enter a price for this service (Only Numbers). If empty, the default price will be used instead.', "dpProAppointments");?></p>
        </td>
    </div>
    <!-- rinse & repeat for other fields you need -->
    <?php
}

function dpProAppointments_service_custom_fields_save()
{

    if (isset($_POST['appointment_service_meta']) && !update_option('appointment_service_meta', $_POST['appointment_service_meta'])) {
        add_option('appointment_service_meta', $_POST['appointment_service_meta']);
    }

}

add_action('created_pro_appointments_service', 'dpProAppointments_service_custom_fields_save', 10);
add_action('edit_pro_appointments_service', 'dpProAppointments_service_custom_fields_save', 10);

// On Delete Service
function dpProAppointments_service_custom_fields_delete($term_id, $tt_id)
{
    $appointment_service_meta = get_option('appointment_service_meta');
    unset($appointment_service_meta[$term_id]);

    update_option('appointment_service_meta', $appointment_service_meta);
}
add_action('delete_pro_appointments_service', 'ft_delete_manufacturer', 10, 2);
?>