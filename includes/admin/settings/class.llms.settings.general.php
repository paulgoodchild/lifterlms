<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Admin Settings Page, General Tab
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Settings_General extends LLMS_Settings_Page {

	/**
	* Constructor
	*
	* executes settings tab actions
	*/
	public function __construct() {
		$this->id    = 'general';
		$this->label = __( 'General', 'lifterlms' );

		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'save' ) );
        add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'register_hooks' ) );
		LLMS()->activate();
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		$is_activated = get_option( 'lifterlms_is_activated', '' );
		$activation_response = get_option( 'lifterlms_activation_message', '' );
		if($is_activated == 'yes') {
			$activation_message = 'Activated';
			$deactivate_checkbox = 'checkbox';
			$deactivate_message = __( 'Deactivate LifterLMS.', 'lifterlms' );
		}
		else {
			$activation_message = 'Not Activated (' . $activation_response . ')';
			$deactivate_checkbox = '';
			$deactivate_message = '';
		}

		$currency_code_options = get_lifterlms_currencies();

		foreach ( $currency_code_options as $code => $name ) {
			$currency_code_options[ $code ] = $name . ' (' . get_lifterlms_currency_symbol( $code ) . ')';
		}

		if ( ! get_option( 'lifterlms_first_time_setup' ) ) {
			return apply_filters( 'lifterlms_general_settings', array(

				array( 'type' => 'sectionstart', 'id' => 'general_information', 'class' =>'top' ),

				array(	'title' => __( 'Welcome to LifterLMS', 
					'lifterlms' ), 
					'type' => 'title', 
					'desc' => '<h2>' . __( 'Getting Started with LifterLMS', 'lifterlms' ) . '</h2>
					<p>' . __( 'Before you start creating courses, making lots of money and building the best (insert your business here) online there are a few setup items we need to address.', 'lifterlms' ) . '</p>
					<p>' . __( 'First things first. We need to activate your plugin. Enter the activation key you were given when you purchased LifterLMS. If you don\'t have a key that\'s ok. Go ahead and continue the setup. You can activate the plugin later.', 'lifterlms' ) . '</p>

	', 
					'id' => 'welcome_options_activate' ),

							array(
					'title' => __( 'Activation Key', 'lifterlms' ),
					'desc' 		=> __( $activation_message, 'lifterlms' ),
					'id' 		=> 'lifterlms_activation_key',
					'type' 		=> 'text',
					'default'	=> '',
					'desc_tip'	=> true,
				),



				array(	
					'type' => 'desc', 
					'desc' => '<p>' . __( 'Next we need to set up your pages. Ya, we know, more pages... That\'s just the way Wordpress works. We\'ve already installed them. You just need to set them.', 'lifterlms' ) . '
					</p> ' . __( 'When you installed LifterLMS we created a few pages for you. You can select those pages or use different ones. Your choice.', 'lifterlms' ) . '</p>

						<p>' . __( 'The first page you need is the Student Account page. This is the page users will go to register, login and access their accounts. We installed a page called My Courses. You can use that or select a different page. If you happen to select a different page you will need to add this shortcode to the page:', 'lifterlms' ) . ' [lifterlms_my_account]</p>

	',
					'id' => 'welcome_options_setup' ),

				array(
					'title' => __( 'Account Access Page', 'lifterlms' ),
					'desc' 		=> __( 'We suggest you choose "My Courses"', 'lifterlms' ),
					'id' 		=> 'lifterlms_myaccount_page_id',
					'type' 		=> 'single_select_page',
					'default'	=> '',
					'class'		=> 'chosen_select_nostd',
					'desc_tip'	=> true,
				),

				array(	
					'type' => 'desc', 
					'desc' => '
					<p>' . __( 'Next we need a checkout page so people can buy your courses and memberships. If you are a true philanthropist and don\'t plan on selling anything you can skip setting up this page. We created a page called "Purchase you can use that or select a different page.', 'lifterlms' ) . '</p>
	', 
					'id' => 'welcome_options_setup' ), 


					

				array(
						'title' => __( 'Checkout Page', 'lifterlms' ),
						'desc' 		=> __( 'We suggest you choose "Purchase"', 'lifterlms' ),
						'id' 		=> 'lifterlms_checkout_page_id',
						'type' 		=> 'single_select_page',
						'default'	=> '',
						'class'		=> 'chosen_select_nostd',
						'desc_tip'	=> __( 'This sets the base page of the checkout page', 'lifterlms' ),
					),



				array(	
								'type' => 'desc', 
								'desc' => '
								<p>' . __( 'If you are going to sell your courses you should probably pick a currency.', 'lifterlms' ) . '</p>
				', 
								'id' => 'welcome_options_setup' ),

				array(
								'title' 	=> __( 'Default Currency', 'lifterlms' ),
								'desc' 		=> __( 'Default currency type.', 'lifterlms' ),
								'id' 		=> 'lifterlms_currency',
								'default'	=> 'USD',
								'type' 		=> 'select',
								'class'		=> 'chosen_select',
								'desc_tip'	=>  true,
								'options'   => $currency_code_options
							),

				array(	
								'type' => 'desc', 
								'desc' => '

								<p>' . __( 'There are a lot of other settings but those were the important ones to get you started. You can access all of the other settings from the big blue menu at the top of the page.', 'liftelrms' ) . '</p>  

								<p>' . __( 'If you have any questions or want to request a feature head on over to our', 'lifterlms' ) . ' <a href="https://lifterlms.com/forums/">' . __( 'Support Forums.', 'lifterlms' ) . '</a></p>

								<p>' . __( 'That\'s all there is to it. Your ready to start building courses and changing the world!', 'lifterlms' ) . '</p> 
								<p>' . __( 'Click "Save Changes" below to save your settings and get started.', 'lifterlms' ) . '</p>
				', 
								'id' => 'welcome_options_setup' ),

				array(	
								'type' => 'hidden', 
								'value' => 'yes',
								
								'id' => 'lifterlms_first_time_setup' ),

				array( 'type' => 'sectionend', 'id' => 'welcome_options_activate' ),

				)
			);

		} else {

			return apply_filters( 'lifterlms_general_settings', array(

				array( 'type' => 'sectionstart', 'id' => 'general_information', 'class' =>'top' ),

				array(	'title' => __( 'Welcome to LifterLMS',
					'lifterlms' ),
					'type' => 'title',
					'desc' => '

					<div class="llms-list">
					<ul>
					<li><p>' . __( 'Thank you for choosing', 'lifterlms' ) . ' <a href="http://lifterlms.com">LifterLMS</a> ' . __( 'as your Learning Management Solution.', 'lifterlms' ) .' </p></li>
					<li><p>' . __( 'Version:', 'lifterlms' ) . ' ' . LLMS()->version . '</p></li>
					<li><p>' . __( 'Support:', 'lifterlms' ) . ' <a href="https://lifterlms.com/forums/">' . __( 'https://lifterlms.com/forums/' ) . '</a></p></li>
					<li><p>' . __( 'Blog:', 'lifterlms' ) . ' <a href="http://blog.lifterlms.com/">' . __( 'http://blog.lifterlms.com/' ) . '</a></p></li>
					<li><p>' . __( 'Tutorials:', 'lifterlms' ) . ' <a href="http://demo.lifterlms.com/">' . __( 'http://demo.lifterlms.com/' ) . '</a></p></li>
					</ul>
					</div>',
					'id' => 'activation_options' ),

				array( 'type' => 'sectionend', 'id' => 'general_information' ),

				array( 'type' => 'sectionstart', 'id' => 'activation' ),

				array(	'title' => __( 'Plugin Activation', 'lifterlms' ), 'type' => 'title',
					'desc' => __( 'Enter your activation key to recieve important updates and new features when they are available.
						Need an activation key? <a href="http://lifterlms.com">Get one here</a>', 'lifterlms' ),
					'id' => 'activation_options' ),

				array(
					'title' => __( 'Activation Key', 'lifterlms' ),
					'desc' 		=> __( $activation_message, 'lifterlms' ),
					'id' 		=> 'lifterlms_activation_key',
					'type' 		=> 'text',
					'default'	=> '',
					'desc_tip'	=> true,
				),

				array(
					'desc'          => $deactivate_message,
					'id'            => 'lifterlms_activation_deactivate',
					'default'       => 'no',
					'type'          => $deactivate_checkbox,
					'checkboxgroup' => 'start',
				),

				array(
					'title' => '',
					'value' => __( 'Update Activation', 'lifterlms' ),
					'type' 		=> 'button',
				),

				array( 'type' => 'sectionend', 'id' => 'activation' ),

				array( 'type' => 'sectionstart', 'id' => 'general_options'),

				array(	'title' => __( 'Currency Options', 'lifterlms' ), 'type' => 'title', 'desc' => __( 'The following options affect how prices are displayed on the frontend.', 'lifterlms' ), 'id' => 'pricing_options' ),

				array(
					'title' 	=> __( 'Default Currency', 'lifterlms' ),
					'desc' 		=> __( 'Default currency type.', 'lifterlms' ),
					'id' 		=> 'lifterlms_currency',
					'default'	=> 'USD',
					'type' 		=> 'select',
					'class'		=> 'chosen_select',
					'desc_tip'	=>  true,
					'options'   => $currency_code_options
				),

				array( 'type' => 'sectionend', 'id' => 'general_options' ),

                array( 'type' => 'sectionstart', 'id' => 'session_manager'),

                array(	'title' => __( 'Session Management', 'lifterlms' ), 'type' => 'title', 'desc' => __( 'Manage User Sessions. LifterLMS creates custom user sessions to manage, payment processing, quizzes and user registration. If you are experiencing issues or incorrect error messages are displaying. Clearing out all of the user session data may help.', 'lifterlms' ), 'id' => 'session_manager' ),

                array(
                    'title' => '',
                    'value' => __( 'Clear All User Session Data', 'lifterlms' ),
                    'type' 		=> 'button',
                ),

                array( 'type' => 'sectionend', 'id' => 'session_manager' )


			 	)

			);
		}

	}

	/**
	 * register new hooks
	 * @return void
	 */
	public function register_hooks() 
	{

		if ( isset($_POST['save']) && strtolower($_POST['save']) == 'clear all user session data')
        {
			$session_handler = new LLMS_Session_Handler();

            $session_handler->delete_all_session_data();

		}
	}
	
	/**
	 * save settings to the database
	 *
	 * @return LLMS_Admin_Settings::save_fields
	 */
	public function save() {
		$settings = $this->get_settings();

		LLMS_Admin_Settings::save_fields( $settings );
		
	}

}

return new LLMS_Settings_General();
