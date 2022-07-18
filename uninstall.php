<?php
	if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {		// If uninstall is not called from WordPress, exit
		exit();	
	}	
	global $wpdb;
	$table_name = $wpdb->prefix . 'wp2sfba_facebook_users';		  	
	$wpdb->query( "DROP TABLE IF EXISTS $table_name" ); 	// Delete -------- Facebook user table. 	
	delete_option( 'wp2sfba_appid' ); 						// Delete -------- Facebook app id.
	delete_option( 'wp2sfba_appsecretkey' );				// Delete -------- Facebook app secret key.
	delete_option( 'wp2sfba_skip_fb_auth' );				// Delete -------- Skip Facebook Authentication Link	 
	/*
	$meta_type  = 'user';	
	$user_id    = 0;  	
	$meta_key   = 'transaction_pin';	
	$meta_value = '';  	
	$delete_all = true;	
	delete_metadata( $meta_type, $user_id, $meta_key, $meta_value, $delete_all );		 		
	delete_metadata( $meta_type, $user_id, 'ehlcoin_balance', $meta_value, $delete_all );  
	*/
?>