<?php 

/************************************************************************/
/*** DISPLAY START
/************************************************************************/
class dpProAppointments_wpress_display {
	
	static $js_flag;
	static $js_declaration = array();
	public $events_html;
	static $type;
	static $opts;

	function dpProAppointments_wpress_display($type = '', $opts = array()) {
		self::$type = $type;
		self::$opts = $opts;
		self::return_dpProAppointments();
		
		add_action('wp_footer', array(__CLASS__, 'add_scripts'), 100);
		
	}
	
	static function add_scripts() {
		global $dpProAppointments;
		
		if(self::$js_flag) {
			foreach( self::$js_declaration as $key) { echo $key; }
			echo '<style type="text/css">'.$dpProAppointments['custom_css'].'</style>';
		}
	}
	
	function return_dpProAppointments() {
		global $dpProAppointments, $wpdb, $table_prefix, $post;
		
		$type = self::$type;
		$opts = self::$opts;
		
		require_once (dirname (__FILE__) . '/../classes/base.class.php');
		$dpProAppointments_class = new DpProAppointments( false, null, $type, $opts );
		
		$events_script= $dpProAppointments_class->addScripts();
		self::$js_declaration[] = $events_script;
		
		self::$js_flag = true;
		
		$events_html = $dpProAppointments_class->output();
					
		$this->events_html = $events_html;
	}
}

function dpProAppointments_simple_shortcode($atts) {
	global $dpProAppointments;
	
	extract(shortcode_atts(array(
		'type' => '',
		'show_status' => true,
		'show_pagination' => false,
		'show_title' => true,
		'show_week_nav' => true,
		'show_tooltip' => true,
		'time_background' => null,
		'show_service_name' => true,
		'skin'	=> '',
		'service' => '',
		'limit' => 0
	), $atts));

	/* Add JS files */
	if ( !is_admin() ){ 
		
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'placeholder', dpProAppointments_plugin_url( 'js/jquery.placeholder.js' ),
			array('jquery'), DP_APPOINTMENTS_VER, false); 
		wp_enqueue_script( 'selectric', dpProAppointments_plugin_url( 'js/jquery.selectric.min.js' ),
			array('jquery'), DP_APPOINTMENTS_VER, false); 
		wp_enqueue_script( 'icheck', dpProAppointments_plugin_url( 'js/jquery.icheck.min.js' ),
			array('jquery'), DP_APPOINTMENTS_VER, false); 
		wp_enqueue_script( 'dpProAppointments', dpProAppointments_plugin_url( 'js/jquery.dpProAppointments.js' ),
			array('jquery'), DP_APPOINTMENTS_VER, false); 
		
		wp_localize_script( 'dpProAppointments', 'ProAppointmentsAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php?lang='.$_GET['lang'] ), 'postEventsNonce' => wp_create_nonce( 'ajax-get-events-nonce' ) ) );
		
	}
		
	if($dpProAppointments['rtl_support']) {
		wp_enqueue_style( 'dpProAppointments_rtlcss', dpProAppointments_plugin_url( 'css/rtl.css' ),
			false, DP_APPOINTMENTS_VER, 'all');
	}
		
	$opts = array(
		'show_status' => $show_status,
		'show_pagination' => $show_pagination,
		'show_title' => $show_title,
		'show_tooltip' => $show_tooltip,
		'show_week_nav' => $show_week_nav,
		'show_service_name' => $show_service_name,
		'time_background' => $time_background,
		'skin' => $skin,
		'service' => $service,
		'limit' => $limit
	);
	
	$dpProAppointments_wpress_display = new dpProAppointments_wpress_display($type, $opts);
	return $dpProAppointments_wpress_display->events_html;
}
add_shortcode('dpProAppointments', 'dpProAppointments_simple_shortcode');

/************************************************************************/
/*** DISPLAY END
/************************************************************************/

/************************************************************************/
/*** WIDGET START
/************************************************************************/

class DpProAppointments_Widget extends WP_Widget {
	function __construct() {
		$params = array(
			'description' => 'Use the schedule as a widget',
			'name' => 'DP Pro Appointments - Appointments Schedule'
		);
		
		parent::__construct('ProAppointments', '', $params);
	}
	
	public function form($instance) {
		global $wpdb, $table_prefix;

		extract($instance);

		?>
        	<p>
            	<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title','dpProAppointments'); ?>: </label>
                <input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" value="<?php if(isset($title)) echo esc_attr($title); ?>" />
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('description');?>"><?php _e('Description','dpProAppointments'); ?>: </label>
                <textarea class="widefat" rows="5" id="<?php echo $this->get_field_id('description');?>" name="<?php echo $this->get_field_name('description');?>"><?php if(isset($description)) echo esc_attr($description); ?></textarea>
            </p>
            
            
        <?php
	}
	
	public function widget($args, $instance) {
		global $wpdb, $table_prefix;
		
		extract($args);
		extract($instance);
		
		$title = apply_filters('widget_title', $title);
		$description = apply_filters('widget_description', $description);
		
		//if(empty($title)) $title = 'DP Pro Appointments';
		
		echo $before_widget;
			if(!empty($title))
				echo $before_title . $title . $after_title;
			echo '<p>'. $description. '</p>';
			echo do_shortcode('[dpProAppointments]');
		echo $after_widget;
		
	}
}

