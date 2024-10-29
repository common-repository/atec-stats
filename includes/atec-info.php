<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_info { function __construct($dir,$url=null,$nonce=null) {

global $wp_filesystem;
WP_Filesystem();

$iconPath 		= plugins_url('assets/img/atec-group/',__DIR__).atec_get_slug().'_icon.svg';
$readmePath	= plugin_dir_path($dir).'readme.txt';
$readme			= $wp_filesystem->get_contents($readmePath);

echo '
<div class="atec-mb-0">
	<div class="atec-dilb">';	atec_little_block('Info'); echo '</div>';
	if (!is_null($url))
	{
		echo '
		<div class="atec-dilb atec-right">
			<span class="atec-dilb atec-bg-white atec-border-tiny atec-box-30">'; atec_readme_button_close($url,$nonce); echo '</span>
		</div>';
	}
echo '
</div>

<div style="font-size: 1.125em; max-width: 100%; padding-top: 20px;" id="readme" class="atec-mmt-10 atec-mb-0 atec-border atec-bg-white-06 atec-anywrap">';

if (!$readme) echo '<p class="atec-red">Can not read the readme.txt file.</p>';
else
{
	preg_match('/===(\s+)(.*)(\s+)===\n/', $readme, $matches);

	$readme = preg_replace('/== Installation(.*)/sm', '', $readme);
	$readme = preg_replace('/Contributors(.*)html\n/sm', '', $readme);
	$readme = preg_replace('/===(\s+)(.*)(\s+)===\n/', '', $readme);
	$readme = preg_replace('/==(\s+)(.*)(\s+)==\n/', "<strong>$2</strong><br>", $readme);

	echo '<h4 class="atec-m-0"><img style="height: 24px;" class="atec-vat nav-icon" src="', esc_url($iconPath), '">', esc_attr(trim($matches[2])), '</h4>', 
	esc_html($readme);
	atec_reg_inline_script('readme','readme=jQuery("#readme"); html=readme.html(); html = html.replaceAll("&lt;", "<"); html = html.replaceAll("&gt;", ">"); readme.html(html);', true);
}
echo '</div>';

}}
?>