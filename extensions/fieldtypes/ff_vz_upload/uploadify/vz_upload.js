function setupVzUpload (field_name, script_path, upload_path, upload_url, upload_count, allow_multiple, file_types) {
	// Hide the list of files if it's empty
	if (upload_count < 0) { jQuery('#'+field_name+'_list').hide(); }

	// Start the Uploadifier
	jQuery('#'+field_name+'_btn').fileUpload({
		'uploader': FT_URL+'ff_vz_upload/uploadify/uploader.swf',
		'cancelImg': FT_URL+'ff_vz_upload/uploadify/cancel.png',
		'buttonImg': FT_URL+'ff_vz_upload/uploadify/button.png',
		'rollover': true, 'width': 100, 'height': 25,
		'script': script_path+'upload.php',
		'folder': upload_path,
		'fileExt': file_types,
		'multi': allow_multiple,
		'auto': true,
		'onComplete': function (event, queueID, fileObj, response, data) {
			// Check if this file is already in the list
			if (jQuery('input[value='+fileObj.name+']', '#'+field_name+'_list').length == 0) {
				upload_count++;
				var rowSwitch = (upload_count % 2) ? 'tableCellTwo' : 'tableCellOne';
				
				// If only one file is allowed, mark the others for deletion
				if (!allow_multiple) jQuery(':input', '#'+field_name+'_list tbody')
					.css('textDecoration','strikethrough')
					.filter(':checkbox').attr({'checked': 'checked', 'disabled': 'disabled'})
					.prev().val('del');
				
				// Get the thumbnail or icon
				var file_ext = fileObj.name.split('.').pop();
				if (file_ext == 'jpg' || file_ext == 'jpeg' || file_ext == 'png' || file_ext == 'gif') 
				{  	// Show thumbnail
					var img = "<img src='"+upload_url+fileObj.name+"' alt='Thumbnail' width='40' />";
				}
				else
				{   // Show file-type icon
					var img = "<img src='"+FT_URL+"ff_vz_upload/icons/"+file_ext+".png' alt='Icon' width='16' />";
				}

				// Add a row to the list of files
				jQuery('#'+field_name+'_list').append("<tr><td class='"+rowSwitch+"'>"+img+"</td><td class='"+rowSwitch+"'><input type='text' readonly='readonly' name='"+field_name+"["+upload_count+"][0]' style='border:none;background:transparent' value='"+fileObj.name+"' /></td><td class='"+rowSwitch+"'><input type='hidden' name='"+field_name+"["+upload_count+"][1]' /><input type='checkbox' value='del' /></td></tr>");
				// Make sure the file list is visible
				jQuery('#'+field_name+'_list').show();
			}
		},
		'onError': function (a, b, c, d) {
        	if (d.status == 404)
				alert('Could not find upload script. Use a path relative to: ".getcwd()."');
        	else if (d.type === 'HTTP')
				alert('error '+d.type+': '+d.status);
        	else if (d.type ==='File Size')
				alert(c.name+' '+d.type+' Limit: '+Math.round(d.sizeLimit/1024)+'KB');
			else
				alert('error '+d.type+': '+d.text);
		}
	});
	
	// Hook up the checkboxes to a hidden input
	jQuery(':checkbox', '#'+field_name+'_list').change( function() {
		var cur = $(this);
		cur.prev().val(cur.val());
	});
}


/*
 * Hook into FF Matrix onDisplayCell event
 */
function setupVzUploadCell (td) {
	// Get the settings
	var field_id = $('span', td).attr('class');
	var settings = 
	alert(field_id);
	// Start the Uploadifier
	jQuery('#'+field_name+'_btn').fileUpload({
		'uploader': FT_URL+'ff_vz_upload/uploadify/uploader.swf',
		'cancelImg': FT_URL+'ff_vz_upload/uploadify/cancel.png',
		'buttonImg': FT_URL+'ff_vz_upload/uploadify/button.png',
		'rollover': true, 'width': 100, 'height': 25,
		'script': script_path+'upload.php',
		'folder': upload_path,
		'fileExt': file_types,
		'multi': false,
		'auto': true,
		'onComplete': function (event, queueID, fileObj, response, data) {
			// Check if this file is already in the list
			if (jQuery('input[value='+fileObj.name+']', '#'+field_name+'_list').length == 0) {
				upload_count++;
				var rowSwitch = (upload_count % 2) ? 'tableCellTwo' : 'tableCellOne';
				
				// If only one file is allowed, mark the others for deletion
				if (!allow_multiple) jQuery(':input', '#'+field_name+'_list tbody')
					.css('textDecoration','strikethrough')
					.filter(':checkbox').attr({'checked': 'checked', 'disabled': 'disabled'})
					.prev().val('del');
				
				// Get the thumbnail or icon
				var file_ext = fileObj.name.split('.').pop();
				if (file_ext == 'jpg' || file_ext == 'jpeg' || file_ext == 'png' || file_ext == 'gif') 
				{  	// Show thumbnail
					var img = "<img src='"+upload_url+fileObj.name+"' alt='Thumbnail' width='40' />";
				}
				else
				{   // Show file-type icon
					var img = "<img src='"+FT_URL+"ff_vz_upload/icons/"+file_ext+".png' alt='Icon' width='16' />";
				}

				// Add a row to the list of files
				jQuery('#'+field_name+'_list').append("<div><td class='"+rowSwitch+"'>"+img+"</td><td class='"+rowSwitch+"'><input type='text' readonly='readonly' name='"+field_name+"["+upload_count+"][0]' style='border:none;background:transparent' value='"+fileObj.name+"' /></td><td class='"+rowSwitch+"'><input type='hidden' name='"+field_name+"["+upload_count+"][1]' /><input type='checkbox' value='del' /></td></div>");
				// Make sure the file list is visible
				jQuery('#'+field_name+'_list').show();
			}
		},
		'onError': function (a, b, c, d) {
        	if (d.status == 404)
				alert('Could not find upload script. Use a path relative to: ".getcwd()."');
        	else if (d.type === 'HTTP')
				alert('error '+d.type+': '+d.status);
        	else if (d.type ==='File Size')
				alert(c.name+' '+d.type+' Limit: '+Math.round(d.sizeLimit/1024)+'KB');
			else
				alert('error '+d.type+': '+d.text);
		}
	});
	
	// Hook up the checkboxes to a hidden input
	jQuery(':checkbox', '#'+field_name+'_list').change( function() {
		var cur = $(this);
		cur.prev().val(cur.val());
	});
}