<?php
// This function displays the admin page content
function dpProAppointments_appointments_page()
{
    global $dpProAppointments;

    if ($_POST['action'] == 1) {

        if (!isset($_POST['appointment_date'])) {die();}

        $timestamp = strtotime($_POST['appointment_date'] . ' ' . $_POST['appointment_time']);
        $name = $_POST['appointment_name'];
        $email = $_POST['appointment_email'];
        $phone = $_POST['appointment_phone'];
        $city = $_POST['appointment_city'];
        $address = $_POST['appointment_address'];
        $note = $_POST['appointment_note'];
        $service = $_POST['appointment_service'];
        $userid = $_POST['appointment_userid'];
        $status = $_POST['appointment_status'];

        $edit = $_POST['appointment_edit'];

        $category = array($service);

        if (is_numeric($edit)) {

            // Edit post object
            $my_appointment = array(
                'ID' => $edit,
                'post_status' => $status,
            );

            $appointment_id = wp_update_post($my_appointment);

        } else {

            // Create post object
            $my_appointment = array(
                'post_title' => 'Appointment created at ' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), current_time('timestamp')),
                'post_content' => '',
                'post_type' => 'pro_appointments_service',
                'post_type' => 'pro-appointments',
                'post_status' => $status,
            );

            $appointment_id = wp_insert_post($my_appointment);

        }

        // Insert the post into the database

        if (is_numeric($edit)) {
            $appointment_id = $edit;
        }

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

        wp_redirect(admin_url('admin.php?page=dpProAppointments-admin&settings-updated=1'));
        exit;

    }

    if ($_GET['delete_appointment'] && is_admin()) {
        $appointment_id = $_GET['delete_appointment'];

        wp_delete_post($appointment_id);

        wp_redirect(admin_url('admin.php?page=dpProAppointments-admin&settings-updated=1'));
        exit;
    }
    ?>
<div class="wrap" style="clear:both;" id="dp_options">

<h2></h2>
<?php $url = dpProAppointments_admin_url(array('page' => 'dpProAppointments-admin'));?>

<div style="clear:both;"></div>
 <!--end of poststuff -->

    <div id="dp_ui_content">

        <div id="leftSide">
        	<div id="dp_logo"></div>
            <p>
                Version: <?php echo DP_APPOINTMENTS_VER ?><br />
            </p>
            <ul id="menu" class="nav">
                <li><a href="admin.php?page=dpProAppointments-settings" title=""><span><?php _e('General Settings', 'dpProAppointments');?></span></a></li>
                <li><a href="javascript:void(0);" class="active" title=""><span><?php _e('Appointments', 'dpProAppointments');?></span></a></li>
                <li><a href="admin.php?page=dpProAppointments-payments" title=""><span><?php _e('Payment Options', 'dpProAppointments');?></span></a></li>
                <li><a href="admin.php?page=dpProAppointments-custom-shortcodes" title=""><span><?php _e('Shortcode Generator', 'dpProAppointments');?></span></a></li>
            </ul>

            <div class="clear"></div>
		</div>

        <div id="rightSide">
        	<div id="menu_general_settings">
                <div class="titleArea">
                    <div class="wrapper">
                        <div class="pageTitle">
                            <h5><?php _e('Appointments', 'dpProAppointments');?></h5>
                            <span></span>
                        </div>

                        <div class="clear"></div>
                    </div>
                </div>

                <div class="wrapper">


                        <div class="new_appointment_form" style="display:none;">
                            <form name="" method="post" id="appointment_frm" action="<?php echo admin_url('admin.php?page=dpProAppointments-admin&noheader=true'); ?>">

                                <input type="hidden" name="action" value="1" />

                                <input type="text" name="appointment_userid" id="appointment_userid" value="" style="display:none;" />
                                <input type="text" name="appointment_edit" id="appointment_edit" value="" style="display:none;" />

                                <div class="option option-select option_w">
                                    <div class="option-inner">
                                        <label class="titledesc">תאריך הפגישה</label>
                                        <div class="formcontainer">
                                            <div class="forminp">
                                                <input type="text" class="dp_appointments_input_text appointment_date" name="appointment_date" id="appointment_date" value="" maxlength="10" placeholder="<?php _e('Format: YYYY-MM-DD', 'dpProAppointments')?>" />
                                                <br>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clear"></div>

                                <div class="option option-select option_w">
                                    <div class="option-inner">
                                        <label class="titledesc"><?php _e('Appointment Time:', 'dpProAppointments');?></label>
                                        <div class="formcontainer">
                                            <div class="forminp">
                                                <select name="appointment_time" id="appointment_time">
													<?php
