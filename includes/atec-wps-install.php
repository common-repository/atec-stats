<?php
if (!defined( 'ABSPATH' )) { exit; }

if (!defined('ATEC_INIT_INC')) require_once('atec-init.php');
add_action('admin_menu', function() { atec_wp_menu(__DIR__,'atec_wps','Stats'); } );

add_action('init', function() 
{ 
	atec_admin_debug('Stats','wps');
	
	function atec_wps_admin_bar($wp_admin_bar): void
	{
		if (!($visits=get_option('atec_wps_page_views_today'))) return;
		$nonce = wp_create_nonce(atec_nonce());
		$link=get_admin_url().'admin.php?page=atec_wps&_wpnonce='.esc_attr($nonce);
			$args = array('id' => 'atec_wps_admin_bar', 'title' => '<span style="font-size:12px;"><img title="Page views today" src="'. plugins_url( '/assets/img/atec_wps_icon_admin.svg', __DIR__ ) .'" style="vertical-align: bottom; height:14px; margin:9px 4px 9px 0;">'.number_format((int) $visits,0).'</span>', 'href' => $link );
		$wp_admin_bar->add_node($args);
	}
	add_action('admin_bar_menu', 'atec_wps_admin_bar', PHP_INT_MAX);

    if (in_array($slug=atec_get_slug(), ['atec_group','atec_wps']))
	{ 
		if (!defined('ATEC_TOOLS_INC')) require_once('atec-tools.php');	
		add_action( 'admin_enqueue_scripts', function() { atec_reg_style('atec',__DIR__,'atec-style.min.css','1.0.002'); });
		
		if ($slug!=='atec_group')
		{
			function atec_wps(): void { require_once('atec-wps-dashboard.php'); }
			add_action( 'admin_enqueue_scripts', function()  
			{ 
				atec_reg_style('atec_wps',__DIR__,'atec-wps.min.css','1.0.0');
				atec_reg_style('atec_check',__DIR__,'atec-check.min.css','1.0.001');
			});

			if (str_contains(add_query_arg(null,null),'action=map')) { @require_once('atec-wps-map-js-pro.php'); }
			
		}
	}  	
});
?>