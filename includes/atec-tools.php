<?php
if (!defined( 'ABSPATH' )) { exit; }
define('ATEC_TOOLS_INC',true);

function atec_is_linux() { return (DIRECTORY_SEPARATOR=='/'); }
function atec_fix_separator($str) 
{ 
	if (atec_is_linux()) return $str;
	return str_replace('/',DIRECTORY_SEPARATOR,$str);
}
function atec_trailingslashit( $str ) { return rtrim($str,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR; }

function atec_replace_seperator(&$str) { $str=str_replace(DIRECTORY_SEPARATOR,'/',$str); }

function atec_random_string($length,$lower=false) 
{ 
	$charset = 'abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ'; $string = ''; 
	while(strlen($string)<$length) { $string .= substr($charset,wp_rand(0,61),1); } 
	return $lower?strtolower($string):$string;
}

function atec_htaccess_exists()
{
	global $wp_filesystem; WP_Filesystem();
	return $wp_filesystem->exists(ABSPATH.'.htaccess');
}

function atec_integrity_check($dir,$plugin='')
{
	if ($plugin==='') $plugin=str_replace('/includes','',plugin_basename($dir));
	if (get_option('atec_allow_integrity_check')===true) 
	wp_remote_get('https://atecplugins.com/WP-Plugins/activated.php?plugin='.esc_attr($plugin).'&domain='.get_bloginfo('url')); 
}

function atec_TR_empty() { echo '<tr class="emptyTR1"><td colspan="99"></td></tr><tr class="emptyTR2"><td colspan="99"></td></tr>'; }

function atec_short_string($str,$len=128): string 
{ 
	if ($str=='') return $str;
	return strlen($str)>$len?substr($str, 0, $len).' ...':$str; 
}
function atec_dash_yes_no($enabled): void 
{ 
	echo '<span style="color:', ($enabled?'green':'red'), '" title="', ($enabled?'Enabled':'Disabled'), '" class="', esc_attr(atec_dash_class($enabled?'yes-alt':'warning')), '"></span>'; 
}

function atec_bar_div($time,$max,$threshold1,$threshold2)
{
	echo '
	<div class="atec-barDiv">
		<span class="atec-bar" style="width:',
			esc_attr($time/$max*100), 'px;';
			if ($time>$threshold1) echo ' background: red;'; elseif ($time>$threshold2) echo ' background: orange;';
		echo '">
		</span>
	</div>';
}
	
function atec_empty_tr() { echo '<tr><td colspan="99" class="emptyTR1"></td></tr><tr><td colspan="99" class="emptyTR2"></td></tr>'; }
function atec_dash_class($icon,$class=''): string { return 'dashicons dashicons-'.$icon.($class!==''?' '.$class:''); }

function atec_check_license($licenseCode=null, $siteName=null)
{
	if (!class_exists('ATEC_pro')) @include_once('atec-pro.php');
	if (class_exists('ATEC_pro')) { return (new ATEC_pro)->atec_pro_check_license($licenseCode, $siteName); }
	return false;
}

function atec_integrity_check_banner($dir):void
{
	$plugin=str_replace('/includes','',plugin_basename($dir));
	$link_yes=get_admin_url().'admin.php?page=atec_group&action=integrity&integrity=true&_wpnonce='.esc_attr(wp_create_nonce('atec_license_nonce').'&plugin='.$plugin);
	$link_no=str_replace('integrity=true','integrity=false',$link_yes);
	echo '
	<div class="atec-sticky-left" style="height:36px;" title="Allow one time connection to https://atecplugins.com on plugin activation.">
		<div class="atec-dilb atec-fs-10">
			Connect to atecplugins.com<br>
			<div class="atec-fs-8" style="margin-top: -2px;">One time connection on activation.</div>
		</div>
		<div class="atec-dilb atec-vat atec-mt-5">
			<a style="background: rgba(0, 180, 0, 0.5); color:white !important;" class="atec-integritry atec-fs-12" href="', esc_url($link_yes), '">YES</a>
			<a style="background: rgba(180, 0, 0, 0.5); color:white !important;" class="atec-integritry atec-fs-12" href="', esc_url($link_no), '">NO</a>
		</div>
	</div>';
}

function atec_license_banner($dir):void
{
	$plugin=str_replace('/includes','',plugin_basename($dir));
	$licenseOk=atec_check_license();
	$link=get_admin_url().'admin.php?page=atec_group&license=true&_wpnonce='.esc_attr(wp_create_nonce('atec_license_nonce').'&plugin='.$plugin);
	echo '
	<div class="atec-sticky-right">
		<a class="atec-nodeco atec-', ($licenseOk?'green':'blue') ,'" href="', esc_url($link), '">
			<span class="', esc_attr(atec_dash_class('awards','atec-'.($licenseOk?'green':'blue'))), '" style="margin-right: 4px;"></span>',
			($licenseOk?esc_attr__('PRO version activated','atec-stats'):esc_attr__('Upgrade to PRO version','atec-stats')), '.
		</a>
	</div>';
}

function atec_pro_feature($desc='',$small=false)
{ 
	$licenseOk=atec_check_license()===true; 
	if (!$licenseOk) 
	{ 
		$link=get_admin_url().'admin.php?page=atec_group&license=true&_wpnonce='.esc_attr(wp_create_nonce('atec_license_nonce'));
		$break=str_starts_with($desc,'BREAK#');
		if ($break) 
		{ 
			$desc=str_replace('BREAK#','',$desc); echo '<br>'; 
			echo '<div style="margin-top: 0px;">';
		}
		echo '
		<div class="', ($desc!==''?'atec-dilb':''), '">
			<a class="atec-dilb atec-nodeco atec-blue" href="', esc_url($link), '">';
			atec_badge('PRO feature – please Upgrade','','blue');
		echo '
			</a>
		</div>';
		if ($desc!=='') 
		{
			if ($small) echo '<div class="atec-dilb atec-badge" style="background: #f9f9ff; border: solid 1px #fff; margin: 0 0 0 0;">';
			else echo '<br><div class="atec-pro-box" style="background: #f9f9ff;"><h4 class="atec-mt-0">';
			$ex 	= explode('<br>',$desc);
			$c		= 0;
			foreach ($ex as $t) { $c++; echo esc_html($t).($c<count($ex)?'<br>':''); }
			if ($small) echo '.</div>';
			else echo '.</h4></div>';
		}
		if ($break) echo '</div>';
	}
	return $licenseOk; 
}

function atec_pro_feature_mini($desc='')
{ 
	$licenseOk=atec_check_license();
	if (!$licenseOk)
	{ 
		$link=get_admin_url().'admin.php?page=atec_group&license=true&_wpnonce='.esc_attr(wp_create_nonce('atec_license_nonce'));
		echo '
		<div class="atec-dilb atec-pro-box" style="background: #f9f9ff; padding:2px;">
			<a class="atec-nodeco atec-blue" href="', esc_url($link), '">
				<span style="padding-top: 0px;" class="', esc_attr(atec_dash_class('awards','atec-blue atec-fs-14')), '"></span>
					Upgrade to PRO version <strong>', esc_attr($desc), '.</strong>
			</a>
		</div><br>';
	}
	return $licenseOk;
}

function atec_nav_tab($url, $nonce, $nav, $arr, $break=0, $pro=true, $highlight='', $about=false): void
{
	$iconPath=plugins_url('assets/img/icons/',__DIR__);
	echo '
	<h2 class="nav-tab-wrapper" style="height:', esc_attr($pro?'auto':'33px'), ';">';
		$c 	= 0;
		$reg = '/#([\-|\w]+)\s(.*)/i';
		foreach($arr as $a) 
		{ 
			$c++;
			preg_match($reg, $a, $matches);
			$nice=$matches[2]??$a;
			$nice=str_replace([' ','.','-','/'],'_',$nice);
			$nice=str_replace(['(',')'],'',$nice);
			$active=$nav==$nice;		
			$proNav=$c>$break && $pro;
echo '<div class="atec-dilb" style="margin-right: ', $c===$break?'0.5em':'0', '">';
	if ($pro) echo '<div class="atec-dilb atec-pro" style="margin-left: 10px; padding-bottom: 10px;">', $proNav?'PRO':'&nbsp;', '</div><br class="atec-clear">';
	echo '
	<a href="', esc_url($url), '&nav=', esc_attr($nice), '&_wpnonce=', esc_attr($nonce), '" class="nav-tab ', ($pro?'atec-grey':'atec-blue'), ($active?' nav-tab-active':''), ($nice==$highlight?' atec-under':''), ($proNav?' atec-pro-nav':''), '">';
		if (isset($matches[2])) echo '<img class="nav-icon" src="', esc_url($iconPath.$matches[1].'.svg'), '">', esc_attr($matches[2]);
		else echo esc_attr(preg_replace($reg, '', $a));
	echo '</a>';
echo '</div>';
		}
		echo '
		<div class="atec-dilb atec-right">';
		if ($pro) echo '<div class="atec-dilb atec-pro" style="height:10px;">&nbsp;</div><br class="atec-clear">';
		if ($about) echo '
		<a href="', esc_url($url), '&nav=About&_wpnonce=', esc_attr($nonce), '" class="nav-tab', ($nav==='About'?' nav-tab-active':''), '"><img class="nav-icon" style="margin-right: 0px;" src="', esc_url($iconPath.'about.svg'), '"></a>';
		echo '
		<a href="', esc_url($url), '&nav=Info&_wpnonce=', esc_attr($nonce), '" class="nav-tab atec-mr-10', ($nav==='Info'?' nav-tab-active':''), '"><img class="nav-icon" style="margin-right: 0px;" src="', esc_url($iconPath.'info.svg'), '"></a>';
		echo '
		</div>
	</h2>';
}

function atec_readme_button($url, $nonce): void
{ echo '<a class="atec-nodeco" href="', esc_url($url), '&nav=Info&_wpnonce=', esc_attr($nonce), '"><span class="', esc_attr(atec_dash_class('info','atec-blue')), '"></span></a>'; }

function atec_readme_button_close($url, $nonce): void
{
	echo '<a style="margin-top: 4px;" class="atec-nodeco" href="', esc_url($url), '&nav=&_wpnonce=', esc_attr($nonce), '"><span class="', esc_attr(atec_dash_class('dismiss','atec-blue')), '"></span></a>';
}

function atec_readme_button_div($url, $nonce, $title): void
{
	echo '
	<div>
		<div class="atec-dilb">'; atec_little_block($title); echo '</div>
		<div class="atec-dilb atec-right">
			<span class="atec-dilb atec-bg-w atec-border-tiny atec-box-30 atec-radius-3">'; atec_readme_button($url,$nonce); echo '</span>
		</div>
	</div>';
}

function atec_table_header_tiny($tds,$id='',$class=''): void
{
	echo '<table ', (esc_attr($id!==''?" id=$id":'')) ,' class="atec-table atec-table-tiny atec-fit ', esc_attr($class), '"><thead><tr>';
	foreach ($tds as $td) { echo '<th>', esc_attr($td), '</th>'; }
	echo '</tr></thead><tbody>';
}

function atec_nav_button($url,$nonce,$action,$nav,$button,$primary=false,$simple=false,$blank=false): void
{
	if (!$simple) echo '<div class="alignleft">';
	$href=$url.'&action='.$action.'&nav='.$nav.'&_wpnonce='.$nonce;
	$action=$action===''?'update':$action;
	$dash='';
	if ($action==='update' || str_starts_with($action,'delete')) { $dash=$action==='update'?'update':'trash'; $button=''; }
	elseif (in_array($button,['left','right'])) $dash='arrow-'.$button.'-alt';
	else
	{
		$reg = '/#([\-|\w]+)\s(.*)/i';
		preg_match($reg, $button, $matches);
		if (isset($matches[2])) { $dash=$matches[1]; $button=$matches[2]; }
	}
	echo '
	<a id="', esc_attr($nonce), '" href="', esc_url($href), '"', ($blank?' target="_blank"':'') ,'>
		<button class="button button-', $primary?'primary':'secondary', '">';
			if ($dash!=='') echo '<span style="padding-top:4px;" class="'.esc_attr(atec_dash_class($dash)).'"></span>';
			if ($dash!=='') echo ' '; echo esc_attr($button);
	echo '</button>
	</a>';
	if (!$simple) echo '</div>';
}

function atec_nav_button_confirm($url,$nonce,$action,$nav,$button,$pro=null): void
{
	echo '
	<div class="alignleft atec-btn-bg" style="background: #f0f0f0;">';
	if ($pro)
	{
		$pro=atec_check_license()===true;
		if (!$pro)
		{
			$link=get_admin_url().'admin.php?page=atec_group&license=true&_wpnonce='.esc_attr(wp_create_nonce('atec_license_nonce'));
			echo '
			<a class="atec-nodeco" href="', esc_url($link), '">
				<span class="atec-fs-9 atec-blue atec-dilb">
					<span class="', esc_attr(atec_dash_class('awards','atec-fs-16')), '"></span>Upgrade to PRO version.
				</span>
			</a><br>';
		}
	}
	echo ($pro===false?'':'<input title="Confirm action" type="checkbox" onchange="const $btn=jQuery(this).parent().find(\'button\'); $btn.prop(\'disabled\',!$btn.prop(\'disabled\'));">'), '
	<a href="', esc_url($url), '&action=', esc_attr($action), '&nav=', esc_attr($nav), '&_wpnonce=', esc_attr($nonce),'"><button disabled="true" class="button button-secondary">', (str_contains($action,'delete')?'<span style="padding-top:4px;" class="'.esc_attr(atec_dash_class('trash')).'"></span> ':''), esc_attr($button), '</button>
	</a>
	</div>';
}

function atec_create_button($action,$icon,$enabled,$url,$id,$nonce,$primary=false): void
{
	echo '
	<td>
		<button ', esc_attr(!$enabled)?'disabled ':'', 'onclick="window.location.assign(\'', esc_url($url), '&action=', esc_attr($action), '&id=', esc_attr($id), '&_wpnonce=', esc_attr($nonce),'\');" class="button button-', ($primary?'primary':'secondary'), '"><span style="padding-top: 3px;" class="', esc_attr(atec_dash_class($icon)), '"></span>
		</button>
	</td>';
}
  
function atec_create_options($name,$arr,$preset=[]): array
{ 
	$options	= get_option($name);
	$update 	= false;
	if (!$options) { $options=[]; $update=true; }
	foreach ($arr as $key) 
	{ 
		if (!isset($options[$key])) 
		{ 
			$update 			= true;
			$options[$key]	= in_array($key,$preset)?'true':'';
		} 
	}
	if ($update) update_option($name,$options);
	return $options;
}

function atec_missing_class_check($class=''): void
{
	if ($class!=='' && class_exists($class)) return;
	$bg 		= '#fff0f0';
	$icon	= 'dismiss';
	echo  '
	<div class="atec-badge atec-dilb" style="background:', esc_attr($bg) ,'">
		<div class="atec-dilb" style="width:20px; margin-right:5px;"><span class="', esc_attr(atec_dash_class($icon)), '"></span></div>
		<div class="atec-dilb atec-vam">A required class-file is missing – please ';
		if (is_plugin_active('atec-cache-apcu/atec-cache-apcu.php')) echo 'use <a href="', esc_url(admin_url().'admin.php?page=atec_wpdp'), '">';
		else echo 'download/activate <a href="https://atecplugins.com/WP-Plugins/atec-deploy.zip">';
		echo 'atec-deploy</a> to install the PRO version of this plugin.
		</div>
	</div>';
}

function atec_badge($strSuccess,$strFailed,$ok,$hide=false,$nomargin=false): void
{
	$bg 		= $ok==='blue'?'#f9f9ff':($ok==='info'?'#fff':($ok==='warning'?'#fff':($ok?'#f0fff0':'#fff0f0')));
	$md5 	= $hide?md5($ok?$strSuccess:$strFailed):'';
	$icon	= $ok==='blue'?'awards':($ok==='info'?'info-outline':($ok==='warning'?'warning':($ok?'yes-alt':'dismiss')));
	$color	= 'atec-'.($ok==='blue'?'blue':($ok==='info'?'black':($ok==='warning'?'orange':($ok?'green':'red'))));
	echo  '
	<div class="atec-badge atec-dilb atec-fit', $nomargin?' atec-mr-0':'' ,'"', ($md5!==''?' id="'.esc_attr($md5).'"':''), ' style="background:', esc_attr($bg) ,'">
		<div class="atec-dc" style="width:20px; padding-right:10px;"><span class="', esc_attr(atec_dash_class($icon,$color)), '"></span></div>
		<div class="atec-dc atec-vam" style="color: ', ($ok==='blue'?'#2271B1':($ok==='warning'?'orange':'black')), '">';
		$str=$ok?$strSuccess:$strFailed; 
		$ex=explode('<br>',$str); $count=count($ex); $c=0;
		foreach($ex as $s) { $c++; echo esc_html($s); if ($c<$count) echo '<br>'; }
		echo '.</div>
	</div>';
	if ($md5!=='') atec_reg_inline_script('badge', 'setTimeout(()=> { jQuery("#'.esc_attr($md5).'").slideUp(); }, 750);', true);
}

function atec_info($str): void { atec_badge($str,'','info'); }
function atec_warning($str): void { atec_badge($str,'','warning'); }
function atec_error_msg($txt,$break=null): void { if ($break) echo '<br>'; atec_badge('',$txt,false); }
function atec_success_msg($txt,$break=null): void { if ($break) echo '<br>'; atec_badge($txt,'',true); }

function atec_progress_div(): void 
{ 
	echo '<div id="atec_loading" class="atec-progress"><div class="atec-progressBar"></div></div>';
	atec_reg_inline_script('progress', 'setTimeout(()=>{ jQuery("#atec_loading").css("opacity",0); },4500);', true); 
}

function atec_progress(): void { ob_start(); ob_end_flush(); if (ob_get_level() > 0) { ob_flush(); } flush(); }
function atec_flush(): void { if (ob_get_level() > 0) { ob_end_flush(); } flush(); }

function atec_get_version($slug) { return wp_cache_get('atec_'.esc_attr($slug).'_version'); }

function atec_help($id,$title,$hide=false): void
{ 
	echo '
	<div id="', esc_attr($id), '_help_button" class="button atec-help-button atec-mt-2" onclick="return showHelp(\'', esc_attr($id), '\');">',
		'<span style="margin-left: -4px;" class="', esc_attr(atec_dash_class('editor-help','atec-orange')), '"></span>&nbsp;', esc_attr($title), 
	'</div>';
	
	atec_reg_inline_script('help', '
		function showHelp(id) 
		{ 
			jQuery("#"+id+"_help").removeClass("atec-dn").show(); 
			jQuery("#"+id+"_help_button").remove();
			return false; 
		}');
}

function atec_header($dir,$slug,$title,$sub_title=''): void
{ 
	$img					= $slug===''?'atec_logo_blue.png':'atec_'.esc_attr($slug).'_icon.svg';
	$imgSrc			= plugins_url('/assets/img/atec-group/'.esc_attr($img), $dir);
	$plugin				= atec_get_plugin($dir);
	$atec_slug_arr	= ['wpca','wpci','wpds','wms','wpsi'];
	$approved		= in_array($slug, $atec_slug_arr);
	$wordpress		= 'https://wordpress.org/support/plugin/';
	$supportLink	= (!$approved)?'https://atecplugins.com/':$wordpress.$plugin;

	if (is_null(get_option('atec_allow_integrity_check',null))) atec_integrity_check_banner($dir);
	atec_license_banner($dir);

	echo '
	<div class="atec-header">
		<h3 class="atec-mb-0 atec-center" style="line-height: 0.85em;">
			<sub><img alt="Plugin icon" src="',esc_url($imgSrc),'" style="height:22px;"></sub> ', ($slug===''?'':'atec '), esc_html($title),
			'<font style="font-size:80%;">';
			$ver=atec_get_version(esc_attr($slug));
			if ($slug!='') echo ' <span', (str_contains($ver, 'BETA')?' class="atec-red"':'') ,'> v'.esc_attr($ver).'</span>';
			if ($sub_title!=='') echo ' – '.esc_html($sub_title);
			echo '
			</font>',
		'</h3>';
		atec_progress_div();
		echo '
		<div class="atec-center">	
			<a style="position:relative;" class="atec-fs-12 atec-nodeco atec-btn-small" href="', esc_url($supportLink), '" target="_blank">
			<span class="', esc_attr(atec_dash_class('sos')), '"></span> Plugin support</a>';
			if ($approved)
			{
				echo ' <a style="position:relative; margin-left: 10px;" class="atec-fs-12 atec-nodeco atec-btn-small" href="', esc_url($wordpress.$plugin.'/reviews/#new-post'), '" target="_blank"><span class="', esc_attr(atec_dash_class('admin-comments')), '"></span> ', esc_attr__('Post a review','atec-stats'), '</a>';
			}		
		echo '
		</div>
	</div>';
}

function atec_clean_request($t,$nonce=''): string
{ 
	if (!isset($_REQUEST[ '_wpnonce' ]) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST[ '_wpnonce' ]) ), $nonce===''?atec_nonce():$nonce ) ) { return ''; }
	return isset($_REQUEST[$t])?sanitize_text_field(wp_unslash($_REQUEST[$t])):'';
}

