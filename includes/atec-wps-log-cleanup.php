<?php
if (!defined('ABSPATH')) { exit; }

function atec_wps_ip_to_int(string $ipaddress) 
{
	$pton = @inet_pton($ipaddress);
	if (!$pton) { return 0; }
    $number = '';
    foreach (unpack('C*', $pton) as $byte) { $number .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT); }
    return (int) base_convert(ltrim($number, '0'), 2, 10);
}

function atec_wps_log_cleanup($atec_wps_IP2GEO_path)
{	
	$atec_wps_cleanup='atec_wps_cleanup';
	if (get_transient($atec_wps_cleanup)===true) return;
	set_transient($atec_wps_cleanup,true,3600); // 3600 is a fallback option
	global $wpdb;

	$table=atec_wps_table();
	// @codingStandardsIgnoreStart
	$results = $wpdb->get_results($wpdb->prepare('SELECT * FROM %1s ORDER BY `ip` LIMIT 50', $table.'_tmp'));
	
	if (!empty($results))
	{
		$db 			= null;
		$lastIp 		= null;
		$lastIpId	= null;	
		
		$refRecord = [];
		$urlRecord = [];

		foreach($results as $result)
		{
			$start=microtime(true);
			$ip2int=atec_wps_ip_to_int($result->ip);
			if ($lastIp!==$ip2int)
			{
				$ips = $wpdb->get_results($wpdb->prepare('SELECT id FROM %1s WHERE `ip` LIKE %s', $table.'_ips', $ip2int));
				if (empty($ips))
				{
					if (!$db)
					{
						require_once(__DIR__.'/atec_wps_IP2GEO.php');
						$db = new \IP2Location\Database($atec_wps_IP2GEO_path, \IP2Location\Database::FILE_IO);
					}
					$records = $db->lookup($result->ip, \IP2Location\Database::ALL);
					$cc=$records['countryCode']??'-';
					$wpdb->insert($table.'_ips', array('cc'=>$cc, 'ip'=>$ip2int), array('%s', '%d'));
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

			$lastIp=$ip2int;
		}		
	}

	$results = $wpdb->get_results($wpdb->prepare('SELECT count(ip_id) AS count FROM %1s WHERE  ts = DATE(NOW());', $table));
	update_option('atec_wps_page_views_today',$results[0]->count, 'auto');
	// @codingStandardsIgnoreEnd
	delete_transient($atec_wps_cleanup);
}
?>