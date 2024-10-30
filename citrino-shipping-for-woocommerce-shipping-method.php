<?php

class Citrino_Shipping_For_WooCommerce_Shipping_Method  extends WC_Shipping_Method {

    public function __construct() 
    {
		$this->id                 = 'citrino_shipping_by_citrino'; 
		$this->method_title       = 'Citrino Shipping';  
		$this->title = 'Citrino Shipping';
		$this->method_description .= '<br><br>To use this plugin, enter your API Username and Password and enable it. You will see the shipping options in the shopping cart, you do not need to configure as a shipping methods in WooCommerce Shipping Zone.<br><br><b>NOTE:</b> you need to entered the weight of all your products for this plugin to work.';

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		
		$this->init_form_fields();
		$this->init_settings();

    }

    public function init_form_fields() 
    {
		$general_options = get_option('woocommerce_citrino_shipping_by_citrino_settings');

		if (!isset($general_options['citrino_api_username']) || $general_options['citrino_api_username'] == '') {
			$user_name_description = '<span style="color: Red">Citrino API User Name.</span>';
		}
		else {
			$user_name_description = 'Citrino API User Name';
		}

		if (!isset($general_options['citrino_api_password']) || $general_options['citrino_api_password'] == '') {
			$password_description = '<span style="color: Red">Citrino API Password<span style="color: Red">';
		}
		else {
			$password_description = 'Citrino API Password';
        }
        
        if (!isset($general_options['citrino_origin_postal_code']) || $general_options['citrino_origin_postal_code'] == '') {
			$postal_code_description = '<span style="color: Red">Origin Postal Code<span style="color: Red">';
		}
		else {
			$postal_code_description = 'Origin Postal Code';
		}

		$this->form_fields = array(
			'enabled' => array(
				'title' => 'Enable/Disable',
				'label' => 'Enable this option to turn on this shipping method.',
				'default' => 'yes',
				'type' => 'checkbox'),
			'citrino_api_username' => array(
				'title' => 'API Username',
				'description' => $user_name_description,
				'default' => '',
				'type' => 'text'
			),
			'citrino_api_password' => array(
				'title' => 'API Password',
				'description' => $password_description,
				'default' => '',
				'type' => 'text'
            ),
			'citrino_origin_postal_code' => array(
				'title' => 'Origin Postal Code',
				'description' => $postal_code_description,
				'default' => '',
				'type' => 'text'
			)
        );
    }
    
    public function calculate_shipping($package = Array()) 
    {
		if ($this->get_option('enabled') == 'no') {
			return;
		}

		include_once 'citrino-shipping-for-woocommerce-canada-post.php';
		$canada_post = new Citrino_Shipping_For_WooCommerce_Canada_Post();
		$rates = $canada_post->calculate_shipping($package["contents"], $package["destination"]);
		
		foreach ($rates as $rate) {
			$this->add_rate($rate);
		}
	}

}

?>