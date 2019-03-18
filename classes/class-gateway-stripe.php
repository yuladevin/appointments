<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (!class_exists('Stripe')) {
    require_once("stripe-php/lib/Stripe.php");
}

class Pro_Appointments_Gateway_Stripe {

	var $notify_url;

	public function __construct() {
		global $dpProAppointments;

        $this->id           = 'stripe';
        $this->icon         = dpProAppointments_plugin_url() . '/images/stripe.png';
        $this->has_fields   = false;
        $this->method_title = __( 'Stripe', 'dpProAppointments' );

		// Define user set variables
		$this->title 				= $dpProAppointments['stripe_title'];
		$this->description 			= $dpProAppointments['stripe_description'];
		$this->testApiKey 		  	= $dpProAppointments['test_api_key'  ];
        $this->liveApiKey 		  	= $dpProAppointments['live_api_key'  ];
        $this->testPublishableKey 	= $dpProAppointments['test_publishable_key'  ];
        $this->livePublishableKey 	= $dpProAppointments['live_publishable_key'  ];
		$this->testmode				= $dpProAppointments['stripe_testmode'];
		$this->debug				= $dpProAppointments['stripe_debug'];
		$this->useUniquePaymentProfile = false;
		$this->useInterval        	= strcmp($dpProAppointments['enable_interval'], 'yes') == 0;
        $this->publishable_key    	= $this->testmode ? $this->testPublishableKey : $this->livePublishableKey;
        $this->secret_key         	= $this->testmode ? $this->testApiKey : $this->liveApiKey;
        $this->capture            	= strcmp($dpProAppointments['capture'], 'yes') == 0;
		$this->form_submission_method = true;

		// Actions
		add_action( 'appointments_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_filter( 'pro_appointment_receipt_stripe', array( $this, 'receipt_page' ), 10, 5 );
		//add_filter( 'appointments_payments_send', array( $this, 'payments_send' ) );
		

		if($_GET['appointments_stripe_post'] == 1 && isset($_POST['stripeToken'])) {
			
			add_action('init', array( $this, 'process_payment' ));

		}

    }
	
	function receipt_page( $txt, $appointment_id, $event_date, $return_url ) {
		
		global $dpProAppointments, $appointment_service_meta;
		
		$services = get_the_terms( $appointment_id, 'pro_appointments_service' );
		if ( $services && ! is_wp_error( $services ) )  {
			$service = $services[0]->term_id;
		}

		$price = $dpProAppointments['price'];
		if($appointment_service_meta[$service]['price'] > 0) {
			$price = $appointment_service_meta[$service]['price'];
		}

		if(!is_numeric($price) || $price == 0) {
			return '';	
		}
		
		return $this->generate_stripe_form( $appointment_id, $event_date, $return_url );

	}
	
	function generate_stripe_form( $appointment_id, $event_date, $return_url ) {
		global $dpProAppointments, $appointment_service_meta;
		
		$services = get_the_terms( $appointment_id, 'pro_appointments_service' );
		if ( $services && ! is_wp_error( $services ) )  {
			$service = $services[0]->term_id;
		}
		
		$price = $dpProAppointments['price'];
		if($appointment_service_meta[$service]['price'] > 0) {
			$price = $appointment_service_meta[$service]['price'];
		}

		$title = ($dpProAppointments['stripe_title'] != "" ? $dpProAppointments['stripe_title'] : __( 'Pay via Stripe', 'dpProAppointments' ));
		
		$return = '<input type="submit" id="dp_appointments_payment_stripe" class="dp_appointments_secondary_btn dpProAppointments_payment_btn" value="' . $title . '" />';
		
		$return .= '
		<div id="stripe_pub_key" class="hidden" style="display:none" data-publishablekey="'.$this->publishable_key.'"> </div>
        
		<form class="appointments_gateway_form" action="'.esc_url(add_query_arg( array('appointments_stripe_post' => 1, 'utm_nooverride' => 1), $return_url )).'" method="POST" id="appointments-payment-form-stripe">
		  	
			<input type="hidden" name="event_date" value="'.$event_date.'" />
			<input type="hidden" name="appointment_id" value="'.$appointment_id.'" />
			<input type="hidden" name="return_url" value="'.$return_url.'" />
			
			<p class="appointments_gateway_item_title">'.__('Appointment for ', 'dpProAppointment').' <span>'.date_i18n(get_option('date_format'). ' ' .get_option('time_format'), strtotime($event_date)).'</span></p>
			
			<span class="appointments-errors" style="display:none;"></span>
			<div class="form-row">
			  <i class="fa fa-credit-card"></i>
			  <input class="dp_appointments_input_text " type="text" size="19" maxlength="19" data-stripe="number" placeholder="'.__( 'Card Number', 'dpProAppointments' ).'" style="width: auto; margin-left: 10px;" />
			</div>
			<div class="dp_appointments_clear"></div>
			<div class="form-row form-row-first">
			  <i class="fa fa-calendar"></i>
			  <input type="text" class="dp_appointments_input_text " size="2" maxlength="2" data-stripe="exp-month" placeholder="'.__( 'MM', 'dpProAppointments' ).'" style="width: auto; margin-left: 10px;" />
			  <span> / </span>
			  <input type="text" class="dp_appointments_input_text " size="4" maxlength="4" data-stripe="exp-year" placeholder="'.__( 'YYYY', 'dpProAppointments' ).'" style="width: auto;" />
			</div>
			<div class="dp_appointments_clear"></div>
			<div class="form-row">
				<i class="fa fa-lock"></i>
				<input class="dp_appointments_input_text" type="text" size="4" maxlength="4" data-stripe="cvc" value="" placeholder="'.__( 'CVC', 'dpProAppointments' ).'" style="width: auto; margin-left: 10px;" />
			</div>
			<div class="dp_appointments_clear"></div>
			<button type="submit" class="dp_appointments_secondary_btn">'.__( 'Pay', 'dpProAppointments' ).' '.number_format( $price, 2, '.', '' ).' '.$dpProAppointments['currency'].'</button>
			<div class="dp_appointments_clear"></div>
		</form>';
		return $return;

	}

	protected function send_to_stripe()
    {
      global $dpProAppointments;

      // Set your secret key: remember to change this to your live secret key in production
      // See your keys here https://manage.stripe.com/account
      Stripe::setApiKey($this->secret_key);

      // Get the credit card details submitted by the form
      $data = $this->getRequestData();

      // Create the charge on Stripe's servers - this will charge the user's card
      try {

            if($this->useUniquePaymentProfile)
            {
              // Create the user as a customer on Stripe servers
              $customer = Stripe_Customer::create(array(
                "email" => $data['card']['billing_email'],
                "description" => $data['card']['name'],
                "card"  => $data['token']
              ));
              // Create the charge on Stripe's servers - this will charge the user's card

            $charge = Stripe_Charge::create(array(
              "amount"      => $data['amount'], // amount in cents, again
              "currency"    => $data['currency'],
              "card"        => $customer->default_card,
              "description" => $data['card']['name'],
              "customer"    => $customer->id,
              "capture"     => !$this->capture,
              "receipt_email" => $data['card']['billing_email']
            ));
          } else {

            $charge = Stripe_Charge::create(array(
              "amount"      => $data['amount'], // amount in cents, again
              "currency"    => $data['currency'],
              "card"        => $data['token'],
              "description" => $data['card']['name'],
              "capture"     => !$this->capture,
              "receipt_email" => $data['card']['billing_email']
            ));
        }
        $this->transactionId = $charge['id'];

        //Save data for the "Capture"
		/*
        update_post_meta( $this->order->id, 'transaction_id', $this->transactionId);
        update_post_meta( $this->order->id, 'key', $this->secret_key);
        update_post_meta( $this->order->id, 'auth_capture', $this->capture);
		*/
        return true;

      } catch(Stripe_Error $e) {
        // The card has been declined, or other error
        $body = $e->getJsonBody();
        $err  = $body['error'];
		
        error_log('Stripe Error:' . $err['message'] . "\n");
        return false;
      }
    }

    public function process_payment()
    {
        global $dpProAppointments;

        if ($this->send_to_stripe())
        {

          $this->completeOrder();
		  
        }
        else
        {

          $this->markAsFailedPayment();
		  
		  // Message to user - FAILED
        }
    }

    protected function markAsFailedPayment()
    {
		$return_url = $_POST['return_url'];
		
        wp_redirect(esc_url_raw(add_query_arg( array('appointments_return_failed' => 1, 'utm_nooverride' => 1), remove_query_arg( array('appointments_return'), $return_url ))));
		exit;
		// XX Save as failed
    }

    protected function completeOrder()
    {
        global $dpProAppointments, $wpdb, $table_prefix;
		
		$appointment_id = $_POST['appointment_id'];
		$return_url = $_POST['return_url'];
		//print_r($_POST);
		//die();
		if(!is_numeric($appointment_id)) {
			$this->markAsFailedPayment();
		}
		
		$my_post = array(
			  'ID'           => $appointment_id,
			  'post_status'   => 'completed'
		  );
		
		// Update the post into the database
		  wp_update_post( $my_post );
        // XX Save as Completed
		
		wp_redirect(esc_url_raw(add_query_arg( array('appointments_return' => 1, 'utm_nooverride' => 1), remove_query_arg( array('appointments_return_failed'), $return_url ))));
		exit;
    }


  protected function getRequestData()
  {
   	global $dpProAppointments, $wpdb, $table_prefix, $appointment_service_meta;
	
	$appointment_id = $_POST['appointment_id'];
	$event_date = $_POST['event_date'];
	
	$services = get_the_terms( $appointment_id, 'pro_appointments_service' );
	if ( $services && ! is_wp_error( $services ) )  {
		$service = $services[0]->term_id;
	}
	
	$price = $dpProAppointments['price'];
	if($appointment_service_meta[$service]['price'] > 0) {
		$price = $appointment_service_meta[$service]['price'];
	}

	if(!is_numeric($appointment_id) || !isset($event_date)) {
		wp_redirect( home_url() ); exit;	
	}
	
	$appointment_email = get_post_meta($appointment_id, 'appointment_email', true);
	$appointment_name = get_post_meta($appointment_id, 'appointment_name', true);
	//die("Stripe Get Request Data");

	return array(
		"amount"      => number_format( $price, 2, '', '' ),
		"currency"    => strtolower($dpProAppointments['currency']),
		"token"       => $_POST['stripeToken'],
		"receipt_email" => $appointment_email,
		"description" => sprintf( __( 'Appointment for %s' , 'dpProAppointments'), date_i18n(get_option('date_format'), strtotime($event_date)) ),
		"card"        => array(
			"name"            => $appointment_name,
			"billing_email"	  => $appointment_email,
			"address_line1"   => '',
			"address_line2"   => '',
			"address_zip"     => '',
			"address_state"   => '',
			"address_country" => ''
		)
	);

    return false;
  }

}
