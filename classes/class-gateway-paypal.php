<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class Pro_Appointments_Gateway_Paypal {

	var $notify_url;

	public function __construct() {
		global $dpProAppointments;

        $this->id           = 'paypal';
        $this->icon         = dpProAppointments_plugin_url() . '/images/paypal.png';
        $this->has_fields   = false;
        $this->liveurl      = 'https://www.paypal.com/cgi-bin/webscr';
		$this->testurl      = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        $this->method_title = __( 'PayPal', 'dpProAppointments' );
        $this->notify_url   = str_replace( 'https:', 'http:', esc_url(add_query_arg( 'appointments-api', 'Pro_Appointments_Gateway_Paypal', home_url( '/' ) )) );

		// Define user set variables
		$this->title 			= $dpProAppointments['paypal_title'];
		$this->description 		= $dpProAppointments['paypal_description'];
		$this->email 			= $dpProAppointments['paypal_email'];
		$this->receiver_email   = ($dpProAppointments['paypal_main_email'] == '' ? $dpProAppointments['paypal_email'] : $dpProAppointments['paypal_main_email']);
		$this->testmode			= $dpProAppointments['paypal_testmode'];
		$this->debug			= $dpProAppointments['paypal_debug'];
		$this->form_submission_method = true;

		// Actions
		add_action( 'valid-paypal-standard-ipn-request', array( $this, 'successful_request' ) );
		add_filter( 'pro_appointment_receipt_paypal', array( $this, 'receipt_page' ), 10, 5 );
		add_action( 'appointments_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		// Payment listener/API hook
		add_action( 'dpProAppointments_api_wc_gateway_paypal', array( $this, 'check_ipn_response' ) );

    }

	function get_paypal_args( $appointment_id, $event_date, $return_url ) {
		global $dpProAppointments, $appointment_service_meta;

		$services = get_the_terms( $appointment_id, 'pro_appointments_service' );
		if ( $services && ! is_wp_error( $services ) )  {
			$service = $services[0]->term_id;
		}
		
		$price = $dpProAppointments['price'];
		if($appointment_service_meta[$service]['price'] > 0) {
			$price = $appointment_service_meta[$service]['price'];
		}

		
		// PayPal Args
		$paypal_args = array(

			'cmd' 					=> '_cart',
			'business' 				=> $this->email,
			'no_note' 				=> 1,
			'currency_code' 		=> $dpProAppointments['currency'],
			'charset' 				=> 'UTF-8',
			'rm' 					=> is_ssl() ? 2 : 1,
			'upload' 				=> 1,
			'return' 				=> esc_url(add_query_arg( array('appointments_return' => 1, 'utm_nooverride' => 1), $return_url )),
			'cancel_return'			=> home_url().'/?appointments_cancel=1',
			'BUTTONSOURCE'          => 'ProEventCalendar_Cart',
			
			// Booking ID
			'custom'          		=> $appointment_id,
			
			// IPN
			'notify_url'			=> $this->notify_url,
		);
		
		$paypal_args['item_name_1'] 	= sprintf( __( 'Appointment for %s' , 'dpProAppointments'), date_i18n(get_option('date_format'). ' ' .get_option('time_format'), $event_date) );
		$paypal_args['quantity_1'] 		= 1;
		$paypal_args['amount_1'] 		= number_format( $price, 2, '.', '' );

		$paypal_args['no_shipping'] = 1;

		return $paypal_args;
	}

    function generate_paypal_form( $appointment_id, $event_date, $return_url ) {
		global $dpProAppointments;
		
		if ( $this->testmode ):
			$paypal_adr = $this->testurl . '?test_ipn=1&';
		else :
			$paypal_adr = $this->liveurl . '?';
		endif;

		$paypal_args = $this->get_paypal_args( $appointment_id, $event_date, $return_url );

		$paypal_args_array = array();

		foreach ($paypal_args as $key => $value) {
			$paypal_args_array[] = '<input type="hidden" name="'.esc_attr( $key ).'" value="'.esc_attr( $value ).'" />';
		}
		
		$script_js = '
			jQuery("body").block({
					message: "' . esc_js( __( 'Thank you for your order. We are now redirecting you to PayPal to make payment.', 'dpProAppointments' ) ) . '",
					baseZ: 99999,
					overlayCSS:
					{
						background: "#fff",
						opacity: 0.6
					},
					css: {
				        padding:        "20px",
				        zindex:         "9999999",
				        textAlign:      "center",
				        color:          "#555",
				        border:         "3px solid #aaa",
				        backgroundColor:"#fff",
				        cursor:         "wait",
				        lineHeight:		"24px",
				    }
				});
			jQuery("#submit_paypal_payment_form").click();
		' ;
		
		$title = ($dpProAppointments['paypal_title'] != "" ? $dpProAppointments['paypal_title'] : __( 'Pay via PayPal', 'dpProAppointments' ));
		
		return '<form action="'.esc_url( $paypal_adr ).'" method="post" id="paypal_payment_form" target="_top">
				' . implode( '', $paypal_args_array) . '
				<input type="submit" class="dp_appointments_secondary_btn dpProAppointments_payment_btn" id="submit_paypal_payment_form" value="' . $title . '" />
			</form>';

	}

	function receipt_page( $txt, $appointment_id, $event_date, $return_url ) {
		
		global $dpProAppointments;
		
		if(!is_numeric($dpProAppointments['price']) || $dpProAppointments['price'] == 0) {
			return '';	
		}
		
		return $this->generate_paypal_form( $appointment_id, $event_date, $return_url );

	}

	/**
	 * Check PayPal IPN validity
	 **/
	function check_ipn_request_is_valid() {
		global $dpProAppointments;

    	// Get recieved values from post data
		$received_values = array( 'cmd' => '_notify-validate' );
		$received_values += stripslashes_deep( $_POST );

        // Send back post vars to paypal
        $params = array(
        	'body' 			=> $received_values,
        	'sslverify' 	=> false,
        	'timeout' 		=> 60,
        	'httpversion'   => '1.1',
        	'headers'       => array( 'host' => 'www.paypal.com' ),
        	'user-agent'	=> 'dpProAppointments/' . $dpProAppointments->version
        );

        if ( 'yes' == $this->debug )
			$this->log->add( 'paypal', 'IPN Request: ' . print_r( $params, true ) );

        // Get url
       	if ( $this->testmode )
			$paypal_adr = $this->testurl;
		else
			$paypal_adr = $this->liveurl;

		// Post back to get a response
        $response = wp_remote_post( $paypal_adr, $params );

        // check to see if the request was valid
        if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && ( strcmp( $response['body'], "VERIFIED" ) == 0 ) ) {

            return true;
        }

        return false;
    }


	/**
	 * Check for PayPal IPN Response
	 *
	 * @access public
	 * @return void
	 */
	function check_ipn_response() {

		@ob_clean();

    	if ( ! empty( $_POST ) && $this->check_ipn_request_is_valid() ) {

    		header( 'HTTP/1.1 200 OK' );

        	do_action( "valid-paypal-standard-ipn-request", $_POST );

		} else {

			wp_die( "PayPal IPN Request Failure" );

   		}

	}


	function successful_request( $posted ) {
		global $dpProAppointments, $wpdb, $table_prefix;
		
		$posted = stripslashes_deep( $posted );

		// Custom holds post ID
	    if ( ! empty( $posted['custom'] ) ) {

		    // Lowercase returned variables
	        $posted['payment_status'] 	= strtolower( $posted['payment_status'] );
	        $posted['txn_type'] 		= strtolower( $posted['txn_type'] );
			$appointment_ = $posted['custom'];
			
	        // Sandbox fix
	        if ( $posted['test_ipn'] == 1 && $posted['payment_status'] == 'pending' )
	        	$posted['payment_status'] = 'completed';
			
	        // We are here so lets check status and do actions
	        switch ( $posted['payment_status'] ) {
	            case 'completed' :
	            case 'pending' :

					//wp_mail( '', 'IPN Test', "Completed / Pending" );
					
	            	// Check valid txn_type
	            	$accepted_types = array( 'cart', 'instant', 'express_checkout', 'web_accept', 'masspay', 'send_money' );
					if ( ! in_array( $posted['txn_type'], $accepted_types ) ) {

						exit;
					}

					// Validate Amount
					
				    // Validate Email Address
					if ( strcasecmp( trim( $posted['receiver_email'] ), trim( $this->receiver_email ) ) != 0 ) {
						//wp_mail( '', 'IPN Test', "Validate Email: ". trim( $posted['receiver_email'] ) . ' - ' . trim( $this->receiver_email ) . ' - ' .strcasecmp( trim( $posted['receiver_email'] ), trim( $this->receiver_email ) ));
				    	exit;
					}

					 // Store PP Details
	                /*if ( ! empty( $posted['payer_email'] ) )
	                	update_post_meta( $order->id, 'Payer PayPal address', $posted['payer_email'] );
	                if ( ! empty( $posted['txn_id'] ) )
	                	update_post_meta( $order->id, 'Transaction ID', $posted['txn_id'] );
	                if ( ! empty( $posted['first_name'] ) )
	                	update_post_meta( $order->id, 'Payer first name', $posted['first_name'] );
	                if ( ! empty( $posted['last_name'] ) )
	                	update_post_meta( $order->id, 'Payer last name', $posted['last_name'] );
	                if ( ! empty( $posted['payment_type'] ) )
	                	update_post_meta( $order->id, 'Payment type', $posted['payment_type'] );*/

	                if ( $posted['payment_status'] == 'completed' ) {
	                	
						//wp_mail( '', 'IPN Test', "Completed" );
						
						$my_post = array(
							  'ID'           => $appointment_id,
							  'post_status'   => 'completed'
						  );
						
						// Update the post into the database
						  wp_update_post( $my_post );
						
						/*
						add_filter( 'wp_mail_from_name', 'dpProAppointments_wp_mail_from_name' );
						
						
						
						if($calendar_obj->booking_email_template_user == '') {
							$calendar_obj->booking_email_template_user = "Hi #USERNAME#,\n\nThanks for set the appointment:\n\n#APPOINTMENT_DETAILS#\n\nPlease contact us if you have questions.\n\nKind Regards.\n#SITE_NAME#";
						}
						
						if($calendar_obj->booking_email_template_admin == '') {
							$calendar_obj->booking_email_template_admin = "The user #USERNAME# created an appointment:\n\n#APPOINTMENT_DETAILS#\n\n#COMMENT#\n\n#SITE_NAME#";
						}

						// Email to User
						
						wp_mail( $userdata->user_email, get_bloginfo('name'), apply_filters('appointments_booking_email', $calendar_obj->booking_email_template_user, $booking_obj->id_event, $userdata->display_name, $userdata->user_email, $booking_obj->event_date) );
						
						// Email to Admin

						wp_mail( get_bloginfo('admin_email'), get_bloginfo('name'), apply_filters('appointments_booking_email', $calendar_obj->booking_email_template_admin, $booking_obj->id_event, $userdata->display_name, $userdata->user_email, $booking_obj->event_date) );*/
						
						
	                } else {
	                	
						// Still Pending
						//wp_mail( '', 'IPN Test', "Pending" );
						
	                }

	            break;
	            case 'denied' :
	            case 'expired' :
	            case 'failed' :
	            case 'voided' :
	                // Order failed
					//wp_mail( '', 'IPN Test', "Failed" );
					$wpdb->delete( $table_name_booking, array( 'id' => $booking_id ) );
	            break;
	            case "refunded" :

					//wp_mail( '', 'IPN Test', "Refunded" );
					$wpdb->delete( $table_name_booking, array( 'id' => $booking_id ) );

	            break;
	            case "reversed" :

	            	// Mark order as refunded
					//wp_mail( '', 'IPN Test', "Reversed" );
					$wpdb->delete( $table_name_booking, array( 'id' => $booking_id ) );
					
	            break;
	            case "canceled_reversal" :
					//wp_mail( '', 'IPN Test', "Canceled Reversal" );
					
					
	            break;
	            default :
	            	// No action
	            break;
	        }

			exit;
	    }

	}

}
