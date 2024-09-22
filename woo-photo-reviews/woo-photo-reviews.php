<?php
/**
 * Plugin Name: Photo Reviews for WooCommerce
 * Plugin URI: https://villatheme.com/extensions/woocommerce-photo-reviews/
 * Description: Allow you to automatically send email to your customers to request reviews. Customers can include photos in their reviews.
 * Version: 1.2.12
 * Author: VillaTheme
 * Author URI: http://villatheme.com
 * Text Domain: woo-photo-reviews
 * Domain Path: /languages
 * Copyright 2018-2024 VillaTheme.com. All rights reserved.
 * Requires Plugins: woocommerce
 * Requires at least: 5.0
 * Tested up to: 6.5
 * WC requires at least: 7.0
 * WC tested up to: 9.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'VI_WOO_PHOTO_REVIEWS_VERSION', '1.2.12' );

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
define( 'WOO_PHOTO_REVIEWS_DIR',  plugin_dir_path( __FILE__ ) );
define( 'WOO_PHOTO_REVIEWS_INCLUDES', WOO_PHOTO_REVIEWS_DIR . "includes" . DIRECTORY_SEPARATOR );

if ( ! class_exists( 'VI_Woo_Photo_Reviews' ) ) {
	class VI_Woo_Photo_Reviews {

		public function __construct() {
			//compatible with 'High-Performance order storage (COT)'
			add_action( 'before_woocommerce_init', array( $this, 'before_woocommerce_init' ) );
			if ( is_plugin_active( 'woocommerce-photo-reviews/woocommerce-photo-reviews.php' ) ) {
				return;
			}
			add_action( 'plugins_loaded', array( $this, 'init' ) );
			add_filter(
				'plugin_action_links_woo-photo-reviews/woo-photo-reviews.php', array(
					$this,
					'settings_link'
				)
			);
		}
		public function init() {
			$include_dir = plugin_dir_path( __FILE__ ) . 'includes/';
			if ( ! class_exists( 'VillaTheme_Require_Environment' ) ) {
				include_once $include_dir . 'support.php';
			}

			$environment = new VillaTheme_Require_Environment( [
					'plugin_name'     => 'Photo Reviews for WooCommerce',
					'php_version'     => '7.0',
					'wp_version'      => '5.0',
					'require_plugins' => [
						[
							'slug' => 'woocommerce',
							'name' => 'WooCommerce',
							'required_version' => '7.0',
						]
					]
				]
			);

			if ( $environment->has_error() ) {
				return;
			}

			$init_file = WOO_PHOTO_REVIEWS_INCLUDES . "includes.php";
			require_once $init_file;
		}
		public function before_woocommerce_init() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		}


		public function settings_link( $links ) {
		    $settings_link = sprintf('<a href="admin.php?page=woo-photo-reviews" title="%s">%s</a>',esc_html__( 'Settings', 'woo-photo-reviews' ),esc_html__( 'Settings', 'woo-photo-reviews' ));
			array_unshift( $links, $settings_link );

			return $links;
		}

	}
}

new VI_Woo_Photo_Reviews();