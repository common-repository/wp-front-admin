<?php 
/*
Plugin Name: WP Front Admin
Description: Front-End Admin Panel for Posts, Pages & Custom Post Types Custom Fields
Author: hwk
Version: 0.3.2
Author URI: http://hwk.fr
Licence: GPLv2
*/

if(!defined('ABSPATH'))
  die('You are not allowed to call this page directly.');

defined( 'WP_FAP_PLUGIN_ABS_PATH' ) || define( 'WP_FAP_PLUGIN_ABS_PATH', plugin_dir_path( __FILE__ ) );

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

require( WP_FAP_PLUGIN_ABS_PATH . 'inc/functions.php');
require( WP_FAP_PLUGIN_ABS_PATH . 'inc/hooks.php');
require( WP_FAP_PLUGIN_ABS_PATH . 'inc/ajax.php');

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_action('wp_enqueue_scripts', 'wpfap_admin_enqueue_scripts');
function wpfap_admin_enqueue_scripts() {
	if(current_user_can('manage_options') && is_singular()){
		wp_enqueue_style('wpfap-bootstrap', 	plugins_url('/css/bootstrap-wrapper.css', 	__FILE__ ));
		wp_enqueue_style('wpfap-style', 		plugins_url('/css/style.css', 				__FILE__ ));
		wp_enqueue_style('jquery-ui');
		
		wp_enqueue_script('wpfap-script', 		plugins_url('/js/script.js', 				__FILE__), array('jquery'), false, true);
		wp_localize_script('wpfap-script', 'wpfap_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
		wp_enqueue_script('jquery-ui-resizable');
		
		//wp_register_style('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
	}
}

add_action('wp_enqueue_scripts', 'wpfap_admin_check_fontawesome', 99999);
function wpfap_admin_check_fontawesome(){
	global $wp_styles;
	$srcs = array_map('basename', (array) wp_list_pluck($wp_styles->registered, 'src'));
	if( !in_array('font-awesome.css', $srcs) && !in_array('font-awesome.min.css', $srcs) )
		wp_enqueue_style('wpfap-fontawesome', 	plugins_url('/css/font-awesome.min.css', 	__FILE__ ));
}

add_action('wp_footer', 'wpfap_setup');
function wpfap_setup(){
	global $wpdb, $post;
	
	if(!current_user_can('manage_options') || empty($post) || !is_singular())
		return;

	add_thickbox();
	
	$wpfap = array();
	
	$wpfap['get']['meta'] = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d ", $post->ID ));
    usort($wpfap['get']['meta'], function($a, $b){
		return strcmp($a->meta_key, $b->meta_key);
	});
	
	$wpfap['add'] = wpfap_setup_add_keys();
	
	?>
	
	<?php if(wpfap_empty(get_option('wpfap_hide'))){ ?>
		<style type="text/css">html{margin-right:<?php echo (!wpfap_empty(get_option('wpfap_width'))) ? (get_option('wpfap_width') . 'px') : '500px'; ?>;}</style>
	<?php } ?>
	
	<div class="bootstrap-wrapper">
	
	<div id="wpfap_edit_serialized" style="display:none;">
	<form id="wpfap_edit_serialized_content" class="bootstrap-wrapper" style="margin:0;">
		<div>
		
			<div class="panel panel-default" style="margin:0; border:0;">
				<div class="panel-body">
				
					<div class="row">
						<div class="col-sm-4" style="display:none;">
							<input type="hidden" id="serialized" value='' />
							<textarea rows="20" cols="30" id="editor_mirror" class="form-control" wrap="off" style="height: 400px; word-break: break-all;word-wrap: break-word; overflow:auto;" readonly></textarea>
						</div>
						
						<div class="col-sm-6">
							<textarea rows="20" cols="30" id="editor" class="form-control" wrap="off" style="height: 400px; font-family:Menlo,Monaco,Consolas,'Courier New',monospace; font-size:13px; word-break: break-all;word-wrap: break-word; overflow:auto;"></textarea>
						</div>
						
						<div class="col-sm-6">
							<input type="hidden" name="output" id="output" />
							<textarea rows="20" cols="30" id="output_mirror_array" class="form-control" style="height: 400px; font-family:Menlo,Monaco,Consolas,'Courier New',monospace; font-size:11px; word-break: break-all;word-wrap: break-word; overflow:auto; line-height:1.47;" wrap="off" readonly></textarea>
						</div>
					</div>
					
				</div>
				<div class="panel-footer" style="background:#f8f8f8; border-top:1px solid #eee;">
				
					<button class="btn btn-primary btn-submit">Save</button>
					<a href="javascript:void(0);" onclick="tb_remove()" class="btn btn-default">Cancel</a>
					
				</div>
			</div>
			
		</div>

	</form>
	</div>
	
	<div id="wpfap_sidebar" data-width="<?php echo (!wpfap_empty(get_option('wpfap_width'))) ? (get_option('wpfap_width') . 'px') : '500px'; ?>" class="<?php echo (!wpfap_empty(get_option('wpfap_hide'))) ? 'hide' : ''; ?>" <?php echo (!wpfap_empty(get_option('wpfap_width'))) ? 'style="width:'.(get_option('wpfap_width') . 'px').'"' : 'style="width:500px;"'; ?>>
		<div style="display:table; width:100%; height:100%; table-layout:fixed; position:relative; border-spacing:0; padding:15px; padding-top:47px;">
		
			<div class="panel panel-default" style="display:table; width:100%; height:100%; position:relative; border-spacing:0;">

				<!--
				<div class="panel-heading">
					<div class="pull-right">
						<a href="javascript:window.location.reload(true)" class="btn btn-primary btn-xs hide wpfap_refresh" style="margin-top: -5px;"><i class="fa fa-refresh"></i></a>
					</div>
					<div class="text-ellipsis" style="max-width:350px;"><strong><?php //echo get_the_title(); ?></strong></div>
				</div>
				
				<div class="panel-footer" style="border-bottom:1px solid #eee;">
					<ul class="nav nav-xs nav-pills">
						<li role="summary" class="active"><a href="#">Summary</a></li>
						<li role="cf"><a href="#">Custom Fields</a></li>
						<li role="comments"><a href="#">Comments</a></li>
					</ul>
				</div>
				-->
				
				<div style="height:100%; width:100%; display:table-row;">
				<div style="position:relative; width:100%; height:100%;" class="scrollable">
				<div style="position:absolute; top:0; right:0; bottom:0; left:0; overflow:auto;">
				
					<form class="wpfap_form_cf_add panel-footer" style="border-bottom:1px solid #eee;">
						<div class="row">
							<div class="col-lg-4">
								<div style="margin-bottom:3px;">
									<input type="hidden" name="pid" value="<?php echo get_the_ID(); ?>" />
									
									<select name="key_select" class="form-control <?php echo ($wpfap['add']['keys_type'] == 'select') ? '' : 'hide'; ?>">
										<option value="">Select</option>
										<?php foreach($wpfap['add']['keys'] as $key){ ?>
											<option value="<?php echo $key; ?>"><?php echo $key; ?></option>
										<?php } ?>
									</select>
									
									<input name="key_input" type="text" class="form-control <?php echo ($wpfap['add']['keys_type'] == 'select') ? 'hide' : ''; ?>" value="" />
									
								</div>
								<div>
									<div class="btn-group">
										<button class="btn btn-default btn-sm">Create</button>
										<a href="javascript:void(0);" class="btn btn-default btn-sm wpfap_form_cf_add_input <?php echo ($wpfap['add']['keys_type'] == 'select') ? '' : 'hide'; ?>"><i class="fa fa-pencil"></i></a>
										<a href="javascript:void(0);" class="btn btn-default btn-sm wpfap_form_cf_add_select <?php echo ($wpfap['add']['keys_type'] == 'select') ? 'hide' : ''; ?>"><i class="fa fa-bars"></i></a>
									</div>
								</div>
							</div>
							<div class="col-lg-8">
								<textarea class="form-control" name="value"></textarea>
							</div>
						</div>
					</form>
				
					<?php foreach($wpfap['get']['meta'] as $meta){ ?>
						<?php
						$m['id'] 	= (int) $meta->meta_id;
						$m['key'] 	= esc_attr($meta->meta_key);
						$m['value'] = $meta->meta_value;
						?>
						<form class="wpfap_form_cf_update panel-body" style="border-bottom:1px solid #eee;">
							<div class="row">
								<div class="col-lg-4">
									<div style="margin-bottom:3px;">
										<input type="hidden" name="mid" value="<?php echo $m['id']; ?>" />
										<input type="text" class="form-control" name="key" value="<?php echo $m['key']; ?>" />
									</div>
									<div>
										<div class="btn-group">
											<button class="btn btn-default btn-sm">Update</button>
											<a href="javascript:void(0);" class="btn btn-default btn-sm wpfap_form_cf_delete"><i class="fa fa-times"></i></a>
										</div>
									</div>
								</div>
								<div class="col-lg-8" style="position:relative">
								
									<?php if(!is_serialized($meta->meta_value)){ ?>
										
										<textarea class="form-control" name="value"><?php echo $m['value']; ?></textarea>
										
									<?php }else{ ?>
										<input type="hidden" name="value" value="<?php echo urlencode($meta->meta_value); ?>" />
										
										<a href="#TB_inline?i=1&height=493&width=1000&inlineId=wpfap_edit_serialized" class="thickbox btn btn-default btn-sm" rel="photo-album" style="position:absolute; right:15px; top:0;">
											<i class="fa fa-fw fa-pencil"></i> Edit
										</a>
										<pre><?php print_r(unserialize($meta->meta_value)); ?></pre>
										
									<?php } ?>
									
								</div>
							</div>
						</form>
								
					<?php } ?>
					
					<!--
					<div class="panel panel-default panel-body">
					<pre><?php //print_r($wpfap); ?></pre>
					</div>
					-->
					
				</div>
				</div>
				</div>
			</div>
			
		</div>
	</div>
	</div>
	<?php
}

function wpfap_setup_add_keys(){
	global $wpdb, $post;
	
	$return = array();
	$return['keys'] = apply_filters('postmeta_form_keys', null, $post);
 
    if($return['keys'] === null){
        $limit = apply_filters( 'postmeta_form_limit', 999 );
        $sql = "SELECT DISTINCT meta_key
            FROM $wpdb->postmeta
            WHERE meta_key NOT BETWEEN '_' AND '_z'
            HAVING meta_key NOT LIKE %s
            ORDER BY meta_key
            LIMIT %d";
        $return['keys'] = $wpdb->get_col( $wpdb->prepare( $sql, $wpdb->esc_like( '_' ) . '%', $limit ) );
    }
	
	$return['keys_type'] = 'input';
 
    if($return['keys']){
        natcasesort($return['keys']);
        $return['keys_type'] = 'select';
    }
	
	return $return;
}