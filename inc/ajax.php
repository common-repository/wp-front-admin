<?php 
// ---------------------------------------------------
// Show/Hide Panel
// ---------------------------------------------------
function wpfap_ajax_toggle(){
	if( !defined('DOING_AJAX') || !DOING_AJAX || !isset($_POST['action']) || !isset($_POST['toggle']) || !current_user_can('manage_options') ) 
		return;
	
	$toggle = $_POST['toggle'];
	
	if($toggle == 'show')
		update_option('wpfap_hide', 0);
		
	elseif($toggle == 'hide')
		update_option('wpfap_hide', 1);
	
	die();
	
}
add_action('wp_ajax_wpfap_ajax_toggle', 'wpfap_ajax_toggle');


// ---------------------------------------------------
// Width Panel
// ---------------------------------------------------
function wpfap_ajax_width(){
	if( !defined('DOING_AJAX') || !DOING_AJAX || !isset($_POST['action']) || !isset($_POST['width']) || !current_user_can('manage_options') ) 
		return;
	
	$width = (int) $_POST['width'];
	if(empty($width))
		die();
		
	update_option('wpfap_width', $width);
	die();
	
}
add_action('wp_ajax_wpfap_ajax_width', 'wpfap_ajax_width');


// ---------------------------------------------------
// Update: Custom Field
// ---------------------------------------------------
function wpfap_ajax_cf_update(){
	if( !defined('DOING_AJAX') || !DOING_AJAX || !isset($_POST['action']) || !isset($_POST['data']) || !current_user_can('manage_options') ) 
		return;
	
	parse_str($_POST['data'], $data);
	
	$mid = 			(int) $data['mid'];
	$key = 			sanitize_key(wp_unslash($data['key']));
	$value = 		maybe_unserialize(wp_unslash(urldecode($data['value'])));
	
	if(empty($mid) || empty($key) || !get_metadata_by_mid('post', $mid))
		die();
	
	if(update_metadata_by_mid('post', $mid, $value, $key)){
		
		$return = array(
						'mid' => 		$mid,
						'key' => 		$key,
						'value' => 		$value
		);
		echo json_encode($return);
		
	}
	
	die();
}
add_action('wp_ajax_wpfap_ajax_cf_update', 'wpfap_ajax_cf_update');


// ---------------------------------------------------
// Add: Custom Field
// ---------------------------------------------------
function wpfap_ajax_cf_add(){
	if( !defined('DOING_AJAX') || !DOING_AJAX || !isset($_POST['action']) || !isset($_POST['data']) || !current_user_can('manage_options') ) 
		return;
	
	parse_str($_POST['data'], $data);
	
	$pid = 			(int) $data['pid'];
	$key_select = 	sanitize_key(wp_unslash($data['key_select']));
	$key_input = 	sanitize_key(wp_unslash($data['key_input']));
	$value = 		maybe_unserialize(wp_unslash($data['value']));
	
	$key = $key_select;
	if(empty($key_select) && !empty($key_input))
		$key = $key_input;
	
	if(empty($pid) || empty($key) || !get_post($pid))
		die();
	
	if($mid = add_metadata( 'post', $pid, $key, $value )){ ?>
		
		<form class="wpfap_form_cf_update panel-body" style="border-bottom:1px solid #eee;">
			<div class="row">
				<div class="col-lg-4">
					<div style="margin-bottom:3px;">
						<input type="hidden" name="mid" value="<?php echo $mid; ?>" />
						<input type="text" class="form-control" name="key" value="<?php echo $key; ?>" />
					</div>
					<div>
						<div class="btn-group">
							<button class="btn btn-default btn-sm">Update</button>
							<a href="javascript:void(0);" class="btn btn-default btn-sm wpfap_form_cf_delete"><i class="fa fa-times"></i></a>
						</div>
					</div>
				</div>
				<div class="col-lg-8" style="position:relative">
				
					<textarea class="form-control" name="value"><?php echo $value; ?></textarea>
					
				</div>
			</div>
		</form>
		
	<?php }
	
	die();
}
add_action('wp_ajax_wpfap_ajax_cf_add', 'wpfap_ajax_cf_add');


// ---------------------------------------------------
// Delete: Custom Field
// ---------------------------------------------------
function wpfap_ajax_cf_delete(){
	if( !defined('DOING_AJAX') || !DOING_AJAX || !isset($_POST['action']) || !isset($_POST['data']) || !current_user_can('manage_options') ) 
		return;
	
	parse_str($_POST['data'], $data);
	
	$mid = (int) $data['mid'];
	
	if(empty($mid))
		die();
	
	if(delete_metadata_by_mid('post', $mid))
		die('1');
	
	die();
}
add_action('wp_ajax_wpfap_ajax_cf_delete', 'wpfap_ajax_cf_delete');


// ---------------------------------------------------
// Edit Serialized
// ---------------------------------------------------
function wpfap_ajax_edit_serialized(){
	if( !defined('DOING_AJAX') || !DOING_AJAX || !isset($_POST['action']) || !isset($_POST['data']) || !current_user_can('manage_options') ) 
		return;
	
	$data = $_POST['data'];
	
	if($data['type'] == 'serialized'){
		echo wpfap_json_indent(json_encode(unserialize(stripslashes(urldecode($data['value'])))));
	}
	
	elseif($data['type'] == 'exported'){
		if(wpfap_is_json(stripslashes($data['value'])))
			echo serialize(json_decode(stripslashes($data['value']), true));
	}
	
	elseif($data['type'] == 'exported_to_array'){
		print_r(unserialize(stripslashes($data['value'])));
	}
	
	die();
	
}
add_action('wp_ajax_wpfap_ajax_edit_serialized', 'wpfap_ajax_edit_serialized');