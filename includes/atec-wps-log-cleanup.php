<?php
if (!defined('ABSPATH')) { exit; }

class ATEC_wps_log_cleanup {

private function ipv4ToDecimal($ip)	{ return sprintf('%u', ip2long($ip)); }
private function ipv6ToDecimal($ipv6) { return (string) gmp_import(inet_pton($ipv6)); }
private function isIpv6($ip) { return (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) ? true : false; }

public function atec_wps_log_cleanup($atec_wps_IP2GEO_path)
{	
	$atec_wps_cleanup='atec_wps_cleanup';
	if (get_transient($atec_wps_cleanup)===true) return;
	set_transient($atec_wps_cleanup,true,3600); // 3600 is a fallback option
	global $wpdb;

	$table=atec_wps_table();
	// @codingStandardsIgnoreStart
	$results = $wpdb->get_results($wpdb->prepare('SELECT * FROM %1s ORDER BY `ip` LIMIT 50', $table.'_tmp'));
	
	$IP2LOC='IP2LOCATION-LITE-DB1';
	$ip4Path=$atec_wps_IP2GEO_path.$IP2LOC.'.BIN';
	$ip6Path=$atec_wps_IP2GEO_path.$IP2LOC.'.IPV6.BIN';
	
	if (!empty($results))
	{
		$db 			= [];
		$lastIp 		= null;
		$lastIpId	= null;	
		
		$refRecord = [];
		$urlRecord = [];

		foreach($results as $result)
		{
			$IPver=$this->isIpv6($result->ip)?6:4;
			$ip2dec=($IPver===6)?$this->ipv6ToDecimal($result->ip):$this->ipv4ToDecimal($result->ip);
			if ($lastIp!==$ip2dec)
			{
				$ips = $wpdb->get_results($wpdb->prepare('SELECT id FROM %1s WHERE `ip` LIKE %s', $table.'_ips', $ip2dec));
				if (empty($ips))
				{
					if (!isset($db[$IPver]))
					{
						require_once(__DIR__.'/atec_wps_IP2GEO.php');
						$db[$IPver] = new \IP2Location\Database($IPver===6?$ip6Path:$ip4Path, \IP2Location\Database::FILE_IO);
					}
					$records = $db[$IPver]->lookup($result->ip, \IP2Location\Database::ALL);
					$cc=$records['countryCode']??'-';
					$wpdb->insert($table.'_ips', array('cc'=>$cc, 'ip'=>$ip2dec), array('%s', '%s'));
					$lastIpId=$wpdb->insert_id;
				}
				else $lastIpId=$ips[0]->id;
			}
			
			if (!isset($urlRecord[$result->url]))
			{
				$urls = $wpdb->get_results($wpdb->prepare('SELECT count FROM %1s WHERE `url` LIKE %s', $table.'_urls', $result->url));
				if (empty($urls)) { $wpdb->insert($table.'_urls', array('url'=>$result->url)); $urlRecord[$result->url]['c']=1; }
				else $urlRecord[$result->url]['c']=$urls[0]->count+1;
			}
			else $urlRecord[$result->url]['c']++;
			if ($urlRecord[$result->url]['c']!==1) $wpdb->update($table.'_urls', array('count'=>$urlRecord[$result->url]['c']), array('url'=>$result->url));

			if ($result->ref!=='')
			{
				$host = wp_parse_url($result->ref, PHP_URL_HOST);
				if ($host)
				{
					if (!isset($refRecord[$host]))
					{
						$refs = $wpdb->get_results($wpdb->prepare('SELECT count FROM %1s WHERE `ref` LIKE %s', $table.'_refs', $host));
						if (empty($refs)) { $wpdb->insert($table.'_refs', array('ref'=>$host)); $refRecord[$host]['c']=1; }
						else $refRecord[$host]['c']=$refs[0]->count+1;
					}
					else $refRecord[$host]['c']++;
					if ($refRecord[$host]['c']!==1) $wpdb->update($table.'_refs', array('count'=>$refRecord[$host]['c']), array('ref'=>$host));
				}
			}
			
			$wpdb->insert($table, array('ts'=>$result->ts, 'ip_id'=>$lastIpId));
			$wpdb->delete($table.'_tmp', array('id'=>$result->id));

			$lastIp=$ip2dec;
		}		
	}

	$results = $wpdb->get_results($wpdb->prepare('SELECT count(ip_id) AS count FROM %1s WHERE  ts = DATE(NOW());', $table));
	update_option('atec_wps_page_views_today',$results[0]->count, 'auto');
	// @codingStandardsIgnoreEnd
	delete_transient($atec_wps_cleanup);
}

function __construct() {
}}
?>