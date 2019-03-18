<?php
require_once dirname(__FILE__) . '/dates.class.php';

function get_local_days_of_week()
{
    $timestamp = strtotime('next Sunday');
    $days = array();
    for ($i = 0; $i < 7; $i++) {
        $days[] = date_i18n('D', $timestamp);
        $timestamp = strtotime('+1 day', $timestamp);
    }
    return $days;
}

class DpProAppointments
{

    public $nonce;
    public $is_admin = false;
    public $type = 'schedule';
    public $opts = array();
    public $service_list = array();
    public $datesObj;

    public function dpProAppointments($is_admin = false, $defaultDate = null, $type = "", $opts = array())
    {
        if ($is_admin) {$this->is_admin = true;}
        $this->defaultDate = $defaultDate;
        if (!isset($defaultDate) || is_null($defaultDate)) {$this->defaultDate = current_time('timestamp');}
        if ($type != "") {$this->type = $type;}
        $this->opts = $opts;

        $this->service_list = explode(",", $this->opts['service']);
        if (count($this->service_list) > 1) {
            $this->opts['service'] = $this->service_list[0];
        }

        $this->nonce = rand();

        $this->datesObj = new DPAppointments_Dates($this->defaultDate);

        //die(print_r($this->datesObj));
    }

    public function getNonce()
    {

        return $this->nonce;
    }

    public function addScripts()
    {

        global $dpProAppointments;

        $script = '';

        $script .= '
		<script type="text/javascript">
		// <![CDATA[
		';

        $script .= '
			jQuery(document).ready(function() {

				function startProAppointments() {

					jQuery("#dp_pro_appointments_id' . $this->nonce . '").dpProAppointments({
						nonce: "dp_appointments_id' . $this->nonce . '",
						';
        if ($this->is_admin) {
            $script .= '
							isAdmin: true,
							';
        }
        if (is_numeric($this->opts['service'])) {
            $script .= '
							service: ' . $this->opts['service'] . ',
							';
        }

        if ($this->opts['show_tooltip'] == "") {
            $this->opts['show_tooltip'] = 1;
        }
        $script .= '
						tooltip: ' . $this->opts['show_tooltip'] . ',
						';

        $script .= '
						show_status: ' . ($this->opts['show_status'] ? $this->opts['show_status'] : 0) . ',
						actualMonth: ' . $this->datesObj->currentMonth . ',
						actualYear: ' . $this->datesObj->currentYear . ',
						actualDay: ' . $this->datesObj->currentDate . ',
						defaultDate: "' . $this->defaultDate . '",
						defaultDateFormat: "' . date('Y-m-d', $this->defaultDate) . '"
					});
				}';

        $script .= '
				startProAppointments();
			});';

        $script .= '

		//]]>
		</script>';

        return $script;

    }

    public function countAppointmentsByDateTime($datetime)
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

