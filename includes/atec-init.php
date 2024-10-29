<?php
if (!defined( 'ABSPATH' )) { exit; }
define('ATEC_INIT_INC',true);

function atec_nonce(): string { return atec_get_slug().'_nonce'; }
function atec_get_slug(): string { preg_match('/\?page=([\w_]+)/', add_query_arg( NULL, NULL ), $match); return $match[1] ?? ''; }
function atec_get_plugin($dir): string { $plugin=plugin_basename($dir); return substr($plugin,0,strpos($plugin,'/')); }
function atec_group_page($dir): void { if (!class_exists('ATEC_group')) require_once(plugin_dir_path($dir).'includes/atec-group.php'); } 

function atec_wp_menu($dir,$menu_slug,$title,$single=false,$cb=null): void
{ 
	if ($cb==null) { $cb=$menu_slug; }

	$pluginUrl=plugin_dir_url($dir);
	$icon=$pluginUrl . 'assets/img/'.$menu_slug.'_icon_admin.svg';

	if ($single) { add_menu_page($title, $title, 'administrator', $menu_slug, $cb , $icon); }
	else
	{
		global $atec_plugin_group_active;
		$group_slug='atec_group'; 
		
		if (!$atec_plugin_group_active)
		{
			$atec_icon=$pluginUrl . 'assets/img/atec-group/atec_icon_admin.svg';
			add_menu_page('atec-systems','atec-systems', 'administrator', $group_slug, function() use ($dir) { atec_group_page($dir); }, $atec_icon);
			
			$atec_icon=$pluginUrl . 'assets/img/atec-group/atec_support_icon_admin.svg';
			add_submenu_page($group_slug,'Group', '<img src="'.esc_url($atec_icon).'">&nbsp;Dashboard</span>', 'administrator', $group_slug, function() use ($dir) { atec_group_page($dir); } );
			$atec_plugin_group_active=true;
		}
		add_submenu_page($group_slug, $title, '<img src="'.esc_url($icon).'">&nbsp;'.$title, 'administrator', $menu_slug, $cb );
	}
}

function atec_admin_debug($name,$slug): void
{
	$slug='atec_'.$slug.'_debug'; $notice=get_option($slug);
	$name=$name==='Mega Cache'?$name:'atec '.$name;
	if ($notice) { atec_new_admin_notice($notice['type']??'info',$name.': '.$notice['message']??''); delete_option($slug); }
}

function atec_admin_notice($type,$message): void { echo '<div class="notice notice-',esc_attr($type),' is-dismissible"><p>',esc_attr($message),'</p></div>'; }
function atec_new_admin_notice($type,$message): void { add_action('admin_notices', function() use ( $type, $message ) { atec_admin_notice($type,$message); }); }
?>