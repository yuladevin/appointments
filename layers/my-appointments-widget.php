<?php  /**
 * My Appointments Widget
 *
 * This file is used to register and display the Layers - My Appointments widget.
 *
 * @package Layers
 * @since Layers 1.0.0
 */
if( !class_exists( 'LayersDpProAppointments_myAppointments_Widget' ) ) {
	class LayersDpProAppointments_myAppointments_Widget extends Layers_Widget {

		/**
		*  Widget construction
		*/
		function LayersDpProAppointments_myAppointments_Widget(){

			/**
			* Widget variables
			*
			* @param  	string    		$widget_title    	Widget title
			* @param  	string    		$widget_id    		Widget slug for use as an ID/classname
			* @param  	string    		$post_type    		(optional) Post type for use in widget options
			* @param  	string    		$taxonomy    		(optional) Taxonomy slug for use as an ID/classname
			* @param  	array 			$checkboxes    	(optional) Array of checkbox names to be saved in this widget. Don't forget these please!
			*/
			$this->widget_title = __( 'DP Pro Appointments - My Appointments' , 'dpProAppointments' );
			$this->widget_id = 'my-appointments';
			$this->post_type = 'pro-appointments';
			$this->taxonomy = 'appointments_events_category';
			$this->checkboxes = array(
					'show_status',
					'show_pagination',
					'show_title'
				); // @TODO: Try make this more dynamic, or leave a different note reminding users to change this if they add/remove checkboxes

			/* Widget settings. */
			$widget_ops = array( 'classname' => 'obox-layers-' . $this->widget_id .'-widget', 'description' => __( 'Display the appointments of the logged in user.') . '.' );

			/* Widget control settings. */
			$control_ops = array( 'width' => LAYERS_WIDGET_WIDTH_SMALL, 'height' => NULL, 'id_base' => LAYERS_THEME_SLUG . '-widget-' . $this->widget_id );

			/* Create the widget. */
			$this->WP_Widget( LAYERS_THEME_SLUG . '-widget-' . $this->widget_id , $this->widget_title, $widget_ops, $control_ops );

			/* Setup Widget Defaults */
			$this->defaults = array (
				'title' => __( 'My Appointments', 'dpProAppointments' ),
				'excerpt' => __( '', 'dpProAppointments' ),
				'text_style' => 'regular',
				'show_status' => 'on',
				'show_pagination' => 'off',
				'show_title' => 'on',
                'limit' => 8,
				'design' => array(
					'layout' => 'layout-boxed',
					'textalign' => 'text-center',
					'liststyle' => 'list-grid',
					'columns' => '4',
					'fonts' => array(
						'align' => 'text-left',
						'size' => 'medium',
						'color' => NULL,
						'shadow' => NULL
					)
				)
			);
		}

		/**
		*  Widget front end display
		*/
		function widget( $args, $instance ) {
			
			if(!is_user_logged_in()) {
				return;	
			}
			
			// Turn $args array into variables.
			extract( $args );

			// $instance Defaults
			$instance_defaults = $this->defaults;

			// If we have information in this widget, then ignore the defaults
			if( !empty( $instance ) ) $instance_defaults = array();

			// Parse $instance
			$widget = wp_parse_args( $instance, $instance_defaults );

			// Set the background & font styling
			if( !empty( $widget['design']['fonts'][ 'color' ] ) ) layers_inline_styles( '#' . $widget_id, 'color', array( 'selectors' => array( '.section-title h3.heading' , '.section-title p.excerpt' ) , 'color' => $widget['design']['fonts'][ 'color' ] ) );

			// Apply the advanced widget styling
			$this->apply_widget_advanced_styling( $widget_id, $widget );

			$limit = $widget['limit'];
			
			$show_status = 0;
			if( isset( $widget['show_status'] ) ) $show_status = 1;
			$show_pagination = 0;
			if( isset( $widget['show_pagination'] ) ) $show_pagination = 1;
			$show_title = 0;
			if( isset( $widget['show_title'] ) ) $show_title = 1;
			
			?>
            <section class="widget row content-vertical-massive <?php echo $this->check_and_return( $widget , 'design', 'advanced', 'customclass' ) ?> <?php echo $this->get_widget_spacing_class( $widget ); ?>" id="<?php echo $widget_id; ?>">
				<?php if( '' != $this->check_and_return( $widget , 'title' ) ||'' != $this->check_and_return( $widget , 'excerpt' ) ) { ?>
                
					<div class="container clearfix">
						<div class="section-title <?php echo $this->check_and_return( $widget , 'design', 'fonts', 'size' ); ?> <?php echo $this->check_and_return( $widget , 'design', 'fonts', 'align' ); ?> clearfix">
							<?php if( '' != $widget['title'] ) { ?>
								<h3 class="heading"><?php echo esc_html( $widget['title'] ); ?></h3>
							<?php } ?>
							<?php if( '' != $widget['excerpt'] ) { ?>
								<p class="excerpt"><?php echo $widget['excerpt']; ?></p>
							<?php } ?>
						</div>
					</div>
				
				<?php } ?>
                
            <?php
			
			echo do_shortcode('[dpProAppointments type="my-appointments" show_status="'.$show_status.'" show_pagination="'.$show_pagination.'" show_title="'.$show_title.'" limit="'.$limit.'"]');
			
			?>
            
            </section>
		<?php }

		/**
		*  Widget update
		*/

		function update($new_instance, $old_instance) {
			if ( isset( $this->checkboxes ) ) {
				foreach( $this->checkboxes as $cb ) {
					if( isset( $old_instance[ $cb ] ) ) {
						$old_instance[ $cb ] = strip_tags( $new_instance[ $cb ] );
					}
				} // foreach checkboxes
			} // if checkboxes
			return $new_instance;
		}

		/**
		*  Widget form
		*
		* We use regulage HTML here, it makes reading the widget much easier than if we used just php to echo all the HTML out.
		*
		*/
		function form( $instance ){

			// $instance Defaults
			$instance_defaults = $this->defaults;

			// If we have information in this widget, then ignore the defaults
			if( !empty( $instance ) ) $instance_defaults = array();

			// Parse $instance
			$instance = wp_parse_args( $instance, $instance_defaults );

			extract( $instance, EXTR_SKIP );

			$design_bar_components = apply_filters( 'layers_' . $this->widget_id . '_widget_design_bar_components' , array(
					'layout',
					'fonts',
					'custom',
					'advanced'
				) );

			$design_bar_custom_components = apply_filters( 'layers_' . $this->widget_id . '_widget_design_bar_custom_components' , array(
					'display' => array(
						'icon-css' => 'icon-display',
						'label' => __( 'Display', 'dpProAppointments' ),
						'elements' => array(
								'show_title' => array(
									'type' => 'checkbox',
									'name' => $this->get_field_name( 'show_title' ) ,
									'id' => $this->get_field_id( 'show_title' ) ,
									'value' => ( isset( $show_title ) ) ? $show_title : NULL,
									'label' => __( 'Show Title' , 'dpProAppointments' )
								),
								'show_status' => array(
									'type' => 'checkbox',
									'name' => $this->get_field_name( 'show_status' ) ,
									'id' => $this->get_field_id( 'show_status' ) ,
									'value' => ( isset( $show_status ) ) ? $show_status : NULL,
									'label' => __( 'Show Status' , 'dpProAppointments' )
								),
								'show_pagination' => array(
									'type' => 'checkbox',
									'name' => $this->get_field_name( 'show_pagination' ) ,
									'id' => $this->get_field_id( 'show_pagination' ) ,
									'value' => ( isset( $show_pagination ) ) ? $show_pagination : NULL,
									'label' => __( 'Show Pagination' , 'dpProAppointments' )
								),
							)
						)
				) );

			$this->design_bar(
				'side', // CSS Class Name
				array(
					'name' => $this->get_field_name( 'design' ),
					'id' => $this->get_field_id( 'design' ),
				), // Widget Object
				$instance, // Widget Values
				$design_bar_components, // Standard Components
				$design_bar_custom_components // Add-on Components
			); ?>
			<div class="layers-container-large">

				<?php $this->form_elements()->header( array(
					'title' =>  __( 'Post' , 'dpProAppointments' ),
					'icon_class' =>'post'
				) ); ?>

				<section class="layers-accordion-section layers-content">

					<div class="layers-row layers-push-bottom">
						<p class="layers-form-item">
							<?php echo $this->form_elements()->input(
								array(
									'type' => 'text',
									'name' => $this->get_field_name( 'title' ) ,
									'id' => $this->get_field_id( 'title' ) ,
									'placeholder' => __( 'Enter title here' , 'dpProAppointments' ),
									'value' => ( isset( $title ) ) ? $title : NULL ,
									'class' => 'layers-text layers-large'
								)
							); ?>
						</p>

						<p class="layers-form-item">
							<?php echo $this->form_elements()->input(
								array(
									'type' => 'textarea',
									'name' => $this->get_field_name( 'excerpt' ) ,
									'id' => $this->get_field_id( 'excerpt' ) ,
									'placeholder' => __( 'Short Description' , 'dpProAppointments' ),
									'value' => ( isset( $excerpt ) ) ? $excerpt : NULL ,
									'class' => 'layers-textarea layers-large'
								)
							); ?>
						</p>
                        
						<p class="layers-form-item">
							<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php echo __( 'Number of items to show' , 'dpProAppointments' ); ?></label>
							<?php $select_options[ '0' ] = __( 'Show All' , 'dpProAppointments' );
							$select_options = $this->form_elements()->get_incremental_options( $select_options , 1 , 20 , 1);
							echo $this->form_elements()->input(
								array(
									'type' => 'number',
									'name' => $this->get_field_name( 'limit' ) ,
									'id' => $this->get_field_id( 'limit' ) ,
									'value' => ( isset( $limit ) ) ? $limit : NULL ,
									'min' => '0',
									'max' => '100'
								)
							); ?>
						</p>

					</div>
				</section>

			</div>
		<?php } // Form
	} // Class

	// Add our function to the widgets_init hook.
	 //register_widget("LayersDpProAppointments_myAppointments_Widget");
}