function atec_wps_ajax(url, ref) 
{ 
	jQuery.post(atec_wps_ajax_obj.ajaxurl+"?url="+encodeURIComponent(url)+"&ref="+encodeURIComponent(ref), { action: 'atec_wps_log_ajax' }).done(() => { }); 
}