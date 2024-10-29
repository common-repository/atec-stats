<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_footer { function __construct() {	

global $timestart;

$plugin			= atec_get_plugin(__DIR__);
$wordpress	= 'https://wordpress.org/support/plugin/';
$atec_active = ['cache-apcu','cache-info','dir-scan','system-info','web-map-service'];

echo '
<div class="atec-footer atec-center atec-fs-12">
	<span class="atec-ml-10" style="float:left;">
		<span class="atec-fs-12" title="', esc_attr__('Execution time','atec-stats'), '">
			<span class="atec-fs-12" class="',esc_attr(atec_dash_class('clock')), '"></span> ', 
			esc_attr(intval((microtime(true) - $timestart)*1000)), 
			' <span class="atec-fs-10">ms</span>
		</span> &middot; <a class="atec-nodeco" href="',esc_url(get_admin_url().'admin.php?page=atec_group'),'">atec-',  esc_attr__('plugins','atec-stats'), ' – ', esc_attr__('Dashboard','atec-stats'), '</a>
	</span>
	<span style="width: fit-content;" class="atec-dilb atec-right atec-mr-10">
		© 2023/24 <a href="https://atecplugins.com/" target="_blank" class="atec-nodeco">atecplugins.com</a>
	</span>
</div>';

atec_reg_inline_script('footer','jQuery("#atec_loading").css("opacity",0);', true);

}}

new ATEC_footer();
?>
