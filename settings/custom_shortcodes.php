<?php
// This function displays the admin page content
function dpProAppointments_custom_shortcodes_page() {
	global $wpdb, $table_prefix;

?>

    <div class="wrap" style="clear:both;" id="dp_options">
    
    <h2></h2>
    <div style="clear:both;"></div>
     <!--end of poststuff --> 
        <div id="dp_ui_content">
            
            <div id="leftSide">
                <div id="dp_logo"></div>
                <p>
                    <?php _e('Version:','dpProAppointments'); ?> <?php echo DP_APPOINTMENTS_VER?><br />
                </p>
            <ul id="menu" class="nav">
                <li><a href="admin.php?page=dpProAppointments-settings" title=""><span><?php _e('General Settings','dpProAppointments'); ?></span></a></li>
	            <li><a href="admin.php?page=dpProAppointments-admin" title=""><span><?php _e('Appointments','dpProAppointments'); ?></span></a></li>
                <li><a href="admin.php?page=dpProAppointments-payments" title=""><span><?php _e('Payment Options','dpProAppointments'); ?></span></a></li>                
                <li><a href="javascript:void(0);" class="active" title=""><span><?php _e('Shortcode Generator','dpProAppointments'); ?></span></a></li>                
            </ul>
                
                <div class="clear"></div>
            </div>     
            
            <div id="rightSide">
                <div id="menu_general_settings">
                    <div class="titleArea">
                        <div class="wrapper">
                            <div class="pageTitle">
                                <h2><?php _e('Shortcode Generator','dpProAppointments'); ?></h2>
                                <span><?php _e('Get a custom shortcode and paste it in your post / page. If needed, you can place a shortcode inside the template php files using this code i.e: ','dpProAppointments'); ?> <i><?php echo htmlentities('<?php echo do_shortcode(\'[dpProAppointments]\')?>')?></i></span>
                            </div>
                            
                            <div class="clear"></div>
                        </div>
                    </div>
                    
                    <div class="wrapper">
                        
                        <div class="option option-select">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Layout','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <select name="appointments_custom_shortcode_layout" id="appointments_custom_shortcode_layout" onchange="appointments_updateShortcode();">
											<option value=""><?php _e('Default','dpProAppointments'); ?></option>
                                            <option value="my-appointments"><?php _e('My Appointments','dpProAppointments'); ?></option>
                                        </select>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Select a layout type.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                                                
                        <div class="option option-select" id="list-authors" style="display:none;">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Provider','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <select name="appointments_custom_shortcode_authors" id="appointments_custom_shortcode_authors" onchange="appointments_updateShortcode();">
                                            <option value="current"><?php _e('Current logged in user','dpProAppointments'); ?></option>
											<?php 
											$blogusers = get_users('who=authors');
											foreach ($blogusers as $user) {
												echo '<option value="'.$user->ID.'">' . $user->display_name . ' ('.$user->user_nicename.')</option>';
											}?>
                                        </select>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Select a provider.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="option option-select" id="list-service">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Service','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                    	<?php
										$tax_terms = get_terms('pro_appointments_service', array('hide_empty' => false));
										
										foreach ($tax_terms as $tax_term) {
										?>
                                        	<input type="checkbox" name="appointments_custom_shortcode_service" class="appointments_custom_shortcode_service" onclick="appointments_updateShortcode();" value="<?php echo $tax_term->term_id?>" /> <?php echo $tax_term->name ?><br>
                                        <?php }?>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Select a service to filter the appointments.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="option option-select" id="list-skin">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Skin color','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <select name="appointments_custom_shortcode_skin" id="appointments_custom_shortcode_skin" onchange="appointments_updateShortcode();">
                                            <option value=""><?php _e('None','dpProAppointments'); ?></option>
                                            <option value="red"><?php _e('Red','dpProAppointments'); ?></option>
                                            <option value="pink"><?php _e('Pink','dpProAppointments'); ?></option>
                                            <option value="purple"><?php _e('Purple','dpProAppointments'); ?></option>
                                            <option value="deep_purple"><?php _e('Deep Purple','dpProAppointments'); ?></option>
                                            <option value="indigo"><?php _e('Indigo','dpProAppointments'); ?></option>
                                            <option value="blue"><?php _e('Blue','dpProAppointments'); ?></option>
                                            <option value="light_blue"><?php _e('Light Blue','dpProAppointments'); ?></option>
                                            <option value="cyan"><?php _e('Cyan','dpProAppointments'); ?></option>
                                            <option value="teal"><?php _e('Teal','dpProAppointments'); ?></option>
                                            <option value="green"><?php _e('Green','dpProAppointments'); ?></option>
                                            <option value="light_green"><?php _e('Light Green','dpProAppointments'); ?></option>
                                            <option value="lime"><?php _e('Lime','dpProAppointments'); ?></option>
                                            <option value="yellow"><?php _e('Yellow','dpProAppointments'); ?></option>
                                            <option value="amber"><?php _e('Amber','dpProAppointments'); ?></option>
                                            <option value="orange"><?php _e('Orange','dpProAppointments'); ?></option>
                                            <option value="deep_orange"><?php _e('Deep Orange','dpProAppointments'); ?></option>
                                            <option value="brown"><?php _e('Brown','dpProAppointments'); ?></option>
                                            <option value="grey"><?php _e('Grey','dpProAppointments'); ?></option>
                                            <option value="blue_grey"><?php _e('Blue Grey','dpProAppointments'); ?></option>
                                        </select>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Select the skin color for this layout.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="option option-select" id="list-skin">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Show Service Name','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <select name="appointments_custom_shortcode_service_name" id="appointments_custom_shortcode_service_name" onchange="appointments_updateShortcode();">
                                            <option value=""><?php _e('Yes','dpProAppointments'); ?></option>
                                            <option value="0"><?php _e('No','dpProAppointments'); ?></option>
                                        </select>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Display the service name in the selected layout.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="option option-select" id="list-skin">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Show Week Navigation','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <select name="appointments_custom_shortcode_week_nav" id="appointments_custom_shortcode_week_nav" onchange="appointments_updateShortcode();">
                                            <option value=""><?php _e('Yes','dpProAppointments'); ?></option>
                                            <option value="0"><?php _e('No','dpProAppointments'); ?></option>
                                        </select>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Display the week navigation in the selected layout.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="option option-select" id="list-skin">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Show Tooltips','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <select name="appointments_custom_shortcode_tooltip" id="appointments_custom_shortcode_tooltip" onchange="appointments_updateShortcode();">
                                            <option value=""><?php _e('Yes','dpProAppointments'); ?></option>
                                            <option value="0"><?php _e('No','dpProAppointments'); ?></option>
                                        </select>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Display the tooltips in the selected layout.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                                                
                        <div class="option option-select" id="limit-param" style="display:none;">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Limit','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
	                                    
                                        <input type="number" min="1" max="99" name="appointments_custom_shortcode_limit" id="appointments_custom_shortcode_limit" value="5" onchange="appointments_updateShortcode();" />
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Select a limit of posts to display.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="submit">
                        
                            <span class="appointments_custom_shortcode"></span> 

                            <div class="clear"></div>
                            
                            <p class="appointments_custom_shortcode_help"></p>
                            
                            <div id="appointments_custom_shortcode_preview"></div>
                        </div>
                    </div>
                </div>           
            </div>
        </div>
                    
</div> <!--end of float wrap -->

<script type="text/javascript">
	function appointments_updateShortcode() {
		var shortcode = '[dpProAppointments';
		
		jQuery('#list-authors').hide();
		jQuery('#limit-param').hide();
		
		if(jQuery('#appointments_custom_shortcode_layout').val() != "") {
			shortcode += ' type="'+jQuery('#appointments_custom_shortcode_layout').val()+'"';
		}
		
		if(jQuery('#appointments_custom_shortcode_skin').val() != "") {
			shortcode += ' skin="'+jQuery('#appointments_custom_shortcode_skin').val()+'"';
		}
		
        var services_list = "";
        jQuery( "input.appointments_custom_shortcode_service:checked" ).each(function() {
            if(services_list != "") { services_list += ','; }

            services_list += jQuery(this).val();
        });

		if(services_list != "") {
			shortcode += ' service="'+services_list+'"';
		}
		
		if(jQuery('#appointments_custom_shortcode_service_name').val() != "") {
			shortcode += ' show_service_name="'+jQuery('#appointments_custom_shortcode_service_name').val()+'"';
		}
		
		if(jQuery('#appointments_custom_shortcode_week_nav').val() != "") {
			shortcode += ' show_week_nav="'+jQuery('#appointments_custom_shortcode_week_nav').val()+'"';
		}
		
		if(jQuery('#appointments_custom_shortcode_tooltip').val() != "") {
			shortcode += ' show_tooltip="'+jQuery('#appointments_custom_shortcode_tooltip').val()+'"';
		}
		
		shortcode += ']';
		
		jQuery('.appointments_custom_shortcode').text(shortcode);
	};
	
	appointments_updateShortcode();
</script>


<?php
}
?>