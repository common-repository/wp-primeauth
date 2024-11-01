<?php
/*
Plugin Name: WP Primeauth
Plugin URI: http://www.Primeauth.com
Description: WP Primeauth - The authentication for the 21st century
Version: 1.0.2
Author: Primeauth
Author URI: http://www.Primeauth.com/
*/

define( "WPPMA_FILE", __FILE__ );
require_once dirname( WPPMA_FILE ).'/includes/wp_primeauth.php';

function hook_lost_your_passwords ( $text ) {
        if ($text == 'Lost your password?'){
            $text .= '<a href="/primeauth.php" class="">
			
			| Log in with Primeauth</a> <br /> ';
        }
    return $text;
}
add_filter( 'gettext', 'hook_lost_your_passwords' );