function atec_clean_server($t): string { return isset($_SERVER[$t])?sanitize_text_field(wp_unslash($_SERVER[$t])):''; } 

function atec_reg_style($id,$dir,$css,$ver): void { wp_register_style($id, plugin_dir_url($dir).'assets/css/'.$css, [], esc_attr($ver)); wp_enqueue_style($id); } 
function atec_reg_script($id,$dir,$js,$ver): void { wp_register_script($id, plugin_dir_url($dir).'assets/js/'.$js, [], esc_attr($ver),true); wp_enqueue_script($id); } 
function atec_reg_inline_style($id, $css):void { $id=($id==='')?'atec-css':'atec_'.$id; wp_register_style($id, false, [], '1.0.0'); wp_enqueue_style($id); wp_add_inline_style($id, $css); }
function atec_reg_inline_script($id, $js, $jquery=false):void { $id='atec_'.$id; wp_register_script($id, false, $jquery?array('jquery'):array(), '1.0.0', false); wp_enqueue_script($id); wp_add_inline_script($id, $js); }

function atec_get_url(): string
{ 
	$url_parts	= wp_parse_url( home_url() );
	$url			= $url_parts['scheme'] . "://" . $url_parts['host'] . (isset($url_parts['port'])?':'.$url_parts['port']:'') .add_query_arg( NULL, NULL );
	return rtrim(strtok($url, '&'),'/');
} 

