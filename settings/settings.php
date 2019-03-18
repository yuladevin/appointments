<?php // Hook for adding admin menus
if ( is_admin() ){ // admin actions
  add_action('admin_menu', 'dpProAppointments_settings');
  add_action('admin_init', 'dpProAppointments_register_mysettings'); 
} 

// function for adding settings page to wp-admin
function dpProAppointments_settings() {
global $dpProAppointments, $current_user;

	if(!is_array($dpProAppointments['user_roles'])) { $dpProAppointments['user_roles'] = array(); }
	if(!in_array(dpProAppointments_get_user_role(), $dpProAppointments['user_roles']) && dpProAppointments_get_user_role() != "administrator" && !is_super_admin($current_user->ID)) { return; }
    // Add a new submenu under Options:
	add_menu_page( 'Appointments', __('Appointments', 'dpProAppointments'), 'edit_posts','dpProAppointments-admin', 'dpProAppointments_appointments_page', 'dashicons-admin-generic', '139.4' );
	add_submenu_page('dpProAppointments-admin', __('Appointments', 'dpProAppointments'), __('Appointments', 'dpProAppointments'), 'edit_posts', 'dpProAppointments-admin', 'dpProAppointments_appointments_page');
	add_submenu_page('dpProAppointments-admin', __('Categories', 'dpProAppointments'), __('Services', 'dpProAppointments'), 'edit_posts', 'edit-tags.php?taxonomy=pro_appointments_service');
	if(dpProAppointments_get_user_role() != 'editor' && dpProAppointments_get_user_role() != 'contributor' && dpProAppointments_get_user_role() != 'author') {
		add_submenu_page('dpProAppointments-admin', __('Settings', 'dpProAppointments'), __('Settings', 'dpProAppointments'), 'edit_posts', 'dpProAppointments-settings', 'dpProAppointments_settings_page');
		add_submenu_page('dpProAppointments-admin', __('Payment Options', 'dpProAppointments'), __('Payment Options', 'dpProAppointments'), 'edit_posts', 'dpProAppointments-payments', 'dpProAppointments_payments_page');
		add_submenu_page('dpProAppointments-admin', __('Custom Shortcodes', 'dpProAppointments'), __('Custom Shortcodes', 'dpProAppointments'), 'edit_posts', 'dpProAppointments-custom-shortcodes', 'dpProAppointments_custom_shortcodes_page');
	}
	
	//add_submenu_page('dpProAppointments-admin', __('Display Data in Event Page', 'dpProAppointments'), __('Display Data in Event Page', 'dpProAppointments'), 'edit_posts', 'dpProAppointments-eventdata', 'dpProAppointments_eventdata_page');
}

require_once (dirname (__FILE__) . '/appointments.php');
require_once (dirname (__FILE__) . '/payments.php');
require_once (dirname (__FILE__) . '/custom_shortcodes.php');

function dpProAppointments_get_user_role() {
	global $current_user;

	$user_roles = $current_user->roles;
	$user_role = array_shift($user_roles);

	return $user_role;
}

