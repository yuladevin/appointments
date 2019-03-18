<?php // Hook for adding admin menus

function dpProAppointments_payments_page() {
global $dpProAppointments, $wpdb;
?>

<div class="wrap" style="clear:both;" id="dp_options">

<h2></h2>

<form method="post" id="" action="options.php" enctype="multipart/form-data">
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
                <li><a href="admin.php?page=dpProAppointments-settings" title=""><span><?php _e('General Settings','dpProAppointments'); ?></span></a></li>
	            <li><a href="admin.php?page=dpProAppointments-admin" title=""><span><?php _e('Appointments','dpProAppointments'); ?></span></a></li>
                <li><a href="javascript:void(0);" class="active" title=""><span><?php _e('Payment Options','dpProAppointments'); ?></span></a></li>                
                <li><a href="admin.php?page=dpProAppointments-custom-shortcodes" title=""><span><?php _e('Shortcode Generator','dpProAppointments'); ?></span></a></li>                
            </ul>
            
            <div class="clear"></div>
		</div>     
        
        <div id="rightSide">
        	<div id="menu_general_settings">
                <div class="titleArea">
                    <div class="wrapper">
                        <div class="pageTitle">
                            <h5><?php _e('Payment Options','dpProAppointments'); ?></h5>
                            <span></span>
                        </div>
                        
                        <div class="clear"></div>
                    </div>
                </div>
                
                <div class="wrapper">
					
                    <?php if(!$dpProAppointments['paypal_enable'] && !$dpProAppointments['stripe_enable'] && $dpProAppointments['price'] > 0) {?>
                    <div class="appointments_admin_errorCustom" style="float: left;"><p><?php _e('You must enable at least one payment method.','dpProAppointments'); ?></p></div>
                    <?php }?>
                    
                    <div class="option option-select option_w">
                        <div class="option-inner">
                            <label class="titledesc"><?php _e('Currency','dpProAppointments'); ?></label>
                            <div class="formcontainer">
                                <div class="forminp">
                                    <select name="dpProAppointments_options[currency]">
                                    	<option value="AUD" <?php echo ($dpProAppointments['currency'] == 'AUD' ? "selected='selected'" : "")?>><?php _e('Australian Dollars ($)','dpProAppointments'); ?></option>
                                        <option value="BRL" <?php echo ($dpProAppointments['currency'] == 'BRL' ? "selected='selected'" : "")?>><?php _e('Brazilian Real (R$)','dpProAppointments'); ?></option>
                                        <option value="CAD" <?php echo ($dpProAppointments['currency'] == 'CAD' ? "selected='selected'" : "")?>><?php _e('Canadian Dollars ($)','dpProAppointments'); ?></option>
                                        <option value="RMB" <?php echo ($dpProAppointments['currency'] == 'RMB' ? "selected='selected'" : "")?>><?php _e('Chinese Yuan (¥)','dpProAppointments'); ?></option>
                                        <option value="CZK" <?php echo ($dpProAppointments['currency'] == 'CZK' ? "selected='selected'" : "")?>><?php _e('Czech Koruna (Kč)','dpProAppointments'); ?></option>
                                        <option value="DKK" <?php echo ($dpProAppointments['currency'] == 'DKK' ? "selected='selected'" : "")?>><?php _e('Danish Krone (kr)','dpProAppointments'); ?></option>
                                        <option value="EUR" <?php echo ($dpProAppointments['currency'] == 'EUR' ? "selected='selected'" : "")?>><?php _e('Euros (€)','dpProAppointments'); ?></option>
										
                                        <option value="HKD" <?php echo ($dpProAppointments['currency'] == 'HKD' ? "selected='selected'" : "")?>><?php _e('Hong Kong Dollar ($)','dpProAppointments'); ?></option>
                                        <option value="HUF" <?php echo ($dpProAppointments['currency'] == 'HUF' ? "selected='selected'" : "")?>><?php _e('Hungarian Forint (Ft)','dpProAppointments'); ?></option>
                                        <option value="IDR" <?php echo ($dpProAppointments['currency'] == 'IDR' ? "selected='selected'" : "")?>><?php _e('Indonesia Rupiah (Rp)','dpProAppointments'); ?></option>
                                        <option value="INR" <?php echo ($dpProAppointments['currency'] == 'INR' ? "selected='selected'" : "")?>><?php _e('Indian Rupee (₹)','dpProAppointments'); ?></option>
                                        <option value="ILS" <?php echo ($dpProAppointments['currency'] == 'ILS' ? "selected='selected'" : "")?>><?php _e('Israeli Shekel (₪)','dpProAppointments'); ?></option>
                                        <option value="JPY" <?php echo ($dpProAppointments['currency'] == 'JPY' ? "selected='selected'" : "")?>><?php _e('Japanese Yen (¥)','dpProAppointments'); ?></option>
                                        <option value="KRW" <?php echo ($dpProAppointments['currency'] == 'KRW' ? "selected='selected'" : "")?>><?php _e('South Korean Won (₩)','dpProAppointments'); ?></option>
                                        <option value="MYR" <?php echo ($dpProAppointments['currency'] == 'MYR' ? "selected='selected'" : "")?>><?php _e('Malaysian Ringgits (RM)','dpProAppointments'); ?></option>
                                        <option value="MXN" <?php echo ($dpProAppointments['currency'] == 'MXN' ? "selected='selected'" : "")?>><?php _e('Mexican Peso ($)','dpProAppointments'); ?></option>
                                        <option value="NOK" <?php echo ($dpProAppointments['currency'] == 'NOK' ? "selected='selected'" : "")?>><?php _e('Norwegian Krone (kr)','dpProAppointments'); ?></option>
                                        <option value="NZD" <?php echo ($dpProAppointments['currency'] == 'NZD' ? "selected='selected'" : "")?>><?php _e('New Zealand Dollar ($)','dpProAppointments'); ?></option>
                                        <option value="PHP" <?php echo ($dpProAppointments['currency'] == 'PHP' ? "selected='selected'" : "")?>><?php _e('Philippine Pesos (₱)','dpProAppointments'); ?></option>
                                        <option value="PLN" <?php echo ($dpProAppointments['currency'] == 'PLN' ? "selected='selected'" : "")?>><?php _e('Polish Zloty (zł)','dpProAppointments'); ?></option>
                                        <option value="GBP" <?php echo ($dpProAppointments['currency'] == 'GBP' ? "selected='selected'" : "")?>><?php _e('Pounds Sterling (£)','dpProAppointments'); ?></option>
                                        <option value="RON" <?php echo ($dpProAppointments['currency'] == 'RON' ? "selected='selected'" : "")?>><?php _e('Romanian Leu (lei)','dpProAppointments'); ?></option>
                                        <option value="RUB" <?php echo ($dpProAppointments['currency'] == 'RUB' ? "selected='selected'" : "")?>><?php _e('Russian Ruble (руб.)','dpProAppointments'); ?></option>
                                        <option value="SGD" <?php echo ($dpProAppointments['currency'] == 'SGD' ? "selected='selected'" : "")?>><?php _e('Singapore Dollar ($)','dpProAppointments'); ?></option>
                                        <option value="ZAR" <?php echo ($dpProAppointments['currency'] == 'ZAR' ? "selected='selected'" : "")?>><?php _e('South African rand (R)','dpProAppointments'); ?></option>
                                        <option value="SEK" <?php echo ($dpProAppointments['currency'] == 'SEK' ? "selected='selected'" : "")?>><?php _e('Swedish Krona (kr)','dpProAppointments'); ?></option>
                                        <option value="CHF" <?php echo ($dpProAppointments['currency'] == 'CHF' ? "selected='selected'" : "")?>><?php _e('Swiss Franc (CHF)','dpProAppointments'); ?></option>
                                        <option value="TWD" <?php echo ($dpProAppointments['currency'] == 'TWD' ? "selected='selected'" : "")?>><?php _e('Taiwan New Dollars (NT$)','dpProAppointments'); ?></option>
                                        <option value="THB" <?php echo ($dpProAppointments['currency'] == 'THB' ? "selected='selected'" : "")?>><?php _e('Thai Baht (฿)','dpProAppointments'); ?></option>
                                        <option value="TRY" <?php echo ($dpProAppointments['currency'] == 'TRY' ? "selected='selected'" : "")?>><?php _e('Turkish Lira (TL)','dpProAppointments'); ?></option>
                                        <option value="USD" <?php echo ($dpProAppointments['currency'] == 'USD' || $dpProAppointments['currency'] == "" ? "selected='selected'" : "")?>><?php _e('US Dollars ($)','dpProAppointments'); ?></option>
                                    </select>
                                    <br>
                                </div>
                                <div class="desc"><?php _e('Set the currency to display in the events list.','dpProAppointments'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    
                    <div class="option option-select option_w">
                        <div class="option-inner">
                            <label class="titledesc"><?php _e('Price','dpProAppointments'); ?></label>
                            <div class="formcontainer">
                                <div class="forminp">
                                    <input type="number" min="0" max="999999" value="<?php echo ($dpProAppointments['price'] ? $dpProAppointments['price'] : 0)?>" name='dpProAppointments_options[price]'/>
                                    <br>
                                </div>
                                <div class="desc"><?php _e('Set the appointments general price. Set 0 for free appointments.','dpProAppointments'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    
                    <h2 class="subtitle accordion_title" onclick="showAccordionAppointments('div_paypal_gateway');"><?php _e('PayPal Gateway','dpProAppointments'); ?></h2>
					<div id="div_paypal_gateway" style="display:none;">
                    	<div class="option option-select option_w">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Enable / Disable','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <input type="checkbox" value="1" <?php echo ($dpProAppointments['paypal_enable'] ? "checked='checked'" : "")?> name='dpProAppointments_options[paypal_enable]' class="checkbox"/>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Enable Paypal gateway.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="option option-select option_w">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Title','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <input type="text" value="<?php echo $dpProAppointments['paypal_title']?>" name='dpProAppointments_options[paypal_title]'/>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Text that user sees during checkout.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="option option-select option_w">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Description','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <textarea cols="20" rows="5" name='dpProAppointments_options[paypal_description]'><?php echo $dpProAppointments['paypal_description']?></textarea>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Description that user sees during checkout.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="option option-select option_w">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('PayPal Email','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <input type="text" value="<?php echo $dpProAppointments['paypal_email']?>" name='dpProAppointments_options[paypal_email]'/>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Enter your PayPal email to take payments.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="option option-select option_w">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Main Receiver Email','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <input type="text" value="<?php echo $dpProAppointments['paypal_main_email']?>" name='dpProAppointments_options[paypal_main_email]'/>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Enter your main PayPal email to validate IPN requests.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="option option-select option_w">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('PayPal Sandbox','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <input type="checkbox" value="1" <?php echo ($dpProAppointments['paypal_testmode'] ? "checked='checked'" : "")?> name='dpProAppointments_options[paypal_testmode]' class="checkbox"/>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('PayPal sandbox can be used to test payments. Sign up for a developer account <a href="https://developer.paypal.com/" target="_blank">here</a>.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    
                    <h2 class="subtitle accordion_title" onclick="showAccordionAppointments('div_stripe_gateway');"><?php _e('Stripe Gateway','dpProAppointments'); ?></h2>
					<div id="div_stripe_gateway" style="display:none;">
                    	<div class="option option-select option_w">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Enable / Disable','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <input type="checkbox" value="1" <?php echo ($dpProAppointments['stripe_enable'] ? "checked='checked'" : "")?> name='dpProAppointments_options[stripe_enable]' class="checkbox"/>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Enable Stripe gateway.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="option option-select option_w">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Title','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <input type="text" value="<?php echo $dpProAppointments['stripe_title']?>" name='dpProAppointments_options[stripe_title]'/>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Text that user sees during checkout.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="option option-select option_w">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Description','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <textarea cols="20" rows="5" name='dpProAppointments_options[stripe_description]'><?php echo $dpProAppointments['stripe_description']?></textarea>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Description that user sees during checkout.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="option option-select option_w">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Test Secret API','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <input type="text" value="<?php echo $dpProAppointments['test_api_key']?>" name='dpProAppointments_options[test_api_key]'/>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Enter your Test Secret API.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="option option-select option_w">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Test Publishable API','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <input type="text" value="<?php echo $dpProAppointments['test_publishable_key']?>" name='dpProAppointments_options[test_publishable_key]'/>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Enter your Test Publishable API.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="option option-select option_w">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Live Secret API','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <input type="text" value="<?php echo $dpProAppointments['live_api_key']?>" name='dpProAppointments_options[live_api_key]'/>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Enter your Live Secret API.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="option option-select option_w">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Live Publishable API','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <input type="text" value="<?php echo $dpProAppointments['live_publishable_key']?>" name='dpProAppointments_options[live_publishable_key]'/>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Enter your Live Publishable API.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="option option-select option_w">
                            <div class="option-inner">
                                <label class="titledesc"><?php _e('Test Mode','dpProAppointments'); ?></label>
                                <div class="formcontainer">
                                    <div class="forminp">
                                        <input type="checkbox" value="1" <?php echo ($dpProAppointments['stripe_testmode'] ? "checked='checked'" : "")?> name='dpProAppointments_options[stripe_testmode]' class="checkbox"/>
                                        <br>
                                    </div>
                                    <div class="desc"><?php _e('Allows to test payments.','dpProAppointments'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
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

                    
</div> <!--end of float wrap -->


<?php	
}
?>