for ($hour = 0; $hour <= 23; $hour++) {
        for ($min = 0; $min < 60; $min += 30) {
            $h_pad = str_pad($hour, 2, '0', STR_PAD_LEFT);
            $m_pad = str_pad($min, 2, '0', STR_PAD_LEFT);
            ?>

                                                        <option value="<?php echo $h_pad . ':' . $m_pad ?>"><?php echo date(get_option('time_format'), mktime($hour, $min)) ?></option>

                                                    <?php }
    }?>
                                                </select>
                                                <br>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clear"></div>

                                <div class="option option-select option_w">
                                    <div class="option-inner">
                                        <label class="titledesc"><?php _e('Service:', 'dpProAppointments');?></label>
                                        <div class="formcontainer">
                                            <div class="forminp">
                                                <select name="appointment_service" id="appointment_service">
													<option value=""><?php _e('Default', 'dpProAppointments');?></option>
													<?php
$tax_terms = get_terms('pro_appointments_service', array('hide_empty' => false));

    foreach ($tax_terms as $tax_term) {
        ?>
                                                        <option value="<?php echo $tax_term->term_id ?>"><?php echo $tax_term->name ?></option>
                                                    <?php }?>
                                                </select>
                                                <br>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clear"></div>

                                <div class="option option-select option_w">
                                    <div class="option-inner">
                                        <label class="titledesc"><?php _e('Name:', 'dpProAppointments');?></label>
                                        <div class="formcontainer">
                                            <div class="forminp">
                                                <input type="text" class="dp_appointments_input_text" name="appointment_name" id="appointment_name" value="" />
                                                <br>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clear"></div>

                                <div class="option option-select option_w">
                                    <div class="option-inner">
                                        <label class="titledesc"><?php _e('Email:', 'dpProAppointments');?></label>
                                        <div class="formcontainer">
                                            <div class="forminp">
                                                <input type="text" class="dp_appointments_input_text" name="appointment_email" id="appointment_email" value="" />
                                                <br>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clear"></div>

                                <div class="option option-select option_w">
                                    <div class="option-inner">
                                        <label class="titledesc"><?php _e('Phone:', 'dpProAppointments');?></label>
                                        <div class="formcontainer">
                                            <div class="forminp">
                                                <input type="text" class="dp_appointments_input_text" name="appointment_phone" id="appointment_phone" value="" />
                                                <br>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clear"></div>

                                <div class="option option-select option_w">
                                    <div class="option-inner">
                                        <label class="titledesc"><?php _e('City:', 'dpProAppointments');?></label>
                                        <div class="formcontainer">
                                            <div class="forminp">
                                                <input type="text" class="dp_appointments_input_text" name="appointment_city" id="appointment_city" value="" />
                                                <br>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clear"></div>

                                <div class="option option-select option_w">
                                    <div class="option-inner">
                                        <label class="titledesc"><?php _e('Address:', 'dpProAppointments');?></label>
                                        <div class="formcontainer">
                                            <div class="forminp">
                                                <input type="text" class="dp_appointments_input_text" name="appointment_address" id="appointment_address" value="" />
                                                <br>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clear"></div>

                                <div class="option option-select option_w">
                                    <div class="option-inner">
                                        <label class="titledesc"><?php _e('Note:', 'dpProAppointments');?></label>
                                        <div class="formcontainer">
                                            <div class="forminp">
                                                <textarea cols="60" rows="10" class="dp_appointments_input_text" name="appointment_note" id="appointment_note"></textarea>
                                                <br>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clear"></div>

                                <div class="option option-select option_w">
                                    <div class="option-inner">
                                        <label class="titledesc"><?php _e('Status:', 'dpProAppointments');?></label>
                                        <div class="formcontainer">
                                            <div class="forminp">
                                                <select name="appointment_status" id="appointment_status">
                                                    <option value="publish"><?php _e('Active', 'dpProAppointments');?></option>
                                                    <option value="pending"><?php _e('Pending of approval', 'dpProAppointments');?></option>
                                                    <option value="completed"><?php _e('Completed', 'dpProAppointments');?></option>
                                                    <option value="canceled"><?php _e('Canceled', 'dpProAppointments');?></option>
                                                    <option value="pending_payment"><?php _e('Pending of Payment', 'dpProAppointments');?></option>
                                                </select>
                                                <br>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clear"></div>

                                <div class="submit">

                                <input type="submit" class="button-primary" value="<?php echo __('Submit', 'dpProAppointments') ?>" name="" onclick="" />

                                <input type="button" class="button-secondary" value="<?php echo __('Cancel', 'dpProAppointments') ?>" name="" onclick="jQuery('#appointment_frm')[0].reset(); jQuery('.new_appointment_form').slideUp('fast'); jQuery('.list_appointments').slideDown('slow');" />

                                </div>
                            </form>

						</div>
                        <div class="list_appointments">

                            <div class="submit">

                                <input type="button" class="button-primary" value="<?php echo __('Add New', 'dpProAppointments') ?>" name="" onclick="jQuery('.new_appointment_form').slideDown('slow'); jQuery('.list_appointments').slideUp('fast');" />

                                <input type="button" class="button-secondary" value="<?php echo __('Export Appointments', 'dpProAppointments') ?>" name="" onclick="location.href='<?php echo dpProAppointments_plugin_url('includes/export_appointments.php') . get_query_params_str_for_export_appointments() ?>'" />

                                <div style="float:right;" class="select-month">
                                    <select id="month-select">
