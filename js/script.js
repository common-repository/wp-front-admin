jQuery(function($){
	
	var ajaxurl = wpfap_ajax.ajax_url;
	
	if($('#wpfap_sidebar').length > 0 && !$('#wpfap_sidebar').hasClass('hide')){
		wpfap_sidebar_init();
	}
	
	function wpfap_sidebar_init(){
		$("#wpfap_sidebar").resizable({
			minWidth: 420,
			handles: "w",
			resize: function(event, ui){
				$('html').css({"margin-right": ui.size.width + "px" });
				$('#wpfap_sidebar').attr('data-width', ui.size.width + "px")
			},
			stop: function(event, ui){
				jQuery.post(ajaxurl, {action:'wpfap_ajax_width', width:ui.size.width});
			}
		});
		$('#wp-admin-bar-wpfap_toolbar').addClass('hover');
		
		$('#wpfap_sidebar .ui-resizable-handle.ui-resizable-w').html('<i class="fa fa-rotate-90 fa-bars"></i>');
	}
	
	$('#wp-admin-bar-wpfap_toolbar a').click(function(e){
		e.preventDefault();
		$(this).blur();
		
		if($('#wpfap_sidebar').hasClass('hide')){
			
			wpfap_sidebar_init();
			$('html').css({"margin-right": $('#wpfap_sidebar').attr('data-width') });
			$('#wpfap_sidebar').removeClass('hide');
			$('#wp-admin-bar-wpfap_toolbar').addClass('hover');

			jQuery.post(ajaxurl, {action:'wpfap_ajax_toggle', toggle:'show'});
			
		}else{
			
			$('html').css({"margin-right": "0" });
			$('#wpfap_sidebar').addClass('hide');
			$('#wp-admin-bar-wpfap_toolbar').removeClass('hover');
			
			jQuery.post(ajaxurl, {action:'wpfap_ajax_toggle', toggle:'hide'});
			
		}
		
	});
	
	$("#wpfap_sidebar .wpfap_form_cf_update").submit(function(e){
		e.preventDefault();
		var form = $(this);
		var dataForm = $(this).serialize();
		if(dataForm.length > 0){
			
			var data = {
				action: 'wpfap_ajax_cf_update',
				data: dataForm
			};
			jQuery.post(ajaxurl, data).done(function(){
				form.addClass('wpfap_success');
				setTimeout(function(){
					form.removeClass('wpfap_success');
				}, 1500);
				$('.wpfap_refresh').removeClass('hide');
			});
			
		}
	});
	
	$("#wpfap_sidebar .wpfap_form_cf_add").submit(function(e){
		e.preventDefault();
		var form = $(this);
		var dataForm = $(this).serialize();
		if(dataForm.length > 0){
			
			var data = {
				action: 'wpfap_ajax_cf_add',
				data: dataForm
			};
			jQuery.post(ajaxurl, data).done(function(response){
				if(response != 0 && response != null){
					$('#wpfap_sidebar .wpfap_form_cf_add').after(response);
					$('.wpfap_refresh').removeClass('hide');
				}
			});
			
		}
	});
	
	$("#wpfap_sidebar .wpfap_form_cf_add .wpfap_form_cf_add_input").click(function(e){
		$('#wpfap_sidebar .wpfap_form_cf_add .wpfap_form_cf_add_input').addClass('hide');
		$('#wpfap_sidebar .wpfap_form_cf_add .wpfap_form_cf_add_select').removeClass('hide');
		$('#wpfap_sidebar .wpfap_form_cf_add select[name="key_select"]').addClass('hide');
		$('#wpfap_sidebar .wpfap_form_cf_add select[name="key_select"] option').prop("selected", false);
		$('#wpfap_sidebar .wpfap_form_cf_add input[name="key_input"]').removeClass('hide');
		
	});
	
	$("#wpfap_sidebar .wpfap_form_cf_add .wpfap_form_cf_add_select").click(function(e){
		$('#wpfap_sidebar .wpfap_form_cf_add .wpfap_form_cf_add_select').addClass('hide');
		$('#wpfap_sidebar .wpfap_form_cf_add .wpfap_form_cf_add_input').removeClass('hide');
		$('#wpfap_sidebar .wpfap_form_cf_add select[name="key_select"]').removeClass('hide');
		$('#wpfap_sidebar .wpfap_form_cf_add input[name="key_input"]').val('').addClass('hide');
		
	});
	
	
	$("#wpfap_sidebar .wpfap_form_cf_delete").click(function(e){
		e.preventDefault();
		var form = $(this).closest('.wpfap_form_cf_update');
		
		var dataForm = form.serialize();
		
		if(dataForm.length > 0 && window.confirm("Are you sure?")){
			
			var data = {
				action: 'wpfap_ajax_cf_delete',
				data: dataForm
			};
			jQuery.post(ajaxurl, data).done(function(response){
				if(response != 0 && response != null){
					form.fadeOut(700);
					setTimeout(function(){
						form.remove();
					}, 700);
				}
			});
			
		}
		
	});
	
	$("#wpfap_sidebar .thickbox").click(function(e){
		$serialized_value = $(this).closest('.wpfap_form_cf_update').find('input[name="value"]');
		$serialized_pre = $(this).closest('.wpfap_form_cf_update').find('pre');
		$('#wpfap_edit_serialized #serialized').val($(this).closest('.wpfap_form_cf_update').find('input[name="value"]').val()).trigger("change");
	});
	
	
	var updating = false, 
		original = '';
	var output = 				$('#wpfap_edit_serialized #output'), 
		output_mirror_array = 	$('#wpfap_edit_serialized #output_mirror_array');
		
	var serialized = $("#wpfap_edit_serialized #serialized").on("change keyup", function(){
		if (updating)
			return;
		original = serialized.val();
		
		var data = {
			action: 'wpfap_ajax_edit_serialized',
			data: {
				type: 'serialized',
				value: original
			}
		};
		
		$.post(ajaxurl, data).done(function(response){
			updating = true;
			editor.val(response);
			$("#editor_mirror").val(response);
			updating = false;
			editor.change();
		});
	});

	var editor = $("#wpfap_edit_serialized #editor").on("change keyup", function(){
		if (updating)
			return;
		
		var data = {
			action: 'wpfap_ajax_edit_serialized',
			data: {
				type: 'exported',
				value: editor.val()
			}
		};
		
		$.post(ajaxurl, data).done(function(response){
			updating = true;
			output.val(response);
			
			var data = {
				action: 'wpfap_ajax_edit_serialized',
				data: {
					type: 'exported_to_array',
					value: output.val()
				}
			};
			
			$.post(ajaxurl, data).done(function(response){
				updating = true;
				output_mirror_array.val(response);
				updating = false;
			});
			updating = false;
		});
		
		
	});
	
	output_mirror_array.on('scroll', function(){
		editor.scrollTop($(this).scrollTop());
	});
	
	editor.on('scroll', function(){
		output_mirror_array.scrollTop($(this).scrollTop());
	});
	
	$("#wpfap_edit_serialized form").submit(function(e){
		e.preventDefault();
		
		if(output.val() == ''){
			$(".btn-submit").attr('disabled', false);
			output_mirror_array.val('JSON Error. Please Enter a correct JSON.');
			return;
		}
		
		$serialized_value.val(output.val());
		$serialized_pre.html(output_mirror_array.val());
		tb_remove();
		output.val('');
		output_mirror_array.val('');
		
	});
	
});