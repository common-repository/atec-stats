<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_wps_dashboard { function __construct() {

echo '
<div class="atec-page">';
	
	atec_header(__DIR__,'wps','Stats');	

	echo '
	<div class="atec-main">';
		atec_progress();

		$url		= atec_get_url();
		$nonce = wp_create_nonce(atec_nonce());
		$nav		= atec_clean_request('nav');
			
			if ($nav=='Info') { require_once('atec-info.php'); new ATEC_info(__DIR__,$url,$nonce); }
			else
			{
				
				atec_readme_button_div($url, $nonce,'Statistics');
				atec_wps_log_cleanup_include(true);
		
				$action =atec_clean_request('action');
				if ($action=='') $action='month';
				
				$month =atec_clean_request('month');
				if ($month=='') $month=intval(gmdate('m'));
				else $month=intval($month);
		
				$yearNow=intval(gmdate('Y'));
				$year =atec_clean_request('year');
				if ($year=='') $year=$yearNow;
				else $year=intval($year);
				
				echo '
				<div class="atec-g atec-border atec-mmt-10">
				
						<div class="atec-btn-div">
						<div class="atec-pro-btn">PRO</div>
						<div class="tablenav">';
						atec_nav_button($url,$nonce,'map','','Map',in_array($action, ['map','allow_gstatic']));
						atec_nav_button($url,$nonce,'urls','','URLs',$action=='urls');
						echo '<div class="alignleft"> | </div>';
						atec_nav_button($url,$nonce,'year&month='.$month.'&year='.$yearNow-1,'',$yearNow-1,$action!='map' && $yearNow-1==$year);
						atec_nav_button($url,$nonce,'year&month='.$month.'&year='.$yearNow,'',$yearNow,$action!='map' && $yearNow==$year);
						echo '<div class="alignleft"> | </div>';
						for ($m = 1; $m <= 12; $m++) 
						{ atec_nav_button($url,$nonce,'month&month='.$m.'&year='.$year,'',$m,$action=='month' && $m==$month); }
					echo '
						</div>
					</div>';
					
					atec_flush();
					echo '
					<div class="atec-g atec-g-50">';
					if ($action=='allow_gstatic') 
					{
						update_option('atec_wps_allow_gstatic', true);
						atec_reg_inline_script('atec_wps','window.location.assign("'.esc_url($url).'&action=map&_wpnonce='.esc_attr($nonce).'");');
					}
					elseif ($action=='map') 
					{ 
						if (atec_pro_feature('`Map´ shows the country\'s popularity on a world map, highlighting all countries with their respective visitor count')) 
						{ 
							@include_once('atec-wps-map-pro.php'); 
							if (class_exists('ATEC_wps_map')) new ATEC_wps_map($url, $action, $nonce);
							else atec_missing_class_check();
						}
					}
					elseif ($action=='urls') { require_once('atec-wps-urls.php'); }
					else { require_once('atec-wps-stats.php'); new ATEC_wps_stats($action, $month, $year); }
						
					echo '
					</div>
				</div>';
			}
		echo '
	</div>
</div>';

if (!class_exists('ATEC_footer')) require_once('atec-footer.php');

}}

new ATEC_wps_dashboard;
?>