<?php
for ($i = 1; $i <= 12; $i++) {
        $month = date('F', mktime(0, 0, 0, $i, 1, date('Y')));
        $selected = get_if_set($_GET, 'monthnum', date('n')) == $i ? 'selected' : '';
        echo "<option value='$i' $selected>$month</option>";
    }
    ?>
                                    </select>

                                    <select id="year-select">
<?php
$starting_year = date('Y', strtotime('-10 year'));
    $ending_year = date('Y', strtotime('+10 year'));
    for ($starting_year; $starting_year <= $ending_year; $starting_year++) {
        $selected = get_if_set($_GET, 'yearnum', date('o')) == $starting_year ? 'selected' : '';
        echo "<option value='$starting_year' $selected>$starting_year</option>";
    }
    ?>
                                    </select>
                                    <script>
                                    jQuery('#month-select, #year-select').change(function(data){
                                        var symbol = document.location.href.indexOf('?') !== -1 ? '&' : '?';
                                        var monthNum = jQuery('#month-select').first(':selected').val();
                                        var yearNum = jQuery('#year-select').first(':selected').val();                                        var monthNum = jQuery('#month-select').first(':selected').val();
                                        var url = document.location.href + symbol + "monthnum=" +  monthNum + "&yearnum=" + yearNum;
                                        document.location = url;
                                    });
                                    </script>
                                </div>
                            </div>

                           <table class="widefat" cellpadding="0" cellspacing="0" id="sort-table">
                                <thead>
                                    <tr style="cursor:default !important;">
                                        <th><?php _e('ID', 'dpProAppointments');?></th>
                                        <th><?php _e('Client', 'dpProAppointments');?></th>
                                        <th><?php _e('Service', 'dpProAppointments');?></th>
                                        <th><?php _e('Date / Time', 'dpProAppointments');?></th>
                                        <th><?php _e('Status', 'dpProAppointments');?></th>
                                        <th><?php _e('Actions', 'dpProAppointments');?></th>
                                     </tr>
                                </thead>
                                <tbody>

                            <?php
