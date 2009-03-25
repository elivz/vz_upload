<?php

if ( ! defined('EXT')) exit('Invalid file request');


/**
 * VZ Upload Class
 *
 * @package   VZ Upload
 * @author    Eli Van Zoeren - eli@elivz.com
 * @copyright Copyright (c) 2009 Eli Van Zoeren
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 */
class Ff_vz_upload extends Fieldframe_Fieldtype {

	/**
	 * Fieldtype Info
	 * @var array
	 */
	var $info = array(
		'name'        => 'VZ Upload',
		'version'     => 0.5,
		'desc'        => 'Upload files',
		'docs_url'    => 'http://elivz.com'
	);

	var $requires = array(
		'ff'        => '0.9.5',
		'cp_jquery' => '1.1'
	);
    
	//var $hooks = array('publish_form_headers');


    /**
	 * Display Field Settings
	 * 
	 * @param  array  $field_settings  The field's settings
	 * @return array  Settings HTML (cell1, cell2, rows)
	 */
	function display_field_settings($field_settings)
	{
		global $DSP, $LANG, $DB;
		$LANG->fetch_language_file('ff_vz_upload');
	
		// Initialize a new instance of SettingsDisplay
		$SD = new Fieldframe_SettingsDisplay();
		
		$out = $SD->block('field_settings');
		
		// Get the file upload destinations
		$results = $DB->query("SELECT id, name FROM exp_upload_prefs ORDER BY name ASC");
		
		if ($results->num_rows > 0)
		{
			// If there are any upload destinations, put them in a select box...
			$dests = array();
			foreach($results->result as $row)
			{
				$dests += array($row['id'] => $row['name']);    
			}
			$out .= $SD->row(array(
							$SD->label('settings_destination'),
							$SD->select('vz_upload_dest', '1', $dests)
							));
		}
		else
		{
			$out .= '<p class="highlight">'.$LANG->line('no_destinations_found').'</p>';
		}
		
		// Which file types are allowed?
		$types = isset($field_settings['vz_upload_types']) ? $field_settings['vz_upload_types'] : '*.jpg;*.jpeg;*.png;*.gif';
		$out .= $SD->row(array(
						$SD->label('settings_types', 'settings_types_example'),
						$SD->text('vz_upload_types', $types)
						));
		$multiple = isset($field_settings['vz_upload_multiple']) ? ' checked="checked"' : '';	
		// Allow multiple uploads?
		$out .= $SD->row(array(
						'<label for="vz_upload_multiple" class="defaultBold">'.$LANG->line('settings_multiple_uploads').'</label>',
						'<input type="checkbox" name="vz_upload_multiple" id="vz_upload_multiple"'.$multiple.' />'
						));
		
		$out .= $SD->block_c();

		// Return the settings block
		return array('cell2' => $out);
	}
	
	
	/**
	 * Display Field
	 * 
	 * @param  string  $field_name      The field's name
	 * @param  mixed   $field_data      The field's current value
	 * @param  array   $field_settings  The field's settings
	 * @return string  The field's HTML
	 */
	function display_field($field_name, $field_data, $field_settings)
	{
		global $DB, $DSP;

		// List out all the uploaded files in a table
		$out = '<table id="'.$field_name.'_list" class="tableBorder" style="width:50%" cellspacing="0" cellpadding="0">';
		$out .= '<thead><tr><td class="tableHeading">File Name</td><td class="tableHeading" style="width:20%">Delete</td></tr></thead><tbody>';
		$uploadCount = -1;
		if (isset($field_data)) 
		{
			$field_data = explode(' ', $field_data);
			foreach ($field_data as $file)
			{
				$uploadCount++;
				$rowSwitch = ($uploadCount % 2) ? 'tableCellTwo' : 'tableCellOne';
				$out .= "<tr><td class='".$rowSwitch."'><input type='text' readonly='readonly' name='".$field_name."[".$uploadCount."][0]' style='border:none;background:transparent' value='".$file."' /></td><td class='".$rowSwitch."'><input type='checkbox' name='".$field_name."[".$uploadCount."][1]' class='ff_vz_upload_".$field_name."_delete' /></td></tr>";
			}
		}
		$out .= '</tbody></table>';
		$out .= '<input type="file" id="vz_upload_'.$field_name.'" />';
		
		// Get the server paths we will need later
		$upload_path = $DB->query("SELECT server_path FROM exp_upload_prefs WHERE id = ".$field_settings['vz_upload_dest']." LIMIT 1")->row['server_path'];
		$script_path = str_replace(getcwd().'/', '', FT_PATH.'/ff_vz_upload/uploadify/upload.php');

		// Include the styles and scripts
		$this->include_css('uploadify/vz_upload.css');
		$this->include_js('uploadify/jquery.uploadify.js');
		$this->insert_js("jQuery(document).ready(function() {
	var uploadCount = ".$uploadCount.";
	jQuery('#vz_upload_".$field_name."').fileUpload({
		'uploader': FT_URL+'ff_vz_upload/uploadify/uploader.swf',
		'cancelImg': FT_URL+'ff_vz_upload/uploadify/cancel.png',
		'script': '".$script_path."',
		'folder': '".$upload_path."',
		'fileDesc': 'Image Files',
		'fileExt': '".$field_settings['vz_upload_types']."',
		'multi': ".(isset($field_settings['vz_upload_multiple']) ? 'true' : 'false').",
		'auto': true,
		'onComplete': function (event, queueID, fileObj, response, data) {
			uploadCount++;
			rowSwitch = (uploadCount % 2) ? 'tableCellTwo' : 'tableCellOne';
			jQuery('#".$field_name."_list').append(\"<tr><td class='\"+rowSwitch+\"'><input type='text' readonly='readonly' name='".$field_name."[\"+uploadCount+\"][0]' style='border:none;background:transparent' value='\"+fileObj.name+\"' /></td><td class='\"+rowSwitch+\"'><input type='checkbox' name='".$field_name."[\"+uploadCount+\"][1]' class='ff_vz_upload_".$field_name."_delete' /></td></tr>\");
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
	});
");
		
		return $out;
	}


	/**
	 * Save Field
	 * 
	 * @param  string  $field_data		The field's post data
	 * @param  array  $field_settings	The field settings
	 */
	function save_field($field_data, $field_settings)
	{
		global $DB;
		
		$upload_path = $DB->query("SELECT server_path FROM exp_upload_prefs WHERE id = ".$field_settings['vz_upload_dest']." LIMIT 1")->row['server_path'];
		
		// See if they checked "delete" for any of them
		foreach ($field_data as $file)
		{
			if (isset($file[1]))
			{
				// Delete the file
				$targetFile =  str_replace('//','/',$upload_path.$file[0]);
				@unlink($targetFile);
			}
			else
			{
				// Add it to the list to save
				$files[] = $file[0];
			}
		}
		// Convert the array to a space-delimited list
		return implode(' ', $files);
	}

}

/* End of file ft.ff_vz_upload.php */
/* Location: ./system/fieldtypes/vz_upload/ft.ff_vz_upload.php */