<?php
/*
Plugin Name: WooCommerce PayHub Gateway Plugin
Plugin URI: http://payhub.com/wiki
Description: PayHub Inc. is a technology company that provides SAAS solutions and products that facilitate payment processing across a wide range of industries and devices.  We are a San Francisco Bay Area company, headquartered in San Rafael, California. We are a team of professionals with more than 35 years of combined electronic payment and financial industry and high tech expertise.
Version: 1.0.6
Author: EJ

*/


add_action('plugins_loaded', 'woocommerce_payhub_init', 0);

	function woocommerce_payhub_init() {

		if ( ! class_exists( 'WC_Payment_Gateway' ) ) { return; }

		require_once(WP_PLUGIN_DIR . "/" . plugin_basename( dirname(__FILE__)) . '/class/payhubTransaction.class.php');

		/**
	 	* Gateway class
	 	**/
		class WC_PayHub_Gateway extends WC_Payment_Gateway {
		
			var $avaiable_countries = array(
				'GB' => array(
					'Visa',
					'MasterCard',
					'Discover',
					'American Express'
				),
				'US' => array(
					'Visa',
					'MasterCard',
					'Discover',
					'American Express'
				),
				'CA' => array(
					'Visa',
					'MasterCard',
					'Discover',
					'American Express'
				)
			);
			var $api_username;
			var $api_password;
			var $orgid;
			var $terminal_id;
			var $card_data;
			var $card_cvv;
			var $card_exp_month;
			var $card_exp_year;
			var $response;


			function __construct() { 
				
				$this->id				= 'payhub';
				$this->method_title 	= __('PayHub', 'woothemes');
				$this->icon 			= WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)) . '/PoweredbyPayHubCards.png';
				$this->has_fields 		= true;
				
				// Load the form fields
				$this->init_form_fields();
				
				// Load the settings.
				$this->init_settings();
				
				// Get setting values
				$this->title 			= $this->settings['title'];
				$this->description 		= $this->settings['description'];
				$this->enabled 			= $this->settings['enabled'];
				$this->api_username 	= $this->settings['api_username'];
				$this->api_password 	= $this->settings['api_password'];
				$this->orgid 	= $this->settings['orgid'];
				$this->tid 	= $this->settings['terminal_id'];
				

				// Hooks
				add_action( 'admin_notices', array( &$this, 'ssl_check') );
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options') );
				add_action( 'woocommerce_thankyou_cheque', array(&$this, 'thankyou_page' ));
			}

			/**
		 	* Check if SSL is enabled and notify the user if SSL is not enabled
		 	**/
	
			function ssl_check() {
		     
			if (get_option('woocommerce_force_ssl_checkout')=='no' && $this->enabled=='yes') :
			
				echo '<div class="error"><p>'.sprintf(__('PayHub is enabled, but the <a href="%s">force SSL option</a> is disabled; your checkout is not secure! Please enable SSL and ensure your server has a valid SSL certificate - PayHub is in live mode.', 'woothemes'), admin_url('admin.php?page=woocommerce')).'</p></div>';
			
			endif;
			}


			/**
	     * Initialize Gateway Settings Form Fields
	     */
	    function init_form_fields() {
	    
	    	$this->form_fields = array(
				'title' => array(
								'title' => __( 'Title', 'woothemes' ), 
								'type' => 'text', 
								'description' => __( 'This controls the title which the user sees during checkout.', 'woothemes' ), 
								'default' => __( 'PayHub, Inc', 'woothemes' ),
							), 
				'enabled' => array(
								'title' => __( 'Enable/Disable', 'woothemes' ), 
								'label' => __( 'Enable PayHub', 'woothemes' ), 
								'type' => 'checkbox', 
								'description' => '', 
								'default' => 'no'
							), 
				'description' => array(
								'title' => __( 'Description', 'woothemes' ), 
								'type' => 'text', 
								'description' => __( 'This controls the description which the user sees during checkout.', 'woothemes' ), 
								'default' => 'We accept Visa, Mastercard, & Discover'
							),  
				'api_username' => array(
								'title' => __( 'API Username', 'woothemes' ), 
								'type' => 'text', 
								'description' => __( 'Get your API Login from PayHub.', 'woothemes' ), 
								'default' => ''
							), 
				'api_password' => array(
								'title' => __( 'API Password', 'woothemes' ), 
								'type' => 'text', 
								'description' => __( 'Get your API Password from PayHub.', 'woothemes' ), 
								'default' => ''
							),
				'orgid' => array(
								'title' => __( 'OrgID', 'woothemes' ),
								'type' => 'text',
								'description' => __( 'This is your organization ID', 'woothemes' ),
								'default' => '00000'
							),
				'terminal_id' => array(
								'title' => __( 'Terminal ID', 'woothemes' ),
								'type' => 'text',
								'description' => __( 'Get your terminal ID from PayHub.', 'woothemes' ),
								'default' => '0000'
							)
				);
	    }
		  
		   /**
			 * Admin Panel Options 
			 * - Options for bits like 'title' and availability on a country-by-country basis
			 */
			function admin_options() {
		    	?>
		    	<h3><?php _e( 'PayHub', 'woothemes' ); ?></h3>
		    	<p><?php _e( 'Payhub works by adding credit card fields on the checkout and then sending the details to our webservice for verification. You must first have a PayHub Account to accept credit card and debit card payments. Please contact x to setup an account. If you have any questions you can contact us at (415) 306-9476 M-F from 8am - 5 pm PST or email us at wecare@payhub.com</a> anytime.  ', 'woothemes' ); ?></p>
		    	<table class="form-table">
		    		<?php $this->generate_settings_html(); ?>
				</table><!--/.form-table-->
		    	<?php
		    }
			

			/**
		     * Payment form on checkout page
		     */
			function payment_fields() {
				global $woocommerce;
				?>
				<?php if ($this->description) : ?><p><?php echo $this->description; ?></p><?php endif; ?>

				<fieldset>
					<p class="form-row form-row-first">
						<label for="card_number"><?php echo __("Credit Card number", 'woocommerce') ?> <span class="required">*</span></label>
						<input type="text" class="input-text" name="card_number" />
					</p>
					<div class="clear"></div>
					<p class="form-row form-row-first">
						<label for="cc_exp_month"><?php echo __("Expiration date", 'woocommerce') ?> <span class="required">*</span></label>
						<select name="card_exp_month" id="cc_exp_month">
							<option value=""><?php _e('Month', 'woocommerce') ?></option>
							<?php
								$months = array();
								for ($i = 1; $i <= 12; $i++) {
								    $timestamp = mktime(0, 0, 0, $i, 1);
								    $months[date('m', $timestamp)] = date('F', $timestamp);
								}
								foreach ($months as $num => $name) {
						            printf('<option value="%s">%s</option>', $num, $name);
						        }
						        
							?>
						</select>
						<select name="card_exp_year" id="cc_exp_year">
							<option value=""><?php _e('Year', 'woocommerce') ?></option>
							<?php
								$years = array();
								for ($i = date('Y'); $i <= date('Y') + 15; $i++) {
								    printf('<option value="%u">%u</option>', $i, $i);
								}
							?>
						</select>
					</p>
					<p class="form-row form-row-last">
						<label for="card_cvv"><?php _e("Card security code", 'woocommerce') ?> <span class="required">*</span></label>
						<input type="text" class="input-text" id="cc_cvv" name="card_cvv" maxlength="4" style="width:45px" />
						<span class="help payhub_card_cvv_description"></span>
					</p>
					<div class="clear"></div>
				</fieldset>

				<?php
			}


				/**
		     * Validate the payment form
		     */
			function validate_fields() {
				
										
				
				#$card_data 			= isset($_POST['payjunction_card_type']) ? $_POST['payjunction_card_type'] : '';
				$card_data 		= isset($_POST['card_number']) ? $_POST['card_number'] : '';
				$card_cvv 			= isset($_POST['card_cvv']) ? $_POST['card_cvv'] : '';
				$card_exp_month		= isset($_POST['card_exp_month']) ? $_POST['card_exp_month'] : '';
				$card_exp_year 		= isset($_POST['card_exp_year']) ? $_POST['card_exp_year'] : '';
				
					
				// Check card security code
				/*
				if(!ctype_digit($card_cvv)) {
					$woocommerce->add_error(__('Card security code is invalid (only digits are allowed)', 'woothemes'));
					return false;
				}
		
				
				if((strlen($card_cvv) != 3 && in_array($card_type, array('Visa', 'MasterCard', 'Discover'))) || (strlen($card_csc) != 4 && $card_type == 'American Express')) {
					$woocommerce->add_error(__('Card security code is invalid (wrong length)', 'woothemes'));
					return false;
				}
				
		
				// Check card expiration data
				if(!ctype_digit($card_exp_month) || !ctype_digit($card_exp_year) ||
					 $card_exp_month > 12 ||
					 $card_exp_month < 1 ||
					 $card_exp_year < date('Y') ||
					 $card_exp_year > date('Y') + 20
				) {
					$woocommerce->add_error(__('Card expiration date is invalid', 'woothemes'));
					return false;
				}
		
				// Check card number
				$card_number = str_replace(array(' ', '-'), '', $card_number);
		
				if(empty($card_number) || !ctype_digit($card_number)) {
					$woocommerce->add_error(__('Card number is invalid', 'woothemes'));
					return false;
				}
		
				
				*/
				return true;
			}

		/**
	 	* Add the Gateway to WooCommerce
	 	**/

		
	



		function process_payment( $order_id ) {
			global $woocommerce;
			$order = new WC_Order( $order_id );

			try {
				//set credentials for transaction
				payhubTransaction::setCredentials($this->orgid, $this->api_username, $this->api_password);
				payhubTransaction::setTerminalId($this->tid);
				payhubTransaction::setDebug(true);

				var_dump($order->billing_state);
				
				$wooresponse = payhubTransaction::sale(array(
					"payment_type" => "credit",
					"card_data_type" => "pan",
					"card_data" => $_POST['card_number'],
					"card_exp_month" => $_POST['card_exp_month'],
					"card_exp_year" => $_POST['card_exp_year'],
					"card_cvv" => $_POST['card_cvv'],
					"amount" => $order->order_total,
					"cust_first_name" => $order->billing_first_name,
					"cust_last_name" => $order->billing_last_name,
					"cust_email" => $order->billing_email,
					"cust_phone" => $order->billing_phone,
					"billing_address1" => $order->billing_address_1,
					"billing_address2" => $order->billing_address_2,
					"billing_city" => $order->billing_city,
					"billing_state" => $order->billing_state,
					"billing_zip" => $order->billing_postcode,
					"note" => $order_id . ", " . $order->user_id
				));
  

				
			} catch(Exception $error_message) {
				}

			if ($wooresponse->result_text == "SUCCESS") :

				$order->add_order_note( __('Transaction completed', 'woothemes') . ' (PayHub Transaction ID: ' . $wooresponse->transaction_id);
				
				//$order->payment_complete();
				$order->payment_complete();
				

				// Remove cart
				
				//$woocommerce->cart->empty_cart();
				// Empty awaiting payment session
				unset($_SESSION);

				// Return thank you page redirect
				return array(
					'result' 	=> 'success',
					'redirect'	=> add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, get_permalink(get_option('woocommerce_thanks_page_id'))))
					);

			else :
				$order->update_status('failed');
				$woocommerce->add_error(__('Payment Error:  ', 'woothemes') . $wooresponse->result_text);
				$woocommerce->add_error(__('Payment Error:  ', 'woothemes') . $wooresponse->response_code);
				$order->add_order_note( __('Transaction Failed', 'woothemes') . ' (PayHub Response Code: ' . $wooresponse->response_code);
				$order->add_order_note( __('Transaction Failed', 'woothemes') . ' (Failed due to: ' . $wooresponse->result_text);
				return;
			endif;

			
		}


	}
}
		function woocommerce_add_payhub_gateway( $methods ) {
			$methods[] = 'WC_PayHub_Gateway';
			return $methods;
		}
		add_filter('woocommerce_payment_gateways', 'woocommerce_add_payhub_gateway');
		