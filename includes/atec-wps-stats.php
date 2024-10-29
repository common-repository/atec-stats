<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_wps_stats { 

private function getVisitors($d,$m,$y)
{
	global $wpdb;
	$table=atec_wps_table();
	// @codingStandardsIgnoreStart
	$d=sanitize_key($d);
	$d=($d)!=0?($d):'%';
	$results = $wpdb->get_results($wpdb->prepare('SELECT COUNT(sub.ip_id) AS count FROM (SELECT ip_id FROM %1s WHERE CAST(day(ts) as CHAR) LIKE %s AND month(ts)=%d AND year(ts)=%d GROUP BY ip_id) sub', $table, $d, sanitize_key($m), sanitize_key($y)));
	return $results[0]->count;
	// @codingStandardsIgnoreEnd
}

private function getViews($d,$m,$y)
{
	global $wpdb;
	$table=atec_wps_table();
	// @codingStandardsIgnoreStart
	$d=sanitize_key($d);
	$d=($d)!=0?($d):'%';
	$results = $wpdb->get_results($wpdb->prepare('SELECT COUNT(ip_id) AS count FROM %1s WHERE CAST(day(ts) as CHAR) LIKE %s AND month(ts)=%d AND year(ts)=%d', $table, $d, sanitize_key($m), sanitize_key($y)));
	return $results[0]->count;
	// @codingStandardsIgnoreEnd
}

private function getMaxScale($maxVal) 
{
	if ($maxVal<10) $maxVal=10;
	$maxInt = ceil($maxVal);
	$numDigits = strlen((string)$maxInt)-1; //this makes 2150->3000 instead of 10000
	$dividend = pow(10,$numDigits);
	$maxScale= ceil($maxInt/ $dividend) * $dividend;
	return $maxScale;
}

private function days_in_month($month, $year)
{ return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31); }
	