    public function weeklyCalendarLayout($curDate = null)
    {

        global $dpProAppointments;

        if (is_null($curDate)) {
            $curDate = $this->datesObj->currentYear . '-' . str_pad($this->datesObj->currentMonth, 2, "0", STR_PAD_LEFT) . '-' . str_pad($this->datesObj->currentDate, 2, "0", STR_PAD_LEFT);
        }

        if ($dpProAppointments['first_day'] == 1) {
            $weekly_first_date = strtotime('last monday', ($this->defaultDate + (24 * 60 * 60)));
            $weekly_last_date = strtotime('next sunday', ($this->defaultDate - (24 * 60 * 60)));
        } else {
            $weekly_first_date = strtotime('last sunday', ($this->defaultDate + (24 * 60 * 60)));
            $weekly_last_date = strtotime('next saturday', ($this->defaultDate - (24 * 60 * 60)));
        }

        $week_days = array();

        $week_days[0] = $weekly_first_date;
        $weekly_one = date_i18n('d', $weekly_first_date);
        $week_days[1] = strtotime('+1 day', $weekly_first_date);
        $weekly_two = date_i18n('d', $week_days[1]);
        $week_days[2] = strtotime('+2 day', $weekly_first_date);
        $weekly_three = date_i18n('d', $week_days[2]);
        $week_days[3] = strtotime('+3 day', $weekly_first_date);
        $weekly_four = date_i18n('d', $week_days[3]);
        $week_days[4] = strtotime('+4 day', $weekly_first_date);
        $weekly_five = date_i18n('d', $week_days[4]);
        $week_days[5] = strtotime('+5 day', $weekly_first_date);
        $weekly_six = date_i18n('d', $week_days[5]);
        $week_days[6] = strtotime('+6 day', $weekly_first_date);
        $weekly_seven = date_i18n('d', $week_days[6]);

        $days = get_local_days_of_week();

        $html .= '<div class="dayname-sticky" data-scroll-class="200px:pos-fixed">';
        if ($dpProAppointments['first_day'] == 1) {
            if ($this->datesObj->firstDayNum == 0) {$this->datesObj->firstDayNum == 7;}
            $this->datesObj->firstDayNum--;

            $html .= '

				 <div class="dp_appointments_dayname">
						<span>' . $weekly_one . ' <strong>' . $days[1] . '</strong></span>
				 </div>';
        } else {
            $html .= '
				 <div class="dp_appointments_dayname">
						<span>' . $weekly_one . ' <strong>' . $days[0] . '</strong></span>
				 </div>
				 <div class="dp_appointments_dayname">
						<span>' . $weekly_two . ' <strong>' . $days[1] . '</strong></span>
				 </div>';
        }
        $html .= '
				 <div class="dp_appointments_dayname">
						<span>' . ($dpProAppointments['first_day'] == 1 ? $weekly_two : $weekly_three) . ' <strong>' . $days[2] . '</strong></span>
				 </div>
				 <div class="dp_appointments_dayname">
						<span>' . ($dpProAppointments['first_day'] == 1 ? $weekly_three : $weekly_four) . ' <strong>' . $days[3] . '</strong></span>
				 </div>
				 <div class="dp_appointments_dayname">
						<span>' . ($dpProAppointments['first_day'] == 1 ? $weekly_four : $weekly_five) . ' <strong>' . $days[4] . '</strong></span>
				 </div>
				 <div class="dp_appointments_dayname">
						<span>' . ($dpProAppointments['first_day'] == 1 ? $weekly_five : $weekly_six) . ' <strong>' . $days[5] . '</strong></span>
				 </div>
				 <div class="dp_appointments_dayname">
						<span>' . ($dpProAppointments['first_day'] == 1 ? $weekly_six : $weekly_seven) . ' <strong>' . $days[6] . '</strong></span>
				 </div>
				 ';
        if ($dpProAppointments['first_day'] == 1) {
            $html .= '
				 <div class="dp_appointments_dayname">
						<span>' . $weekly_seven . ' <strong>' . $days[0] . '</strong></span>
				 </div>';
        }

        $html .= '</div>';
        if (!isset($dpProAppointments['schedule_start_time'])) {
            $dpProAppointments['schedule_start_time'] = '08:00';
        }

        if (!isset($dpProAppointments['schedule_end_time'])) {
            $dpProAppointments['schedule_end_time'] = '17:00';
        }

        $schedule_start_time = explode(':', $dpProAppointments['schedule_start_time']);
        $schedule_end_time = explode(':', $dpProAppointments['schedule_end_time']);

        $start_hour = $schedule_start_time[0];
        $end_hour = $schedule_end_time[0];
        $interval = $dpProAppointments['time_base'];
        if (empty($interval)) {
            $interval = 30;
        }

        //$interval = 105;
        $min_between = (($end_hour - $start_hour) * 60) + ($schedule_start_time[1] + $schedule_end_time[1]) - 1;

        $min_reset = 0;
        $html .= '<div class="dp_appointments_row">';
        for ($i = 0; $i <= $min_between; $i += $interval) {

            $timestamp = strtotime("+ " . $min_reset . "minutes", mktime($start_hour, $schedule_start_time[1]));
            $time = date('H:i', $timestamp);
            $min = date("i", $timestamp);
            $hour = date("H", $timestamp);
            $min_reset += $interval;

            $html .= '
			<div class="dp_appointments_row">
				<div class="dp_appointments_date first-child" ' . ($this->opts['time_background'] != "" && !is_null($this->opts['time_background']) ? 'style="background: ' . $this->opts['time_background'] . ' !important;"' : '') . '>
					<div class="dp_date_head" ' . ($this->opts['time_background'] != "" && !is_null($this->opts['time_background']) ? 'style="background: ' . $this->opts['time_background'] . ' !important;"' : '') . '><span>' . $time . '</span></div>
				</div>
				';

            if (is_numeric($this->opts['service'])) {
                $exceptions = explode(",", $dpProAppointments['service_' . $this->opts['service']]['exceptions']);
                $working_days_list = $dpProAppointments['service_' . $this->opts['service']]['working_days'];
            } else {
                $exceptions = explode(",", $dpProAppointments['exceptions']);
                $working_days_list = $dpProAppointments['working_days'];
            }

            for ($x = 1; $x <= 7; $x++) {

                $curDate = date('Y-m-d', $week_days[$x - 1]);

                $appointment_date = strtotime($curDate . ' ' . $hour . ':' . $min . ':00');

                $block_hours = ($dpProAppointments['block_hours'] > 0 ? $dpProAppointments['block_hours'] : 0);

                $recurring_appointment = get_recurring_appointment_by_date($appointment_date);

                if (count($recurring_appointment) > 0) {
                    $html .= '<div data-id-recurring-appointment="' . $recurring_appointment[0]->id . '" class="dp_appointments_date dp_appointments_appointed" ' . ($x == 1 ? 'style="margin-left: 0;"' : '') . '>';
                    $html .= '<span>' . $recurring_appointment[0]->name . '</span>';
                    $html .= '<input type="checkbox" name="' . $appointment_date . '">';
                } else if (((!$working_days_list[date('l', $appointment_date)]['work'] || str_replace(":", "", $working_days_list[date('l', $appointment_date)]['start']) > ($hour . $min) || str_replace(":", "", $working_days_list[date('l', $appointment_date)]['end']) < ($hour . $min)) && !in_array($curDate, $exceptions)) || $appointment_date <= (current_time('timestamp') + ($dpProAppointments['block_hours'] * 60 * 60))) {
                    $html .= '
						<div class="dp_appointments_date dp_appointments_closed" data-appointment-date="' . $appointment_date . '">
							<i class="fa fa-close"></i>';

                } else if ($this->countAppointmentsByDateTime($appointment_date) < $dpProAppointments['capacity'] || ($dpProAppointments['capacity'] == 0 || !is_numeric($dpProAppointments['capacity']))) {
                    $html .= '
						<div class="dp_appointments_date" ' . ($x == 1 ? 'style="margin-left: 0;"' : '') . ' data-appointment-date="' . $appointment_date . '">
							<input type="checkbox" name="' . $appointment_date . '">';
                } else {

                    global $dpProAppointments;

                    $not_in_type = array();
                    $terms = get_terms("pro_appointments_service", array("hide_empty" => false));
                    foreach ($terms as $term) {
                        $not_in_type[] = $term->term_id;
                    }
                    $datetime = $appointment_date;
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

                    $appointment = get_posts($args);

                    $obj_id = get_queried_object_id();
                    $current_url = get_permalink($obj_id);
                    $home_link = get_home_url() . '/';
                    if ($home_link === $current_url) {
                        $is_home = 'home';
                    } else { $is_home = '';}
                    //echo "<script>console.log( 'Debug Objects: " . $current_url . "' );</script>";
                    //echo '<pre>'; print_r(get_post_meta($appointment[0]->ID, 'appointment_name', true)); echo '</pre>';
                    $html .= '
						<div data-id-appointment="' . $appointment[0]->ID . '" class="dp_appointments_date dp_appointments_appointed" ' . ($x == 1 ? 'style="margin-left: 0;"' : '') . ' data-is-home="' . $is_home . '" data-appointment-date="' . $appointment_date . '" >';
                    /*$html .= '
                    <div id="delete_appointment" data-id-appointment="'.$appointment[0]->ID.'" class="dp_appointments_date dp_appointments_appointed" '.($x == 1 ? 'style="margin-left: 0;"' : '').' data-appointment-date="'.$appointment_date.'" onclick="if(confirm(\''.__('בטוח למחוק?', 'dpProAppointments').'\')) { location.href=\''.admin_url('admin.php?page=dpProAppointments-admin&delete_appointment='.$appointment[0]->ID.'&noheader=true').'\'; }">';*/
                    //<i class="fa fa-dot-circle-o"></i>

                    if ($this->opts['show_tooltip']) {
                        $html .= '<span>' . get_post_meta($appointment[0]->ID, 'appointment_name', true) . '</span>';
                        //wp_get_post_terms( $post_id = 0, $taxonomy = 'post_tag', $args = array() )
                        $html .= '<input type="checkbox" name="' . $appointment_date . '">';

                    }
                }

                $html .= '
						</div>';

            }
            $html .= '</div>';
        }

        $html .= '<div class="">';
        if ($dpProAppointments['first_day'] == 1) {
            if ($this->datesObj->firstDayNum == 0) {$this->datesObj->firstDayNum == 7;}
            $this->datesObj->firstDayNum--;

            $html .= '

				 <div class="dp_appointments_dayname">
						<span>' . $weekly_one . ' <strong>' . $days[1] . '</strong></span>
				 </div>';
        } else {
            $html .= '
				 <div class="dp_appointments_dayname">
						<span>' . $weekly_one . ' <strong>' . $days[0] . '</strong></span>
				 </div>
				 <div class="dp_appointments_dayname">
						<span>' . $weekly_two . ' <strong>' . $days[1] . '</strong></span>
				 </div>';
        }
        $html .= '
				 <div class="dp_appointments_dayname">
						<span>' . ($dpProAppointments['first_day'] == 1 ? $weekly_two : $weekly_three) . ' <strong>' . $days[2] . '</strong></span>
				 </div>
				 <div class="dp_appointments_dayname">
						<span>' . ($dpProAppointments['first_day'] == 1 ? $weekly_three : $weekly_four) . ' <strong>' . $days[3] . '</strong></span>
				 </div>
				 <div class="dp_appointments_dayname">
						<span>' . ($dpProAppointments['first_day'] == 1 ? $weekly_four : $weekly_five) . ' <strong>' . $days[4] . '</strong></span>
				 </div>
				 <div class="dp_appointments_dayname">
						<span>' . ($dpProAppointments['first_day'] == 1 ? $weekly_five : $weekly_six) . ' <strong>' . $days[5] . '</strong></span>
				 </div>
				 <div class="dp_appointments_dayname">
						<span>' . ($dpProAppointments['first_day'] == 1 ? $weekly_six : $weekly_seven) . ' <strong>' . $days[6] . '</strong></span>
				 </div>
				 ';
        if ($dpProAppointments['first_day'] == 1) {
            $html .= '
				 <div class="dp_appointments_dayname">
						<span>' . $weekly_seven . ' <strong>' . $days[0] . '</strong></span>
				 </div>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;

    }

    public function output()
    {

        global $dpProAppointments;

        if ($this->type != 'schedule') {

            $html = $this->myAppointmentsLayout();
            return $html;

        }

        $html = '<div class="dp_pro_appointments appointments_skin_' . $this->opts['skin'] . ' appointments_service_' . $this->opts['service'] . '" id="dp_pro_appointments_id' . $this->nonce . '">';

        if ($dpProAppointments['first_day'] == 1) {
            $weekly_first_date = strtotime('last monday', ($this->defaultDate + (24 * 60 * 60)));
            $weekly_last_date = strtotime('next sunday', ($this->defaultDate - (24 * 60 * 60)));
        } else {
            $weekly_first_date = strtotime('last sunday', ($this->defaultDate + (24 * 60 * 60)));
            $weekly_last_date = strtotime('next saturday', ($this->defaultDate - (24 * 60 * 60)));
        }

        $weekly_format = get_option('date_format');
        $weekly_format = 'd F, Y';

        $weekly_txt = date_i18n('d F', $weekly_first_date) . ' - ' . date_i18n($weekly_format, $weekly_last_date);

        if (date('m', $weekly_first_date) == date('m', $weekly_last_date)) {

            $weekly_txt = date('d', $weekly_first_date) . ' - ' . date_i18n($weekly_format, $weekly_last_date);

        }

        if (date('Y', $weekly_first_date) != date('Y', $weekly_last_date)) {

            $weekly_txt = date_i18n($weekly_format, $weekly_first_date) . ' - ' . date_i18n($weekly_format, $weekly_last_date);

        }

        $html .= '
			<div class="dp_appointments_nav">';
        if (($this->opts['show_service_name'] && is_numeric($this->opts['service'])) || count($this->service_list) > 1) {

            if (count($this->service_list) > 1) {
                $html .= '<select name="dp_appointments_switch_service" class="dp_appointments_switch_service">';
                foreach ($this->service_list as $key) {
                    $service = get_term_by('id', $key, 'pro_appointments_service');
                    $html .= '<option value="' . $key . '">' . $service->name . '</span>';
                }
                $html .= '</select>';
                $html .= '<div class="dp_appointments_clear"></div>';
            } else {
                $service = get_term_by('id', $this->opts['service'], 'pro_appointments_service');
                $html .= '<div class="service_name">' . $service->name . '</div><button class="fa fa-close hide-on-adding-event" id="appoint_checklist_delete">למחוק שיבוץ</button><button class="hide-on-adding-event" id="appoint_checklist_push">לשמור שיבוץ</button>';
                $html .= '<div class="dp_appointments_clear"></div>';
            }
        }
        $html .= '
		<div class="harel-weeks-container">
				<span class="next_week"><strong>שבוע הבא</strong> &raquo;</span>
				<span class="prev_week" style="visibility:hidden    ;">&laquo; <strong>שבוע הקודם</strong></span>
				<span class="actual_week">' . $weekly_txt . '</span>
		</div>
				<div class="dp_appointments_clear"></div>
			</div>

			<div class="dp_appointments_content">

		';

        $html .= $this->weeklyCalendarLayout();

        $html .= '
			</div>
			<div class="clear"></div>
		</div>';

        return $html;

    }

    public function myAppointmentsLayout()
    {

        global $dpProAppointments, $current_user;

        if (!is_user_logged_in()) {
            return false;
        }

        $html = '<div class="dp_pro_appointments appointments_skin_' . $this->opts['skin'] . ' dp_pro_appointments_my_appointments appointments_service_' . $this->opts['service'] . '" id="dp_pro_appointments_id' . $this->nonce . '">';

        if ($this->opts['show_title']) {

            $html .= '
				<div class="dp_appointments_nav">
					<span class="actual_week">' . __('My Appointments', 'dpProAppointments') . '</span>
					<div class="dp_appointments_clear"></div>
				</div>
			';

        }

        $appointments = $this->getAppointmentsByUser('', 'any');

        $html .= '<div class="dp_appointments_my_appointments_list_wrap">';
        $total = count($this->getAppointmentsByUser('', 'any', 0, true));

        if ($total == 0) {
            $html .= '<p>' . __('No Appointments', 'dpProAppointments') . '</p>';
        } else {

            $html .= $this->listAppointments($appointments);

        }

        $html .= '</div>';

        if ($this->opts['show_pagination'] && $total > $this->opts['limit']) {

            $html .= '
				<div class="dp_appointments_clear"></div>
				<div class="dp_appointments_pagination">
					<a href="javascript:void(0);" class="dp_appointments_load_more dp_appointments_pimary_btn dp_appointments_full_width" data-limit="' . $this->opts['limit'] . '" data-total="' . $total . '">' . __('Load More', 'dpProAppointments') . '</a>
				</div>
				<div class="dp_appointments_clear"></div>
			';

        }

        $html .= '
			<div class="clear"></div>
		</div>';

        return $html;

    }

    public function getAppointmentsByUser($user_id = '', $status = 'publish', $offset = 0, $count = false)
    {

        global $dpProAppointments, $current_user;

        if (!is_numeric($user_id) && !is_user_logged_in()) {
            return false;
        }

        if (!is_numeric($user_id)) {
            $user_id = $current_user->ID;
        }

        $limit = $this->opts['limit'];

        if ($limit == 0 || !is_numeric($limit)) {
            $limit = -1;
        }

        $not_in_type = array();
        $terms = get_terms("pro_appointments_service", array("hide_empty" => false));
        foreach ($terms as $term) {
            $not_in_type[] = $term->term_id;
        }

        $args = array(
            'post_type' => 'pro-appointments',
            'author' => $user_id,
            'post_status' => $status,
            'meta_key' => 'appointment_date',
            'orderby' => 'meta_value',
            'meta_compare' => '>=',
            'meta_value' => current_time('timestamp'),
            'order' => 'ASC',
            'tax_query' => array(
                array(
                    'taxonomy' => 'pro_appointments_service',
                    'field' => 'id',
                    'terms' => $this->opts['service'] ? $this->opts['service'] : $not_in_type,
                    'operator' => $this->opts['service'] ? 'IN' : 'NOT IN',
                ),
            ),
        );

        if ($count) {
            $args['posts_per_page'] = -1;
        } else {
            $args['posts_per_page'] = $limit;
        }

        if ($offset != 0) {
            $args['offset'] = $offset;
        }

        $appointments = get_posts($args);

        return $appointments;

    }

    public function listAppointments($appointments)
    {

        $html = "";

        foreach ($appointments as $appointment) {

            $appointment_date = get_post_meta($appointment->ID, 'appointment_date', true);

            if (!is_numeric($appointment_date)) {
                continue;
            }

            $html .= '<div class="dp_appointments_my_appointments_list">';
            $html .= '<p><i class="fa fa-calendar"></i>' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $appointment_date) . '</p>';
            if ($this->opts['show_status']) {
                $status = "";

                switch ($appointment->post_status) {
                    case 'publish':
                        $status = __('Active', 'dpProAppointments');
                        break;
                    case 'pending':
                        $status = __('Pending of approval', 'dpProAppointments');
                        break;
                    case 'completed':
                        $status = __('Completed', 'dpProAppointments');
                        break;
                    case 'canceled':
                        $status = __('Canceled', 'dpProAppointments');
                        break;
                    case 'pending_payment':
                        $status = __('Pending of Payment', 'dpProAppointments');
                        break;
                }

                $html .= '<p class="dp_appointments_appointment_status">' . $status . '</p>';
            }
            $html .= '</div>';

        }

        return $html;

    }

}