function atec_little_block($str,$tag='H3',$class='atec-head',$classTag=''): void { echo '<div class="',esc_attr($class),'"><',esc_attr($tag),' class="',esc_attr($classTag),'">',esc_html($str),'</',esc_attr($tag),'></div>'; }

function atec_little_block_with_info($str,$arr,$class='',$buttons=[],$url='',$nonce='',$nav='',$licenseCheck=false): void
{
	$iconPath=plugins_url('assets/img/icons/',__DIR__);
	$reg = '/#([\-|\w]+)\s(.*)/i';
	echo '
	<div class="atec-db atec-mb-10" style="border-bottom: solid 1px white; padding-bottom:5px;">
		<div class="atec-dilb atec-mr-10">'; atec_little_block($str,'H3','atec-head atec-mb-0'); echo '</div>';
		foreach ($buttons as $b)
		{ 
			echo '<div class="atec-dilb atec-mr-10 atec-vat">';
			$lower=strtolower($b);
			if ($lower!==$b) atec_nav_button_confirm($url,$nonce,$lower,$nav,$lower==='update'?'Reload':'Delete',$licenseCheck);
			else atec_nav_button($url,$nonce,$lower,$nav,$lower==='update'?'Reload':'Delete'); 
			echo '</div>'; 
		}
		echo '
		<div class="atec-dilb atec-right">';
			foreach ($arr as $key => $value)
			{ 
				preg_match($reg, $key, $matches);
				echo '
				<span class="atec-dilb atec-bg-w atec-border-tiny atec-ml-10 atec-box-30">
					<strong>'; 
					if (isset($matches[2])) echo '<img class="atec-sys-icon" src="', esc_url($iconPath.$matches[1].'.svg'), '">', esc_attr($matches[2]);
					else echo esc_attr($key);
					echo ': </strong>
					<span class="', esc_attr($class), '">', esc_attr($value), '</span>
				</span>'; 
			}
		echo '
		</div>
	</div>';
}

function atec_little_block_with_button($str,$url,$nonce,$action,$nav,$button,$primary=false,$simple=false,$float=true): void
{
if (gettype($action)!=='array') { $action=array($action); $nav=array($nav); $button=array($button); $primary=array($primary); }
echo '
<div>',
	'<div class="atec-dilb">'; atec_little_block($str); echo '</div>';
	$c=0;
	foreach($action as $a)
	{
		echo '<div class="atec-dilb atec-vat', $float?' atec-right':' atec-ml-20', '">'; atec_nav_button($url,$nonce,$action[$c],$nav[$c],$button[$c],$primary[$c],$simple); echo '</div>';
		$c++;
	};
echo '
</div>';
}
?>