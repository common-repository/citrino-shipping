<?php
/**
* Plugin Name: Citrino Shipping
* Plugin URI: https://citrino.ca/
* Description: Calculate Canada Post Shipping cost based on Citrino price. You must have an account in our website in order to generate your API keys
* Version: 1.0
* Author: Citrine Inc. / Pedro Padron
* Author URI: https://citrino.ca/
**/

/**
 * Exit if accessed directly
**/
if (!defined('ABSPATH')) { 
    exit; 
}

/**
 * Check if WooCommerce is active 
**/
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	exit;
}

function citrino_calculate_shipping($methods) {
	$methods[] = 'Citrino_Shipping_For_WooCommerce_Shipping_Method';
	return $methods;
}

function citrino_shipping_method_init() {
	include_once 'citrino-shipping-for-woocommerce-shipping-method.php';
}

function citrino_add_citrino_admin_options($links) {
	$custom_links = array(
		'<a href="admin.php?page=wc-settings&tab=shipping&section=citrino_shipping">Settings</a>',
		'<a href="mailto:developer@citrinocourier.com?subject=PluginSupport">Support</a>');
		
	return array_merge($custom_links, $links);
}

add_filter('woocommerce_shipping_methods', 'citrino_calculate_shipping');
add_action('woocommerce_shipping_init', 'citrino_shipping_method_init');
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'citrino_add_citrino_admin_options');
?>