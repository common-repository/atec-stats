<?php
if (!defined( 'ABSPATH' )) { exit; }

function atec_load_pll($file,$slug,$domain)
{
	$domain='atec_'.$domain;
	$mo_file = plugin_dir_path($file) . 'languages/atec-'.esc_attr($slug).'-' . esc_attr(str_replace('_formal','',get_locale())) . '.mo';
	load_textdomain( $domain, $mo_file ); 
	load_plugin_textdomain( $domain, false, dirname( plugin_basename( $file ) ) . '/languages/' ); 
}
?>