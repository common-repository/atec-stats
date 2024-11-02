<?php
if (!defined('ABSPATH')) { exit; }

class ATEC_wps_activation { 

private function atec_create_table($table,$sql): bool
{
	global $wpdb;
	$success=true;
	// @codingStandardsIgnoreStart
	if (!$wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table))==$table) 
	{ $success=$wpdb->query($wpdb->prepare('CREATE TABLE %1s '.$sql, $table)); }
	// @codingStandardsIgnoreEnd
	return $success;
}
	
function __construct() {
	
if (!defined('ATEC_TOOLS_INC')) require_once('atec-tools.php');
atec_integrity_check(__DIR__);
	
/** TABLES */

$table=atec_wps_table();
$engine=' ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_general_ci';
$compressed=' ROW_FORMAT=COMPRESSED ';
$sql=' (`ts` DATE NOT NULL , `ip_id` INT UNSIGNED NOT NULL)'.$engine;
$success=$this->atec_create_table($table,$sql);

$sql=' (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `ip` VARCHAR(39) NOT NULL , `url` VARCHAR(14) NOT NULL, `ref` TINYTEXT NOT NULL, PRIMARY KEY (`id`))'.$engine;

$success=$this->atec_create_table($table.'_tmp',$sql);

$sql=' (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `ip` DECIMAL(40,0) NOT NULL, `cc` CHAR(2) NOT NULL DEFAULT "", PRIMARY KEY (`id`))'.$engine;
$success=$success && $this->atec_create_table($table.'_ips',$sql);

$sql=' (`url` VARCHAR(14) NOT NULL , `count` INT UNSIGNED NOT NULL DEFAULT 1, PRIMARY KEY (`url`))'.$compressed.$engine;
$success=$success && $this->atec_create_table($table.'_urls',$sql);

$sql=' (`ref` VARCHAR(255) NOT NULL , `count` INT UNSIGNED NOT NULL DEFAULT 1, PRIMARY KEY (`ref`))'.$compressed.$engine;
$success=$success && $this->atec_create_table($table.'_refs',$sql);

global $wpdb;
$success=true;
// @codingStandardsIgnoreStart
$success=$wpdb->query($wpdb->prepare('ALTER TABLE %1s CHANGE `ip` `ip` DECIMAL(40) NOT NULL', $table.'_ips'));
// @codingStandardsIgnoreEnd

$notice=['type'=>$success?'success':'warning', 'message'=>$success?'Plugin tables created.':'Failed to create plugin tables.'];
update_option('atec_wps_debug',$notice,false);

//** IP2GEO BIN */
global $wp_filesystem; WP_Filesystem();

$upload_dir = wp_get_upload_dir()['basedir'].'/atec-stats';
if (!$wp_filesystem->exists($upload_dir)) $wp_filesystem->mkdir($upload_dir);
$error = '';

$IP2LOC='IP2LOCATION-LITE-DB1';
foreach(['','IPV6.'] as $bin)
{
	if (is_wp_error(($tmp_file = download_url('https://atecplugins.com/WP-Plugins.'.$IP2LOC.$bin.'BIN.zip')))) { $error='Could not download the IP2GEO DB file.'; }
	else 
	{
		$result=unzip_file($tmp_file, $upload_dir);
		if (is_wp_error( $result )) $error='Could not unzip the IP2GEO DB file.';
		$wp_filesystem->delete($tmp_file);
	}
}

if ($error!=='') { $notice['type']='warning'; $notice['message']=$notice['message'].' '.$error; update_option('atec_wps_debug', $notice, false); }
else 
{ 
	update_option('atec_wps_IP2GEO_path', $upload_dir.DIRECTORY_SEPARATOR);
	$notice['message']=$notice['message'].' IP2GEO DB file installed.'; update_option('atec_wps_debug', $notice, false); 
}

}} new ATEC_wps_activation();
?>
