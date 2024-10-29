<?php
if (!defined('ABSPATH')) die;
wp_cache_delete('atec_wps_version');

$arr=['atec_wps_IP2GEO_path','atec_wps_allow_gstatic','atec_wps_page_views_today'];
foreach($arr as $a) delete_option($a);

global $wpdb;
$table=$wpdb->base_prefix.'atec_stats';
// @codingStandardsIgnoreStart
$arr=['','_ips','_urls','_refs','_tmp'];
foreach($arr as $a) $wpdb->query($wpdb->prepare('DROP TABLE IF EXISTS %1s', $table.$a));
// @codingStandardsIgnoreEnd

global $wp_filesystem; WP_Filesystem();
$wp_filesystem->rmdir(wp_get_upload_dir()['basedir'].'/atec-stats',true);
?>