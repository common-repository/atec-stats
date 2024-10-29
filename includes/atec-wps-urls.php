<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_wps_urls { function __construct() {

global $wpdb;
$table=atec_wps_table();

echo '
<div class="atec-g atec-border-white atec-pt-0">
	<h3>URLs</h3>
		<table class="atec-table atec-tiny">
			<thead>
				<tr>
				<th>Type</th>
				<th>ID</th>
				<th><span class="', esc_attr(atec_dash_class('admin-page')), '"></span></th>
				<th><span class="', esc_attr(atec_dash_class('rss')), '"></span></th>
				<th>Title</th>
				<th>Link</th>
				<th>Hits</th>
				</tr>
			</thead>
			<tbody>';		
			// @codingStandardsIgnoreStart
			$results = $wpdb->get_results($wpdb->prepare('SELECT url, count FROM %1s ORDER BY count DESC', $table.'_urls'));
			// @codingStandardsIgnoreEnd
			$regUrl = '/([f|p|c]+)_([\d|\|]+)/';
			$reg=preg_replace('/\//','\/',preg_replace('/https?:\/\//','',get_home_url()));
			foreach($results as $result) 
			{ 
				preg_match($regUrl, $result->url, $match);
				if (isset($match[2]))
				{
					$isCat			= str_contains($match[1],'c');
					$isFeed			= str_contains($match[1],'f');
					if ($isCat) 	{ $ex = explode('|',$match[2]); $id = (int) $ex[0]; $page = $ex[1]??0; }
					else { $id = (int) $match[2]; $page=0; }
					$type			= $isCat?'category':get_post_type($id);
					$title				= $isCat?get_cat_name($id):get_the_title($id);
					$url 				= ($isCat?get_category_link($id):get_permalink($id));
					if ($isFeed) $url.='feed/';
					if ($page!==0) 	{ $url=(str_contains($url, '?cat=')?$url.'&paged=':rtrim($url,'/').'/page/').$page; }
					$short_url 	= preg_replace('/(^https?:\/\/)'.$reg.'/', '', $url);
					echo '
					<tr>
						<td>', esc_attr(ucfirst($type)), '</td>
						<td>', esc_attr($id), '</td>
						<td>', esc_attr($isCat?$page:''), '</td>
						<td>', ($isFeed?' <span class="'.esc_attr(atec_dash_class('yes')).'"></span>':''), '</td>
						<td>', esc_html($title), '</td>
						<td><a href="', esc_url($url), '" target="_blank">', esc_url($short_url), '</a></td>
						<td>', esc_attr($result->count), '</td>
					</tr>';						
				}
			}
		echo '
			</tbody>
		</table>
</div>

<div class="atec-g atec-border-white atec-pt-0">
	<h3>REFERER</h3>
		<table class="atec-table atec-tiny">
		<thead><tr><th>REFERER</th><th>Count</th></tr></thead>
		<tbody>';		
		// @codingStandardsIgnoreStart
		$results = $wpdb->get_results($wpdb->prepare('SELECT ref, count FROM %1s ORDER BY count DESC', $table.'_refs'));
		// @codingStandardsIgnoreEnd
		foreach($results as $result) { echo '<tr><td>', esc_attr($result->ref), '</td><td>', esc_attr($result->count), '</td></tr>'; }
	echo '
	</tbody></table>
</div>';

}}

new ATEC_wps_urls();
?>