$args = array(
        'posts_per_page' => -1,
        'post_type' => 'pro-appointments',
        'post_status' => 'any',
        'meta_key' => 'appointment_creation',
        'orderby' => 'meta_value',
        'order' => 'DESC',
        'meta_query' => get_meta_query_between_dates(),
    );

    $appointments = get_posts($args);
    if (!is_array($appointments) || empty($appointments)) {

        echo '<tr>
                                        <td colspan="5">' . __('No appointments have been found.', 'dpProAppointments') . '</td>
                                    </tr>';

    } else {
        foreach ($appointments as $appointment) {
            $post_status = $appointment->post_status;
            switch ($appointment->post_status) {
                case 'publish':
                    $appointment->post_status = __('Active', 'dpProAppointments');
                    break;
                case 'pending':
                    $appointment->post_status = __('Pending of approval', 'dpProAppointments');
                    break;
                case 'completed':
                    $appointment->post_status = __('Completed', 'dpProAppointments');
                    break;
                case 'canceled':
                    $appointment->post_status = __('Canceled', 'dpProAppointments');
                    break;
                case 'pending_payment':
                    $appointment->post_status = __('Pending of Payment', 'dpProAppointments');
                    break;
            }

            $services = get_the_terms($appointment->ID, 'pro_appointments_service');
            $services_links = array();
            $services_ids = array();

            if (is_array($services)) {

                foreach ($services as $service) {

                    $services_ids[] = $service->term_id;
                    $services_links[] = $service->name;

                }

                $services = implode(", ", $services_links);

            } else {

                $services = __('Default', 'dpProAppointments');

            }

            echo '<tr id="' . $appointment->ID . '">
                                            <td width="5%">' . $appointment->ID . '</td>
                                            <td width="20%">' . get_post_meta($appointment->ID, 'appointment_name', true) . '</td>
											<td width="20%">' . $services . '</td>
                                            <td width="20%">' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), get_post_meta($appointment->ID, 'appointment_date', true)) . '</td>
                                            <td width="20%">' . $appointment->post_status . '</td>
                                            <td width="30%">

												<input type="hidden" class="edit_date" value="' . date_i18n('Y-m-d', get_post_meta($appointment->ID, 'appointment_date', true)) . '" />
												<input type="hidden" class="edit_time" value="' . date_i18n('H:i', get_post_meta($appointment->ID, 'appointment_date', true)) . '" />
												<input type="hidden" class="edit_service" value="' . implode(", ", $services_ids) . '" />
												<input type="hidden" class="edit_name" value="' . get_post_meta($appointment->ID, 'appointment_name', true) . '" />
												<input type="hidden" class="edit_email" value="' . get_post_meta($appointment->ID, 'appointment_email', true) . '" />
												<input type="hidden" class="edit_phone" value="' . get_post_meta($appointment->ID, 'appointment_phone', true) . '" />
												<input type="hidden" class="edit_city" value="' . get_post_meta($appointment->ID, 'appointment_city', true) . '" />
												<input type="hidden" class="edit_address" value="' . get_post_meta($appointment->ID, 'appointment_address', true) . '" />
												<input type="hidden" class="edit_note" value="' . get_post_meta($appointment->ID, 'appointment_note', true) . '" />
												<input type="hidden" class="edit_userid" value="' . get_post_meta($appointment->ID, 'appointment_userid', true) . '" />
												<input type="hidden" class="edit_status" value="' . $post_status . '" />

												<input type="hidden" class="edit_appointment_id" value="' . $appointment->ID . '" />

                                                <input type="button" value="' . __('Edit', 'dpProAppointments') . '" name="edit_appointment" class="button-secondary edit_appointment" />
                                                <input type="button" value="' . __('Delete', 'dpProAppointments') . '" name="delete_appointment" class="button-secondary" onclick="if(confirm(\'' . __('Are you sure?', 'dpProAppointments') . '\')) { location.href=\'' . admin_url('admin.php?page=dpProAppointments-admin&delete_appointment=' . $appointment->ID . '&noheader=true') . '\'; }" />
                                            </td>
                                        </tr>';
            $counter++;
        }
    }
    ?>

                                </tbody>
                                <tfoot>
                                    <tr style="cursor:default !important;">
                                        <th><?php _e('ID', 'dpProAppointments');?></th>
                                        <th><?php _e('Client', 'dpProAppointments');?></th>
                                        <th><?php _e('Service', 'dpProAppointments');?></th>
                                        <th><?php _e('Date / Time', 'dpProAppointments');?></th>
                                        <th><?php _e('Status', 'dpProAppointments');?></th>
                                        <th><?php _e('Actions', 'dpProAppointments');?></th>
                                     </tr>
                                </tfoot>
                            </table>

                            <div class="submit">

                            <input type="button" class="button-primary" value="<?php echo __('Add New', 'dpProAppointments') ?>" name="" id="add_new_appointment_btn" onclick="jQuery('.new_appointment_form').slideDown('slow'); jQuery('.list_appointments').slideUp('fast');" />

                            <input type="button" class="button-secondary" value="<?php echo __('Export Appointments', 'dpProAppointments') ?>" name="" onclick="location.href='<?php echo dpProAppointments_plugin_url('includes/export_appointments.php') . get_query_params_str_for_export_appointments() ?>'" />

                            </div>

                        </div>

                        <div class="clear"></div>


                </div>
            </div>
        </div>
        <div class="clear"></div>
    </div>

	<script type="text/javascript">
	jQuery(document).ready(function() {

		jQuery( ".appointment_date" ).datepicker({
			beforeShow: function(input, inst) {
			   jQuery("#ui-datepicker-div").removeClass("dp_appointments_datepicker");
			   jQuery("#ui-datepicker-div").addClass("dp_appointments_datepicker");
		   },
			showOn: "button",
			buttonImage: "<?php echo dpProAppointments_plugin_url('images/admin/calendar.png') ?>",
			buttonImageOnly: false,
			dateFormat: "yy-mm-dd"
		});

		jQuery('.edit_appointment').click(function() {

			jQuery('#add_new_appointment_btn').trigger('click');

			jQuery('#appointment_date').val(jQuery(this).parent().find('.edit_date').val());
			jQuery('#appointment_time option[value="'+jQuery(this).parent().find('.edit_time').val()+'"]').prop('selected', true);
			jQuery('#appointment_service option[value="'+jQuery(this).parent().find('.edit_service').val()+'"]').prop('selected', true);
			jQuery('#appointment_name').val(jQuery(this).parent().find('.edit_name').val());
			jQuery('#appointment_email').val(jQuery(this).parent().find('.edit_email').val());
			jQuery('#appointment_phone').val(jQuery(this).parent().find('.edit_phone').val());
			jQuery('#appointment_city').val(jQuery(this).parent().find('.edit_city').val());
			jQuery('#appointment_address').val(jQuery(this).parent().find('.edit_address').val());
			jQuery('#appointment_note').val(jQuery(this).parent().find('.edit_note').val());
			jQuery('#appointment_userid').val(jQuery(this).parent().find('.edit_userid').val());
			jQuery('#appointment_status option[value="'+jQuery(this).parent().find('.edit_status').val()+'"]').prop('selected', true);
			jQuery('#appointment_edit').val(jQuery(this).parent().find('.edit_appointment_id').val());

			jQuery('#appointment_time, #appointment_status, #appointment_service').selectric('refresh');

		});

	});
	</script>

    <?php

}
?>