/************************************************************************/
/*** WIDGET END
/************************************************************************/

/************************************************************************/
/*** WIDGET START - MY APPOINTMENTS
/************************************************************************/

class DpProAppointments_myAppointments_Widget extends WP_Widget {
	function __construct() {
		$params = array(
			'description' => 'Display the appointments of the logged in user.',
			'name' => 'DP Pro Appointments - My Appointments'
		);
		
		parent::__construct('ProAppointments_myAppointments', '', $params);
	}
	
	public function form($instance) {
		global $wpdb, $table_prefix;

		extract($instance);

		?>
			<p>
				<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title','dpProAppointments'); ?>: </label>
				<input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" value="<?php if(isset($title)) echo esc_attr($title); ?>" />
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id('description');?>"><?php _e('Description','dpProAppointments'); ?>: </label>
				<textarea class="widefat" rows="5" id="<?php echo $this->get_field_id('description');?>" name="<?php echo $this->get_field_name('description');?>"><?php if(isset($description)) echo esc_attr($description); ?></textarea>
			</p>
			
			
		<?php
	}
	
	public function widget($args, $instance) {
		global $wpdb, $table_prefix;
		
		extract($args);
		extract($instance);
		
		$title = apply_filters('widget_title', $title);
		$description = apply_filters('widget_description', $description);
		
		//if(empty($title)) $title = 'DP Pro Appointments';
		
		echo $before_widget;
			if(!empty($title))
				echo $before_title . $title . $after_title;
			echo '<p>'. $description. '</p>';
			echo do_shortcode('[dpProAppointments type="my-appointments"]');
		echo $after_widget;
		
	}
}

/************************************************************************/
/*** WIDGET END
/************************************************************************/

add_action('widgets_init', 'dpProAppointments_register_widget', 50 );
function dpProAppointments_register_widget() {

	if(defined('LAYERS_VERSION')) {
		
		require_once (dirname (__FILE__) . '/../layers/schedule-widget.php');

		register_widget('LayersDpProAppointments_Widget');
		
		require_once (dirname (__FILE__) . '/../layers/my-appointments-widget.php');

		register_widget('LayersDpProAppointments_myAppointments_Widget');

	} else {
		
		register_widget('DpProAppointments_Widget');
		
		register_widget('DpProAppointments_myAppointments_Widget');

	}
}


function dpProAppointments_enqueue_styles() {	
  	global $post, $dpProAppointments, $wp_registered_widgets,$wp_widget_factory;
  
	wp_enqueue_style( 'dpProAppointments_headcss', dpProAppointments_plugin_url( 'css/dpProAppointments.css' ),
		false, DP_APPOINTMENTS_VER, 'all');
	wp_enqueue_style( 'font-awesome', dpProAppointments_plugin_url( 'css/font-awesome.css' ),
		false, DP_APPOINTMENTS_VER, 'all');
  
}
add_action( 'init', 'dpProAppointments_enqueue_styles' );

//admin settings
function dpProAppointments_admin_scripts($force = false) {
	global $dpProAppointments;
	if ( is_admin() ){ // admin actions
		// Settings page only
		
		if ( $force || (isset($_GET['page']) && 
		(strpos($_GET['page'], 'dpProAppointments-') !== false) )  ) {

			wp_register_script('jquery', false, false, false, false);
			wp_enqueue_script( 'jquery-ui-datepicker' );
		
			wp_enqueue_style( 'dpProAppointments_adminstyles', dpProAppointments_plugin_url( 'css/admin-styles.css' ),
			false, DP_APPOINTMENTS_VER, 'all');
			
			wp_enqueue_script( 'dpProAppointments_admin-settings', dpProAppointments_plugin_url( 'js/admin_settings.js' ),
			array('jquery'), DP_APPOINTMENTS_VER, 'all');
	
			wp_enqueue_script( 'selectric', dpProAppointments_plugin_url( 'js/jquery.selectric.min.js' ),
			array('jquery'), DP_APPOINTMENTS_VER, false); 
			
			wp_enqueue_style( 'jquery-ui-core-appointments', dpProAppointments_plugin_url( 'themes/base/jquery.ui.core.css' ),
				false, DP_APPOINTMENTS_VER, 'all' );
			wp_enqueue_style( 'jquery-ui-theme-appointments', dpProAppointments_plugin_url( 'themes/base/jquery.ui.theme.css' ),
				false, DP_APPOINTMENTS_VER, 'all' );
			wp_enqueue_style( 'jquery-ui-datepicker-appointments', dpProAppointments_plugin_url( 'themes/base/jquery.ui.datepicker.css' ),
				false, DP_APPOINTMENTS_VER, 'all' );
		
		}
		
  	}
}

add_action( 'admin_init', 'dpProAppointments_admin_scripts' );
add_action( 'appointments_enqueue_admin', 'dpProAppointments_admin_scripts' );

function dpProAppointments_admin_head() {
	global $dpProAppointments;
	if ( is_admin() ){ 
	   
	 }
}
add_action('admin_head', 'dpProAppointments_admin_head');
?>