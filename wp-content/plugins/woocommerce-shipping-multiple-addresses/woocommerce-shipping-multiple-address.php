<?php
/**
 * Backwards compat.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_plugins = get_option( 'active_plugins', array() );
foreach ( $active_plugins as $key => $active_plugin ) {
	if ( strstr( $active_plugin, '/woocommerce-shipping-multiple-address.php' ) ) {
		$active_plugins[ $key ] = str_replace( '/woocommerce-shipping-multiple-address.php', '/woocommerce-shipping-multiple-addresses.php', $active_plugin );
	}
}
update_option( 'active_plugins', $active_plugins );
