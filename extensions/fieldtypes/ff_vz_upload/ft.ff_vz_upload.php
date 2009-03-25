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
		'version'     => 0.6,
		'desc'        => 'Upload files',
		'docs_url'    => 'http://elivz.com'
	);

	var $requires = array(
		'ff'        => '0.9.7',
		'cp_jquery' => '1.1'
	);
    

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
							$SD->select('vz_upload_dest', $field_settings['vz_upload_dest'], $dests)
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
		$upload_count = -1;
		if ($field_data) 
		{
			// Split the saved data out into an array
			$field_data = explode(' ', $field_data);
			
			// Cycle through each item and put it in a table row
			foreach ($field_data as $file)
			{
				$upload_count++;
				$rowSwitch = ($upload_count % 2) ? 'tableCellTwo' : 'tableCellOne';
				$out .= "<tr><td class='".$rowSwitch."'><input type='text' readonly='readonly' name='".$field_name."[".$upload_count."][0]' style='border:none;background:transparent' value='".$file."' /></td><td class='".$rowSwitch."'><input type='checkbox' name='".$field_name."[".$upload_count."][1]' class='ff_vz_upload_".$field_name."_delete' /></td></tr>";
			}
		}
		$out .= '</tbody></table>';
		$out .= '<div id="'.$field_name.'_btn">You must have JavaScript enabled to upload files.</div>';
		
		
		// Get the server paths we will need later
		$upload_path = $DB->query("SELECT server_path FROM exp_upload_prefs WHERE id = ".$field_settings['vz_upload_dest']." LIMIT 1")->row['server_path'];
		$script_path = str_replace(getcwd().'/', '', FT_PATH.'ff_vz_upload/uploadify/upload.php');
		
		$allow_multiple = isset($field_settings['vz_upload_multiple']);

		// Include the styles and scripts
		$this->include_css('uploadify/vz_upload.css');
		$this->include_js('uploadify/jquery.uploadify.js');
		$this->include_js('uploadify/vz_upload.js');
		$this->insert_js('jQuery(document).ready(function() { setupVzUpload("'.$field_name.'", "'.$script_path.'", "'.$upload_path.'", "'.$upload_count.'", "'.$allow_multiple.'", "'.$field_settings['vz_upload_types'].'"); });');
	
		
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
		return (isset($files)) ? implode(' ', $files) : '';
	}
	

	/**
	 * Display Tag
	 *
	 * @param  array   $params          Name/value pairs from the opening tag
	 * @param  string  $tagdata         Chunk of tagdata between field tag pairs
	 * @param  string  $field_data      Currently saved field value
	 * @param  array   $field_settings  The field's settings
	 * @return string  relationship references
	 */
	function display_tag($params, $tagdata, $field_data, $field_settings)
	{
		global $DB, $TMPL;
		$upload_url = $DB->query("SELECT url FROM exp_upload_prefs WHERE id = ".$field_settings['vz_upload_dest']." LIMIT 1")->row['url'];
		
		$out = '';
		
		if ($field_data) 
		{
			// Split the saved data out into an array
			$field_data = explode(' ', $field_data);
			
			if (!$tagdata)
			{
				// Single tag: only output the first file
				$out = str_replace('//','/',$upload_url.$field_data[0]);
			}
			else
			{
				// Tag pair: output each file in turn
				$count = 0;
				foreach($field_data as $file)
				{
					// Copy $tagdata
					$file_tagdata = $tagdata;
					
					// Swap out the variables
					$file_tagdata = $TMPL->swap_var_single('file_name', $file, $file_tagdata);
					$file_tagdata = $TMPL->swap_var_single('file_url', str_replace('//','/',$upload_url.$file), $file_tagdata);
					$file_tagdata = $TMPL->swap_var_single('total_results', count($field_data), $file_tagdata);
					$file_tagdata = $TMPL->swap_var_single('count', $count+1, $file_tagdata);

					$out .= $file_tagdata;

					$count++;
				}
				
			}
		}
		
		return $out;
	}

}

/* End of file ft.ff_vz_upload.php */
/* Location: ./system/fieldtypes/vz_upload/ft.ff_vz_upload.php */