function __construct($action, $month, $year) {

$scale['visitors']=0;
$scale['views']=0;
$count=array();
$count['visitors'][0]=0;
$count['views'][0]=0;

if ($action==='month')
{
	$days = $this->days_in_month($month,$year);
	for ($d = 1; $d <= $days; $d++) 
	{ 
		$visitors=$this->getVisitors($d,$month,$year);
		if ($visitors>$scale['visitors']) $scale['visitors']=$visitors;
		$count['visitors'][$d]=$visitors;			
		$count['visitors'][0]+=$visitors;

		$views=$this->getViews($d,$month,$year);
		if ($views>$scale['views']) $scale['views']=$views;
		$count['views'][$d]=$views;
		$count['views'][0]+=$views;
	}
					
	$scale['visitors']=$this->getMaxScale($scale['visitors']);
	$scale['views']=$this->getMaxScale($scale['views']);

	$monthName=date_format(date_create($year.'-'.$month.'-1'),'M');
	
	echo '
	<div class="atec-g atec-border-white atec-pt-0">
		<h3><span class="highlight">', esc_attr($count['visitors'][0]) ,'</span> Visitors<span class="span_right">', esc_attr($monthName), ' ', esc_attr($year), '</span></h3>
		<div class="barDIV">
			<div class="legend">', esc_attr($scale['visitors']), '</div>';
			if ($scale['visitors']>0)
			{
				$days = $this->days_in_month($month,$year);
				for ($d = 1; $d <= $days; $d++) 
				{
					$bar=$count['visitors'][$d]/$scale['visitors']*200;
					echo '<div class="pad5"><span title="', esc_attr($count['visitors'][$d]), '" class="bar" style="margin-top: ', esc_attr(200-$bar), 'px; height: ', esc_attr($bar) ,'px;"></span><br>', esc_attr(str_pad($d, 2, '0', STR_PAD_LEFT)), '</div>';
				}
			}
		echo '
		</div>
	</div>
	
	<div class="atec-g atec-border-white atec-pt-0">
		<h3><span class="highlight">', esc_attr($count['views'][0]) ,'</span> Page views<span class="span_right">', esc_attr($monthName), ' ', esc_attr($year), '</span></h3>
		<div class="barDIV">
			<div class="legend">', esc_attr($scale['views']), '</div>';
			$days = $this->days_in_month($month,$year);
			for ($d = 1; $d <= $days; $d++) 
			{
				$bar=$count['views'][$d]/$scale['views']*200;
			echo '<div class="pad5"><span title="', esc_attr($count['views'][$d]), '" class="bar" style="margin-top: ', esc_attr(200-$bar), 'px; height: ', esc_attr($bar) ,'px;"></span><br>', esc_attr(str_pad($d, 2, '0', STR_PAD_LEFT)), '</div>';
			}
		echo '
		</div>
	</div>';
	}

elseif ($action==='year')
{
	
	for ($m = 1; $m <= 12; $m++) 
	{ 
		$visitors=$this->getVisitors(0,$m,$year);
		if ($visitors>$scale['visitors']) $scale['visitors']=$visitors;
		$count['visitors'][$m]=$visitors;			
		$count['visitors'][0]+=$visitors;
	
		$views=$this->getViews(0,$m,$year);
		if ($views>$scale['views']) $scale['views']=$views;
		$count['views'][$m]=$views;
		$count['views'][0]+=$views;
	}
	
	$scale['visitors']=$this->getMaxScale($scale['visitors']);
	$scale['views']=$this->getMaxScale($scale['views']);
	
	echo '
	<div class="atec-g atec-border-white atec-pt-0">
		<h3><span class="highlight">', esc_attr($count['visitors'][0]) ,'</span> Visitors<span class="span_right">', esc_attr($year), '</span></h3>
		<div class="barDIV">
			<div class="legend">', esc_attr($scale['visitors']), '</div>';
			if ($scale['visitors']>0)
			for ($m = 1; $m <= 12; $m++) 
			{
				$bar=$count['visitors'][$m]/$scale['visitors']*200;
				echo '<div class="pad5"><span title="', esc_attr($count['visitors'][$m]), '" class="bar" style="margin-top: ', esc_attr(200-$bar), 'px; height: ', esc_attr($bar) ,'px;"></span>
					<br>', esc_attr(str_pad($m, 2, '0', STR_PAD_LEFT)), '</div>';
			}
		echo '
		</div>
	</div>
	
	<div class="atec-g atec-border-white atec-pt-0">
		<h3><span class="highlight">', esc_attr($count['views'][0]) ,'</span> Page views<span class="span_right">', esc_attr($year), '</span></h3>
		<div class="barDIV">
			<div class="legend">', esc_attr($scale['views']), '</div>';
			for ($m = 1; $m <= 12; $m++) 
			{
				$bar=$count['views'][$m]/$scale['views']*200;
			echo '<div class="pad5"><span title="', esc_attr($count['views'][$m]), '" class="bar" style="margin-top: ', esc_attr(200-$bar), 'px; height: ', esc_attr($bar) ,'px;"></span><br>', esc_attr(str_pad($m, 2, '0', STR_PAD_LEFT)), '</div>';
			}
		echo '
		</div>
	</div>';
}

atec_flush();

echo '
</div>
<div class="atec-g atec-border-white atec-pt-0">
	<h3>Countries overview</h3>
	<div>';
	
	global $wpdb;
	$table=atec_wps_table();
	$whereMonth=($action==='month')?" AND MONTH(`{$table}`.`ts`)={$month} ":'';
	// @codingStandardsIgnoreStart	
	$sql="SELECT count(*) AS count, cc FROM `{$table}_ips` INNER JOIN `{$table}` ON `{$table}_ips`.`id`=`{$table}`.`ip_id` WHERE YEAR(`{$table}`.`ts`)={$year} {$whereMonth} GROUP BY cc;";
	$results = $wpdb->get_results($wpdb->prepare('SELECT count(*) AS count, cc FROM %1s INNER JOIN %1s ON `%1s`.`id`=`%1s`.`ip_id` WHERE YEAR(`%1s`.`ts`)=%d AND CAST(MONTH(`%1s`.`ts`) as CHAR) LIKE %s GROUP BY cc', $table.'_ips', $table, $table.'_ips', $table, $table, $year, $table, $month));
	// @codingStandardsIgnoreEnd
	
	$src=plugins_url('/assets/img/flags/', __DIR__ );
	foreach($results as $result) 
	{ 
		$lower=strtolower($result->cc);
		echo '
		<div class="atec-dilb atec-vat" style="background: #fff; border: solid 1px #ddd; height: 22px; margin: 0px 4px 4px 0;">
			<table cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
				<tr>
					<td width="35"><span class="country_name">', esc_attr($result->cc), '</span></td>
					<td width="30"><img class="country_flag" src="', esc_url($src.strtolower($result->cc).'.svg'), '"></td>
					<td width="50"><span class="country_count">', esc_attr($result->count), '</span></td>
				</tr>
			</table>
		</div>';
	}
	echo '
	</div>';

}}
?>