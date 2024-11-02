<?php
if ( !defined('ABSPATH') ) { die; }
/**
* Plugin Name:  atec Stats
* Plugin URI: https://atecplugins.com/
* Description: Lightweight, beautiful and GDPR compliant WP statistics, including countries map.
* Version: 1.0.13
* Author: Chris Ahrweiler
* Author URI: https://atec-systems.com
* License: GPL2
* License URI:  https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:  atec-stats
*/
 
function atec_wps_table() { global $wpdb; return $wpdb->base_prefix.'atec_stats'; }

if (is_admin())
{ 
	wp_cache_set('atec_wps_version','1.0.13');
	register_activation_hook( __FILE__, function() { require_once('includes/atec-wps-activation.php'); });
	require_once('includes/atec-wps-install.php');
}
else
{
	add_action('wp_enqueue_scripts', function()
	{
		$id='atec_wps_ajax_script';
		wp_register_script($id, plugin_dir_url(__FILE__).'assets/js/atec-wps-ajax.min.js', array('jquery'), '1.0.0', array('in-footer'=>false));
		wp_localize_script($id, 'atec_wps_ajax_obj', array( 'ajaxurl' => admin_url('admin-ajax.php')));
		wp_enqueue_script($id);
	});
	
	add_action('wp_head', function() 
	{ 
		if (is_user_logged_in()) return;
		$isCat=is_category();
		$id = $isCat?get_queried_object()->term_id:get_queried_object_id();
		if (!in_array(get_post_type($id),['page','post','product']) && !$isCat) return;
		if ($isCat) { $suffix='c'; $id.='|'.get_query_var('paged'); }
		else $suffix = 'p';
		if (is_feed()) $suffix.='f';
		$sId='atec_wps_script';
		wp_register_script($sId, false, array('jquery'), '1.0.0', false); wp_enqueue_script($sId); 
		wp_add_inline_script($sId, '
		var atec_wps_called=false;
		function atec_wps_run() { if (!atec_wps_called) atec_wps_ajax("'.esc_attr($suffix.'_'.$id).'", encodeURIComponent(document.referrer)); atec_wps_called=true; }
		function atecAddListener (item) { document.addEventListener(item, atec_wps_run, { once: true }); }
		["click","scroll"].forEach (atecAddListener);		
		');
	});
}

function atec_wps_log_ajax($args) 
{
	if (isset($_SERVER['QUERY_STRING'])) 
	{ 
		$ex=explode('&',sanitize_text_field(wp_unslash($_SERVER['QUERY_STRING'])));
		if (isset($ex[0]))
		{
			if (($url=explode('=',$ex[0])[1]??'')!=='')
			{
				if (($ip=isset($_SERVER['REMOTE_ADDR'])?sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])):'')!=='')
				{
					$ref = isset($ex[1])?str_replace(['3A2F2F','2F'],['://','/'],explode('=',$ex[1])[1]??''):''; 
					global $wpdb;
					$table=atec_wps_table();
					// @codingStandardsIgnoreStart
					$result=$wpdb->insert($table.'_tmp', array('ip'=>$ip, 'url'=>$url, 'ref'=>$ref));
					// @codingStandardsIgnoreEnd
					if (!get_transient('atec_wps_cleanup')) atec_wps_log_cleanup_include();
				}
			}
		}
	}
    wp_die();
}
add_action( 'wp_ajax_nopriv_atec_wps_log_ajax', 'atec_wps_log_ajax' );

function atec_wps_log_cleanup_include($force=false)
{
	$atec_wps_IP2GEO_path=get_option('atec_wps_IP2GEO_path');
	if ($atec_wps_IP2GEO_path) 
	{ 
		require_once('includes/atec-wps-log-cleanup.php'); 
		(new ATEC_wps_log_cleanup)->atec_wps_log_cleanup($atec_wps_IP2GEO_path); 
	}
	else { $notice=['type'=>'warning', 'message'=>'IP2GEO DB not found. Please deactivate/activate the plugin to download the DB file.']; update_option('atec_wps_debug',$notice,false); }
}
?>