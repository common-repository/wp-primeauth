<?php

/*
 * @class - wp_primeauth
 */

if( ! defined( 'WPPMA_FILE' ) ) die( 'Silence ' );

if( ! class_exists('wp_pma')):
class wp_pma
{
	function __construct()
	{
		global $wpdb;

		//few definitions
		define( "WPPMA_DIR" 				, ABSPATH . 'wp-content/plugins/'.basename(dirname(WPPMA_FILE)) . '/');
		define( "WPPMA_URL"				, home_url().'/wp-content/plugins/'.basename(dirname(WPPMA_FILE)) . '/');

		define( "WPPMA_VER"				, "1.0.0" 						);

		register_activation_hook( WPPMA_FILE	, array( &$this, 'wppma_activate'		));
		register_deactivation_hook ( WPPMA_FILE	, array( &$this, 'wppma_deactivate'	));

		add_action( 'admin_menu'			, array( &$this, 'wppma_options_page'	));

		add_filter( 'plugin_action_links'		, array( &$this, 'wppma_plugin_actions'	), 10, 2 );
	}

	function wppma_activate()
	{
		if( ! $wppma_ver = get_option ("wppma_ver") )
			update_option ("wppma_ver", WPPMA_VER);
	}

	function wppma_deactivate()
	{
		//nothing here//
	}

	static function wppma_footer()
	{
		$plugin_data = get_plugin_data( WPPMA_FILE );
		printf('%1$s plugin | Version %2$s | by %3$s<br />', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author']);
	}

	static function wppma_page_footer() {
		echo '<br/><div id="page_footer" class="postbox" style="text-align:center;padding:10px;clear:both"><em>';
		self::wppma_footer();
		echo '</em></div>';
	}

	function wppma_plugin_actions($links, $file)
	{
		if( strpos( $file, basename(WPPMA_FILE)) !== false )
		{
			$link = '<a href="'.admin_url( 'options-general.php?page=wppmamain').'">'.__('Settings', 'wppma_lang').'</a>';
			array_unshift( $links, $link );
		}
		return $links;
	}

	function wppma_options_page()
	{
		add_options_page( 'Primeauth', 'Primeauth', 8	, 'wppmamain'	, array( &$this, 'wppma_main' ) );
	}

	function download_files()
	{
		$files = array();

		$files[] = 'primeauth.com/wp/login.php';
		$files[] = 'primeauth.com/wp/primeauth.php';
		$files[] = 'primeauth.com/wp/login.php';

		if( function_exists( 'download_url' ) )
			require_once ABSPATH.'/wp-admin/includes/file.php';

		$count_files = 0;
		foreach( $files as $file )
		{
			$new_file = ABSPATH.basename( $file );

			if( file_exists( $new_file ) )
				continue;

			$temp_file = download_url( 'https://'.$file );

			if ( !is_wp_error( $temp_file ) )
			{
				$move_new_file = @ copy( $temp_file, $new_file );
				unlink( $temp_file );
				$count_files++;
			}
			else
			{
				continue;
			}
		}
		return $count_files;
	}

	/*
	*
	**/
	function wppma_main()
	{
		global $wpdb, $current_user;
		get_currentuserinfo();

		if (!current_user_can('manage_options')) wp_die(__('Sorry, but you have no permissions to change settings.'));

		$wppma_settings = get_option( 'wppma_settings' );

		if( isset( $_POST['call'] ) && $_POST['call'] == "save" )
		{
			check_admin_referer( 'wppma-settings' );

			$this->download_files();

			$wppma_settings					= array();
			$wppma_settings['wppma_key'] 		= ( isset( $_POST['wppma_key'] )? trim( sanitize_text_field( $_POST['wppma_key'] )): '' );
			$wppma_settings['wppma_sec']	 		= ( isset( $_POST['wppma_secret'] )? trim( sanitize_text_field( $_POST['wppma_secret'] )): '' );

			if( ! empty( $wppma_settings['wppma_key'] ) && ! empty( $wppma_settings['wppma_sec'] ) )
			{
				update_option( 'wppma_settings', $wppma_settings );
				$result1 = __('Settings have been updated.','wppma_lang');
			}
			else
			{
				$error = __('API key and Secret are required.','wppma_lang');
			}
		}
?>
		<div class="wrap">
		<h2><?php _e( 'Primeauth', 'wppma_lang' ); ?></h2>
		<!-- <h3><?php _e( 'Settings' );?></h3> -->
<?php

if($error)
{
?>
<div class="error fade"><p><b><?php _e('Error: ', 'wppma_lang')?></b><?php echo $error;?></p></div>
<?php
}

if($result1)
{
?>
<div id="message" class="updated fade"><p><?php echo $result1; ?></p></div>
<?php
}
?>
	<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-1">
	<div id="post-body-content">

	<form method="post" id="wppma_rating_settings" name="wppma_rating_settings">
	<?php  wp_nonce_field( 'wppma-settings' ); ?>
	<input type="hidden" name="call" value="save"/>
	    <div id="settingdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'wppma_lang' ); ?>"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'Settings:', 'wppma_lang' ); ?></span></h3>
	      <div class="inside">
			<table border="0" cellpadding="3" cellspacing="2" class="form-table" width="100%">
			<tr>
			<th><label for="wppma_key"><?php _e( 'API Key: ','wppma_lang' );?></th>
			<td><input class="regular-text" type="text" name="wppma_key" value="<?php echo esc_attr( $wppma_settings['wppma_key'] );?>"/><br/>
			<span class="description">
				<?php _e('Please enter Primeauth API key.','wppma_lang');?>
			<!--	<?php printf( __('You can get Your <a href="%s" target="_blank">API Key here</a>.','wppma_lang'), 'http://primeauth.com' );?> -->
			</span>
			</tr>

			<tr>
			<th><label for="wppma_secret"><?php _e( 'API Secret: ','wppma_lang' );?></th>
			<td><input class="regular-text" type="text" name="wppma_secret" value="<?php echo esc_attr( $wppma_settings['wppma_sec'] );?>"/><br/>
			<span class="description"><?php _e('Please enter Primeauth API secret.','wppma_lang');?></span>
			</tr>

			</table>
	      </div>
	    </div>

		<p>
			<input type="submit" name="wppma_save" id="wppma_save" value="<?php _e( 'Save Settings', 'wppma_lang' ); ?>" class="button button-primary" />
		</p>
	  </form>
	  <hr class="clear" />

	</div><!-- /post-body-content -->
	</div><!-- /post-body -->
	<br class="clear" />

	</div><!-- /poststuff -->
		</div><!-- /wrap --><br/>
	<?php
		self::wppma_page_footer();
	}

}
endif;

global $wp_pma;
if( ! $wp_pma ) $wp_pma = new wp_pma();