// This function displays the page content for the Settings submenu
function dpProAppointments_settings_page() {
global $dpProAppointments, $wpdb;

	if(!is_array($dpProAppointments['appointment_fields'])) {
		$dpProAppointments['appointment_fields'] = array();
	}
	
	if($dpProAppointments['appointment_email_template_user'] == '') {
		$dpProAppointments['appointment_email_template_user'] = "Hi #USERNAME#,\n\nThanks for set the appointment:\n\n#APPOINTMENT_DETAILS#\n\nPlease contact us if you have questions.\n\nKind Regards.\n#SITE_NAME#";
	}
	
	if($dpProAppointments['appointment_email_template_admin'] == '') {
		$dpProAppointments['appointment_email_template_admin'] = "The user #USERNAME# created an appointment:\n\n#APPOINTMENT_DETAILS#\n\n#COMMENT#\n\n#SITE_NAME#";
	}
	
	if(!isset($dpProAppointments['schedule_start_time'])) {
		$dpProAppointments['schedule_start_time'] = '08:00';	
	}
	
	if(!isset($dpProAppointments['schedule_end_time'])) {
		$dpProAppointments['schedule_end_time'] = '17:00';	
	}
?>

<div class="wrap" style="clear:both;" id="dp_options">

<h2></h2>
<?php $url = dpProAppointments_admin_url( array( 'page' => 'dpProAppointments-admin' ) );?>

<form method="post" id="dpProAppointments_events_meta" action="options.php" enctype="multipart/form-data">
<?php settings_fields('dpProAppointments-group'); ?>
<div style="clear:both;"></div>
 <!--end of poststuff --> 
	
    <div id="dp_ui_content">
    	
        <div id="leftSide">
        	<div id="dp_logo"></div>
            <p>
                Version: <?php echo DP_APPOINTMENTS_VER?><br />
            </p>
            <ul id="menu" class="nav">
                <li><a href="javascript:void(0);" class="active" title=""><span><?php _e('General Settings','dpProAppointments'); ?></span></a></li>
	            <li><a href="admin.php?page=dpProAppointments-admin" title=""><span><?php _e('Appointments','dpProAppointments'); ?></span></a></li>                
                <li><a href="admin.php?page=dpProAppointments-payments" title=""><span><?php _e('Payment Options','dpProAppointments'); ?></span></a></li>
                <li><a href="admin.php?page=dpProAppointments-custom-shortcodes" title=""><span><?php _e('Shortcode Generator','dpProAppointments'); ?></span></a></li>                
            </ul>
            
            <div class="clear"></div>
		</div>     
        
        <div id="rightSide">
        	<div id="menu_general_settings">
                <div class="titleArea">
                    <div class="wrapper">
                        <div class="pageTitle">
                            <h5><?php _e('General Settings','dpProAppointments'); ?></h5>
                            <span></span>
                        </div>
                        
                        <div class="clear"></div>
                    </div>
                </div>
                
                <div class="wrapper">
                
                    <div class="option option-select option_w">
                        <div class="option-inner">
                            <label class="titledesc"><?php _e('User Roles:','dpProAppointments'); ?></label>
                            <div class="formcontainer">
                                <div class="forminp">
                                    <select name='dpProAppointments_options[user_roles][]' multiple="multiple" class="multiple">
                                    	<option value=""><?php _e('None','dpProAppointments'); ?></option>
                                       <?php 
									   $user_roles = '';
                                       $editable_roles = get_editable_roles();

								       foreach ( $editable_roles as $role => $details ) {
								           $name = translate_user_role($details['name'] );
								           if(esc_attr($role) == "administrator" || esc_attr($role) == "subscriber") { continue; }
										   if ( in_array($role, $dpProAppointments['user_roles']) ) // preselect specified role
								               $user_roles .= "\n\t<option selected='selected' value='" . esc_attr($role) . "'>$name</option>";
								           else
								               $user_roles .= "\n\t<option value='" . esc_attr($role) . "'>$name</option>";
								       }
									   echo $user_roles;
									   ?>
                                    </select>
                                    <br>
                                </div>
                                <div class="desc"><?php _e('Select the user role that will manage the plugin.','dpProAppointments'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    
                    <div class="option option-select option_w">
                        <div class="option-inner">
                            <label class="titledesc"><?php _e('Custom CSS:','dpProAppointments'); ?></label>
                            <div class="formcontainer">
                                <div class="forminp">
                                    <textarea name='dpProAppointments_options[custom_css]' rows="10" placeholder=".classname {
	background: #333;
}"><?php echo $dpProAppointments['custom_css']?></textarea>
                                    <br>
                                </div>
                                <div class="desc"><?php _e('Add your custom CSS code.','dpProAppointments'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    
                    <div class="option option-select">
                        <div class="option-inner">
                            <label class="titledesc"><?php _e('First Day of the Week','dpProAppointments'); ?></label>
                            <div class="formcontainer">
                                <div class="forminp">
                                    <select name="dpProAppointments_options[first_day]" id="dpProAppointments_first_day" class="large-text">
                                    	<option value="0" <?php if($dpProAppointments['first_day'] == "0") { echo 'selected="selected"'; }?>><?php _e('Sunday','dpProAppointments'); ?></option>
                                        <option value="1" <?php if($dpProAppointments['first_day'] == "1") { echo 'selected="selected"'; }?>><?php _e('Monday','dpProAppointments'); ?></option>
                                    </select>
                                    <br>
                                </div>
                                <div class="desc"><?php _e('Select the first day to display in the schedule','dpProAppointments'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    
                    <div class="option option-select">
                        <div class="option-inner">
                            <label class="titledesc"><?php _e('Time Base','dpProAppointments'); ?></label>
                            <div class="formcontainer">
                                <div class="forminp">
                                    <select name="dpProAppointments_options[time_base]" id="dpProAppointments_time_base" class="large-text">
                                    	<option value="10" <?php if($dpProAppointments['time_base'] == "10") { echo 'selected="selected"'; }?>>10 <?php _e('Minutes','dpProAppointments'); ?></option>
                                        <option value="15" <?php if($dpProAppointments['time_base'] == "15") { echo 'selected="selected"'; }?>>15 <?php _e('Minutes','dpProAppointments'); ?></option>
                                        <option value="30" <?php if($dpProAppointments['time_base'] == "30" || empty($dpProAppointments['time_base'])) { echo 'selected="selected"'; }?>>30 <?php _e('Minutes','dpProAppointments'); ?></option>
                                        <option value="60" <?php if($dpProAppointments['time_base'] == "60") { echo 'selected="selected"'; }?>>60 <?php _e('Minutes','dpProAppointments'); ?></option>
                                        <option value="90" <?php if($dpProAppointments['time_base'] == "90") { echo 'selected="selected"'; }?>>90 <?php _e('Minutes','dpProAppointments'); ?></option>
                                        <option value="105" <?php if($dpProAppointments['time_base'] == "105") { echo 'selected="selected"'; }?>>105 <?php _e('Minutes','dpProAppointments'); ?></option>
                                        <option value="120" <?php if($dpProAppointments['time_base'] == "120") { echo 'selected="selected"'; }?>>120 <?php _e('Minutes','dpProAppointments'); ?></option>
                                    </select>
                                    
                                    <br>
                                </div>
                                <div class="desc"><?php _e('Select the time base for schedule intervals in minutes.','dpProAppointments'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    
                    <div class="option option-select">
                        <div class="option-inner">
                            <label class="titledesc"><?php _e('Max number of appointments','dpProAppointments'); ?></label>
                            <div class="formcontainer">
                                <div class="forminp">
                                    <input type="number" name="dpProAppointments_options[capacity]" id="dpProAppointments_capacity" class="large-text" width="100px;" min="0" max="9999" value="<?php echo $dpProAppointments['capacity']?>" />
                                    
                                    <br>
                                </div>
                                <div class="desc"><?php _e('Select the max number of appointments allowed per block of time. Set to 0 for no limits.','dpProAppointments'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    
                    <div class="option option-select">
                        <div class="option-inner">
                            <label class="titledesc"><?php _e('Start / End Time on schedule view','dpProAppointments'); ?></label>
                            <div class="formcontainer">
                                <div class="forminp">
                                    <select name="dpProAppointments_options[schedule_start_time]">
										<?php 
                                        for($hour = 0; $hour <= 23; $hour++) {
                                            for($min = 0; $min < 60; $min+=30) {
                                                $h_pad = str_pad($hour, 2, '0', STR_PAD_LEFT);
                                                $m_pad = str_pad($min, 2, '0', STR_PAD_LEFT);
                                            ?>
                                            
                                            <option value="<?php echo $h_pad.':'.$m_pad?>" <?php echo ($dpProAppointments['schedule_start_time'] == $h_pad.':'.$m_pad ? 'selected="selected"' : '')?>><?php echo date(get_option('time_format'), mktime($hour, $min))?></option>
                                            
                                        <?php }
                                        }?>
                                    </select>
                                    
                                    <select name="dpProAppointments_options[schedule_end_time]">
										<?php 
                                        for($hour = 0; $hour <= 23; $hour++) {
                                            for($min = 0; $min < 60; $min+=30) {
                                                $h_pad = str_pad($hour, 2, '0', STR_PAD_LEFT);
                                                $m_pad = str_pad($min, 2, '0', STR_PAD_LEFT);
                                            ?>
                                            
                                            <option value="<?php echo $h_pad.':'.$m_pad?>" <?php echo ($dpProAppointments['schedule_end_time'] == $h_pad.':'.$m_pad ? 'selected="selected"' : '')?>><?php echo date(get_option('time_format'), mktime($hour, $min))?></option>
                                            
                                        <?php }
                                        }?>
                                    </select>
                                    <br>
                                </div>
                                <div class="desc"><?php _e('Select the start / end time to display in the schedule view.','dpProAppointments'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    
                    <div class="option option-select">
                        <div class="option-inner">
                            <label class="titledesc"><?php _e('Block next time slots (hours)','dpProAppointments'); ?></label>
                            <div class="formcontainer">
                                <div class="forminp">
                                    <input type="number" name="dpProAppointments_options[block_hours]" id="dpProAppointments_block_hours" class="large-text" width="100px;" min="0" max="9999" value="<?php echo $dpProAppointments['block_hours']?>" />
                                    
                                    <br>
                                </div>
                                <div class="desc"><?php _e('Block time slots with the value set in reference to the current time, so users can\'t set an appointment in those hours. i.e: If you need 2 days before approving an event, you should set 48 hours. Default: 0.','dpProAppointments'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    
                    <div class="option option-select option_w">
                        <div class="option-inner">
                            <label class="titledesc"><?php _e('Approve Appointments automatically','dpProAppointments'); ?></label>
                            <div class="formcontainer">
                                <div class="forminp">
                                    <input type="checkbox" value="1" <?php echo ($dpProAppointments['approve_automatically'] ? "checked='checked'" : "")?> name='dpProAppointments_options[approve_automatically]' class="checkbox"/>
                                    <br>
                                </div>
                                <div class="desc"><?php _e('Appointments will be automatically approved.','dpProAppointments'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    
                    <div class="option option-select option_w">
                        <div class="option-inner">
                            <label class="titledesc"><?php _e('Terms & Conditions Page','dpProAppointments'); ?></label>
                            <div class="formcontainer">
                                <div class="forminp">
                                    <select name="dpProAppointments_options[terms_conditions]">
                                    	<option value=""></option>
                                        <?php 
										  $pages = get_pages(); 
										  foreach ( $pages as $page ) {
											$option = '<option value="' . $page->ID . '" ' . ($page->ID == $dpProAppointments['terms_conditions'] ? 'selected="selected"' : '') . '>';
											$option .= $page->post_title;
											$option .= '</option>';
											echo $option;
										  }
										 ?>
                                    </select>
                                    <br>
                                </div>
                                <div class="desc"><?php _e('Select the Terms & Conditions page for new appointments','dpProAppointments'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    
                    <div class="option option-select option_w">
                        <div class="option-inner">
                            <label class="titledesc"><?php _e('Appointment Fields','dpProAppointments'); ?></label>
                            <div class="formcontainer">
                                <div class="forminp">
                                    <input type="checkbox" value="city" <?php echo (in_array('city', $dpProAppointments['appointment_fields']) ? "checked='checked'" : "")?> name='dpProAppointments_options[appointment_fields][]' class="checkbox"/> <?php _e('City', 'dpProAppointments')?>
                                    <br>
                                    <input type="checkbox" value="address" <?php echo (in_array('address', $dpProAppointments['appointment_fields']) ? "checked='checked'" : "")?> name='dpProAppointments_options[appointment_fields][]' class="checkbox"/> <?php _e('Address', 'dpProAppointments')?>
                                    <br>
                                    <input type="checkbox" value="note" <?php echo (in_array('note', $dpProAppointments['appointment_fields']) ? "checked='checked'" : "")?> name='dpProAppointments_options[appointment_fields][]' class="checkbox"/> <?php _e('Note', 'dpProAppointments')?>
                                    <br>
                                </div>
                                <div class="desc"><?php _e('Select the fields to display in the appointment form.','dpProAppointments'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="clear">
                    
                    <div class="option option-checkbox">
                        <div class="option-inner">
                            <label class="titledesc"><?php _e('Allow not logged in users to set an appointment','dpProAppointments'); ?></label>
                            <div class="formcontainer">
                                <div class="forminp">
                                    <input type="checkbox" name="dpProAppointments_options[appointment_not_logged]" class="checkbox" value="1" <?php if($dpProAppointments['appointment_not_logged']) {?>checked="checked" <?php }?> />
                                    <br>
                                </div>
                                <div class="desc"><?php _e('A Full name and email field will be required in the appointment form.','dpProAppointments'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    
                    <div class="option option-select option_w">
                        <div class="option-inner">
                            <label class="titledesc"><?php _e('Email address to send notifications from:','dpProAppointments'); ?></label>
                            <div class="formcontainer">
                                <div class="forminp">
                                    <input type="text" value="<?php echo $dpProAppointments['wp_mail_from']?>" name='dpProAppointments_options[wp_mail_from]' class="large-text" placeholder="wordpress@<?php echo str_replace("www.", "", $_SERVER['HTTP_HOST'])?>"/>
                                    <br>
                                </div>
                                <div class="desc"></div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    
                    <div class="option option-select option_w">
                        <div class="option-inner">
                            <label class="titledesc"><?php _e('Email that will receive the user after creating an appointment','dpProAppointments'); ?></label>
                            <div class="formcontainer">
                                <div class="forminp">
                                    <textarea cols="20" rows="5" name='dpProAppointments_options[appointment_email_template_user]'><?php echo $dpProAppointments['appointment_email_template_user']?></textarea>
                                    <br>
                                </div>
                                <div class="desc"><?php _e('Template of the email that will receive the user. Use the reserved tags to display dynamic data.','dpProAppointments'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    
                    <div class="option option-select option_w">
                        <div class="option-inner">
                            <label class="titledesc"><?php _e('Email that will receive the admin when a user creates an appointment','dpProAppointments'); ?></label>
                            <div class="formcontainer">
                                <div class="forminp">
                                    <textarea cols="20" rows="5" name='dpProAppointments_options[appointment_email_template_admin]'><?php echo $dpProAppointments['appointment_email_template_admin']?></textarea>
                                    <br>
                                </div>
                                <div class="desc"><?php _e('Template of the email that will receive the admin. Use the reserved tags to display dynamic data.','dpProAppointments'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    
                    <!--
                    <div class="option option-select option_w">
                        <div class="option-inner">
                            <label class="titledesc"><?php _e('RTL (Right-to-left) Support','dpProAppointments'); ?></label>
                            <div class="formcontainer">
                                <div class="forminp">
                                    <input type="checkbox" value="1" <?php echo ($dpProAppointments['rtl_support'] ? "checked='checked'" : "")?> name='dpProAppointments_options[rtl_support]' class="checkbox"/>
                                    <br>
                                </div>
                                <div class="desc"><?php _e('Add RTL support for the calendars.','dpProAppointments'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>-->
                    
                    <h2 class="subtitle accordion_title" onclick="showAccordionAppointments('div_reminders');"><?php _e('Reminders','dpProAppointments'); ?></h2>
                    
                    <div id="div_reminders" style="display:none;">
                    	
                        <div class="option option-select option_w">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Enable Reminders','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <input type="checkbox" value="1" <?php echo ($dpProAppointments['reminders_enable'] ? "checked='checked'" : "")?> name='dpProAppointments_options[reminders_enable]' class="checkbox"/>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('enable reminders for appointments.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="option option-select">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Days of anticipation','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <input type="number" name="dpProAppointments_options[reminder_anticipation]" id="dpProAppointments_reminder_anticipation" class="large-text" width="100px;" min="0" max="999" value="<?php echo (is_numeric($dpProAppointments['reminder_anticipation']) ? $dpProAppointments['reminder_anticipation'] : 1) ?>" />
                                        
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Number of days before the appointment to send the reminder. Default: 1.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="option option-select option_w">
                        <?php if($dpProAppointments['appointment_reminder_template_user'] == "") {
							$dpProAppointments['appointment_reminder_template_user'] = "Hi #USERNAME#,\n\nWe would like to remind you that the day #APPOINTMENT_DATE# you have an appointment.\n\nKind Regards.\n#SITE_NAME#";;
						}
						?>
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Email that will receive the user as the appointment reminder','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <textarea cols="20" rows="5" name='dpProAppointments_options[appointment_reminder_template_user]'><?php echo $dpProAppointments['appointment_reminder_template_user']?></textarea>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Template of the email that will receive the user. Use the reserved tags to display dynamic data.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                    </div>
                    
                    <h2 class="subtitle accordion_title" onclick="showAccordionAppointments('div_working_days');"><?php _e('Working Days / Hours','dpProAppointments'); ?></h2>
                    
                    <div id="div_working_days" style="display:none;">
                    
                    	<div class="option option-select no_border" id="list-service">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Service','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <select name="" id="" onchange="changeServiceWorkingDays(this.value);">
	                                        <option value=""><?php _e('Default','dpProAppointments'); ?></option>
                                        	<?php
											$tax_terms = get_terms('pro_appointments_service', array('hide_empty' => false));
											
											if(is_array($tax_terms)) {
											foreach ($tax_terms as $tax_term) {
											?>
                                            	<option value="<?php echo $tax_term->term_id?>"><?php echo $tax_term->name ?></option>
                                            <?php }
											}?>
                                        </select>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Select a service to set the Working Days.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                    	
                        <div class="working_days_default working_days_divs">
                        
                            <table class="widefat" cellpadding="0" cellspacing="0" id="">
                            <thead>
                                <tr style="cursor:default !important;">
                                    <th><?php _e('Day','dpProAppointments'); ?></th>
                                    <th><?php _e('Working','dpProAppointments'); ?></th>
                                    <th><?php _e('Start','dpProAppointments'); ?></th>
                                    <th><?php _e('End','dpProAppointments'); ?></th>
                                 </tr>
                            </thead>
                            <tbody>
                            
                            <?php
                                $daynames = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
    
                                foreach($daynames as $key) {
                            ?>
                            
                                <tr id="">
                                    
                                    <td>
                                        <?php _e($key, 'dpProAppointments')?>
                                    </td>
                                    
                                    <td>
                                        <select name="dpProAppointments_options[working_days][<?php echo $key?>][work]">
                                            <option value="1" <?php echo ($dpProAppointments['working_days'][$key]['work'] == 1 ? 'selected="selected"' : '')?>><?php _e('Yes', 'dpProAppointments')?></option>
                                            <option value="0" <?php echo ($dpProAppointments['working_days'][$key]['work'] == 0 ? 'selected="selected"' : '')?>><?php _e('No', 'dpProAppointments')?></option>
                                        </select>
                                    </td>
                                    
                                    <td>
                                        <select name="dpProAppointments_options[working_days][<?php echo $key?>][start]">
                                            <?php 
                                            for($hour = 0; $hour <= 23; $hour++) {
                                                for($min = 0; $min < 60; $min+=30) {
                                                    $h_pad = str_pad($hour, 2, '0', STR_PAD_LEFT);
                                                    $m_pad = str_pad($min, 2, '0', STR_PAD_LEFT);
                                                ?>
                                                
                                                <option value="<?php echo $h_pad.':'.$m_pad?>" <?php echo ($dpProAppointments['working_days'][$key]['start'] == $h_pad.':'.$m_pad ? 'selected="selected"' : '')?>><?php echo date(get_option('time_format'), mktime($hour, $min))?></option>
                                                
                                            <?php }
                                            }?>
                                        </select>
                                    </td>
                                    
                                    <td>
                                        <select name="dpProAppointments_options[working_days][<?php echo $key?>][end]">
                                            <?php 
                                            for($hour = 0; $hour <= 23; $hour++) {
                                                for($min = 0; $min < 60; $min+=30) {
                                                    $h_pad = str_pad($hour, 2, '0', STR_PAD_LEFT);
                                                    $m_pad = str_pad($min, 2, '0', STR_PAD_LEFT);
                                                ?>
                                                
                                                <option value="<?php echo $h_pad.':'.$m_pad?>" <?php echo ($dpProAppointments['working_days'][$key]['end'] == $h_pad.':'.$m_pad ? 'selected="selected"' : '')?>><?php echo date(get_option('time_format'), mktime($hour, $min))?></option>
                                                
                                            <?php }
                                            }?>
                                        </select>
                                    </td>
                                    
                                </tr>
                                <?php }?>
                            </tbody>
                            <tfoot>
                                <tr style="cursor:default !important;">
                                    <th><?php _e('Day','dpProAppointments'); ?></th>
                                    <th><?php _e('Working','dpProAppointments'); ?></th>
                                    <th><?php _e('Start','dpProAppointments'); ?></th>
                                    <th><?php _e('End','dpProAppointments'); ?></th>
                                 </tr>
                            </tfoot>
                        </table>
                        
                        <h4><?php _e('Exceptions', 'dpProAppointments')?></h4>
                        
                        <?php _e('Add specific days that users won\'t be able to set any appointment, like holidays. Format YYYY-MM-DD. Add multiple days separated by comma. i.e: 2015-12-24,2015-12-25', 'dpProAppointments')?>
                        
                        <input type="text" value="<?php echo $dpProAppointments['exceptions']?>" placeholder="2015-12-24,2015-12-25" name="dpProAppointments_options[exceptions]">
					</div>
                                        
                    <?php
					if(is_array($tax_terms)) {
						foreach ($tax_terms as $tax_term) {
					?>
                    	<div class="working_days_service_<?php echo $tax_term->term_id?> working_days_divs" style="display:none;">
                        
                            <table class="widefat" cellpadding="0" cellspacing="0" id="">
                            <thead>
                                <tr style="cursor:default !important;">
                                    <th><?php _e('Day','dpProAppointments'); ?></th>
                                    <th><?php _e('Working','dpProAppointments'); ?></th>
                                    <th><?php _e('Start','dpProAppointments'); ?></th>
                                    <th><?php _e('End','dpProAppointments'); ?></th>
                                 </tr>
                            </thead>
                            <tbody>
                            
                            <?php
                                $daynames = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
    
                                foreach($daynames as $key) {
                            ?>
                            
                                <tr id="">
                                    
                                    <td>
                                        <?php _e($key, 'dpProAppointments')?>
                                    </td>
                                    
                                    <td>
                                        <select name="dpProAppointments_options[service_<?php echo $tax_term->term_id?>][working_days][<?php echo $key?>][work]">
                                            <option value="1" <?php echo ($dpProAppointments['service_'.$tax_term->term_id]['working_days'][$key]['work'] == 1 ? 'selected="selected"' : '')?>><?php _e('Yes', 'dpProAppointments')?></option>
                                            <option value="0" <?php echo ($dpProAppointments['service_'.$tax_term->term_id]['working_days'][$key]['work'] == 0 ? 'selected="selected"' : '')?>><?php _e('No', 'dpProAppointments')?></option>
                                        </select>
                                    </td>
                                    
                                    <td>
                                        <select name="dpProAppointments_options[service_<?php echo $tax_term->term_id?>][working_days][<?php echo $key?>][start]">
                                            <?php 
                                            for($hour = 0; $hour <= 23; $hour++) {
                                                for($min = 0; $min < 60; $min+=30) {
                                                    $h_pad = str_pad($hour, 2, '0', STR_PAD_LEFT);
                                                    $m_pad = str_pad($min, 2, '0', STR_PAD_LEFT);
                                                ?>
                                                
                                                <option value="<?php echo $h_pad.':'.$m_pad?>" <?php echo ($dpProAppointments['service_'.$tax_term->term_id]['working_days'][$key]['start'] == $h_pad.':'.$m_pad ? 'selected="selected"' : '')?>><?php echo date(get_option('time_format'), mktime($hour, $min))?></option>
                                                
                                            <?php }
                                            }?>
                                        </select>
                                    </td>
                                    
                                    <td>
                                        <select name="dpProAppointments_options[service_<?php echo $tax_term->term_id?>][working_days][<?php echo $key?>][end]">
                                            <?php 
                                            for($hour = 0; $hour <= 23; $hour++) {
                                                for($min = 0; $min < 60; $min+=30) {
                                                    $h_pad = str_pad($hour, 2, '0', STR_PAD_LEFT);
                                                    $m_pad = str_pad($min, 2, '0', STR_PAD_LEFT);
                                                ?>
                                                
                                                <option value="<?php echo $h_pad.':'.$m_pad?>" <?php echo ($dpProAppointments['service_'.$tax_term->term_id]['working_days'][$key]['end'] == $h_pad.':'.$m_pad ? 'selected="selected"' : '')?>><?php echo date(get_option('time_format'), mktime($hour, $min))?></option>
                                                
                                            <?php }
                                            }?>
                                        </select>
                                    </td>
                                    
                                </tr>
                                <?php }?>
                            </tbody>
                            <tfoot>
                                <tr style="cursor:default !important;">
                                    <th><?php _e('Day','dpProAppointments'); ?></th>
                                    <th><?php _e('Working','dpProAppointments'); ?></th>
                                    <th><?php _e('Start','dpProAppointments'); ?></th>
                                    <th><?php _e('End','dpProAppointments'); ?></th>
                                 </tr>
                            </tfoot>
                        </table>
                        
                        <h4><?php _e('Exceptions', 'dpProAppointments')?></h4>
                        
                        <?php _e('Add specific days that users won\'t be able to set any appointment, like holidays. Format YYYY-MM-DD. Add multiple days separated by comma. i.e: 2015-12-24,2015-12-25', 'dpProAppointments')?>
                        
                        <input type="text" value="<?php echo $dpProAppointments['service_'.$tax_term->term_id]['exceptions']?>" placeholder="2015-12-24,2015-12-25" name="dpProAppointments_options[service_<?php echo $tax_term->term_id?>][exceptions]">
					</div>
                    
                    <?php 
						}
					}?>
                    </div>
                    
                </div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
	
    <p align="right">
		<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
</form>

<script type="text/javascript">
function changeServiceWorkingDays(value) {
	jQuery('.working_days_divs').hide();
	
	if(value != "") {
		jQuery('.working_days_service_'+value).show();
	} else {
		jQuery('.working_days_default').show();
	}
}
</script>
                    
</div> <!--end of float wrap -->


<?php	
}
function dpProAppointments_register_mysettings() { // whitelist options
  register_setting( 'dpProAppointments-group', 'dpProAppointments_options', 'dpProAppointments_validate' );
}

function dpProAppointments_validate($input) {
	global $dpProAppointments;
	
	if(!$input['rtl_support']) 
		$input['rtl_support'] = 0;
	
	if(!$input['approve_automatically']) 
		$input['approve_automatically'] = 0;
		
	if(isset($input['currency'])) {
		if(!$input['paypal_enable']) 
			$input['paypal_enable'] = 0;
			
		if(!$input['paypal_testmode']) 
			$input['paypal_testmode'] = 0;
			
		if(!$input['stripe_enable']) 
			$input['stripe_enable'] = 0;
			
		if(!$input['stripe_testmode']) 
			$input['stripe_testmode'] = 0;
	}

	if(!$input['appointment_fields']) 
		$input['appointment_fields'] = 0;	
		
	if(!$input['appointment_not_logged']) 
		$input['appointment_not_logged'] = 0;	
		
	if(is_array($input['appointment_fields'])) {
		$dpProAppointments['appointment_fields'] = array();
	}
	
	$input = dpProAppointments_array_merge($dpProAppointments, $input);
    return $input;
}

function dpProAppointments_array_merge($paArray1, $paArray2)
{
    if (!is_array($paArray1) or !is_array($paArray2)) { return $paArray2; }
    foreach ($paArray2 AS $sKey2 => $sValue2)
    {
		if($sKey2 == "user_roles") {
			$paArray1[$sKey2] = array(); 	
		}
        $paArray1[$sKey2] = dpProAppointments_array_merge(@$paArray1[$sKey2], $sValue2);
    }
    return $paArray1;
}
?>