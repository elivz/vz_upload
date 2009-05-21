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
		'version'     => 0.7,
		'desc'        => 'Upload files directly through the Publish page',
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
		global $DSP, $LANG;
		$LANG->fetch_language_file('ff_vz_upload');
	
		// Initialize a new instance of SettingsDisplay
		$SD = new Fieldframe_SettingsDisplay();
		
		$out = $SD->block('field_settings');
		
		// Get the file upload destinations
		$dests = $this->_get_upload_dests();
		if ($dests)
		{
			// If there are any upload destinations, put them in a select box...
			$upload_dest = isset($field_settings['vz_upload_dest']) ? $field_settings['vz_upload_dest'] : key($dests);
			$out .= $SD->row(array(
							$SD->label('settings_destination'),
							$SD->select('vz_upload_dest', $upload_dest, $dests)
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
		
		// Allow multiple uploads?
		$multiple = isset($field_settings['vz_upload_multiple']) ? 1 : 0;
		$out .= $SD->row(array(
						'<label for="vz_upload_multiple" class="defaultBold">'.$LANG->line('settings_multiple_uploads').'</label>',
						$DSP->input_checkbox('vz_upload_multiple', 'y', $multiple, 'style="width:auto"')
						));
		
		$out .= $SD->block_c();

		// Return the settings block
		return array('cell2' => $out);
	}


    /**
	 * Display Cell Settings
	 * 
	 * @param  array  $cell_settings  The cell's settings
	 * @return array  Settings HTML (cell1, cell2, rows)
	 */
	function display_cell_settings($cell_settings)
	{
		global $DSP, $LANG;
		$LANG->fetch_language_file('ff_vz_upload');
	
		// Initialize a new instance of SettingsDisplay
		$SD = new Fieldframe_SettingsDisplay();
		$out = '';
		
		// Get the file upload destinations
		$dests = $this->_get_upload_dests();

		if ($dests)
		{
			// If there are any upload destinations, put them in a select box...
			$upload_dest = isset($cell_settings['vz_upload_dest']) ? $cell_settings['vz_upload_dest'] : key($dests);
			$out .= '<label class="itemWrapper">'
					. $DSP->qdiv('defaultBold', $LANG->line('settings_destination'))
					. $SD->select('vz_upload_dest', $upload_dest, $dests)
					. '</label>';
		}
		else
		{
			$out .= '<p class="highlight">'.$LANG->line('no_destinations_found').'</p>';
		}
		
		// Which file types are allowed?
		$types = isset($cell_settings['vz_upload_types']) ? $cell_settings['vz_upload_types'] : '*.jpg;*.jpeg;*.png;*.gif';
		$out .= '<label class="itemWrapper">'
				. $DSP->qdiv('defaultBold', $LANG->line('settings_types'))
				. $SD->text('vz_upload_types', $types)
				. '</label>';
		
		// Return the settings block
		return $out;

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
		global $DSP;
		
		// Get the server paths we will need later
		$upload_prefs = $this->_get_upload_paths($field_settings['vz_upload_dest']);
		$script_path = str_replace(getcwd().'/', '', FT_PATH.'ff_vz_upload/uploadify/');

		// List out all the uploaded files in a table
		$upload_count = -1;
		$out = '<table id="'.$field_name.'_list" class="vz_upload_list tableBorder" style="width:50%" cellspacing="0" cellpadding="0">';
		$out .= '<thead><tr><td class="tableHeading" colspan="2">File Name</td><td class="tableHeading" style="width:20%">Delete</td></tr></thead><tbody>';
		if ($field_data) 
		{
			// Split the saved data out into an array
			$field_data = explode(' ', $field_data);
			
			// Cycle through each item and put it in a table row
			foreach ($field_data as $file)
			{
				$upload_count++;
				$rowSwitch = ($upload_count % 2) ? 'tableCellTwo' : 'tableCellOne';
				
				// Get the thumbnail or icon
				$file_ext = strtolower(preg_replace('/^.*\./', '', $file));
				$img = "";
				
				if (array_search($file_ext, array('jpg','jpeg','png','gif')) == false) 
				{  // Show thumbnail
					$img = "<img src='".$upload_prefs['url'].$file."' alt='Thumbnail' width='40' />";
				}
				elseif ($file_ext != '')
				{  // Show file-type icon
					$icon = (is_file(FT_PATH.'ff_vz_upload/icons/'.$file_ext.'.png')) ? FT_URL.'ff_vz_upload/icons/'.$file_ext.'.png' : FT_URL.'ff_vz_upload/icons/unknown.png';
					$img = "<img src='".$icon."' alt='Icon' width='16' />";
				}
				
				// Generate the html
				$out .= "<tr><td class='".$rowSwitch."' width='40'>".$img."</td><td class='".$rowSwitch."'><input type='text' readonly='readonly' name='".$field_name."[".$upload_count."][0]' style='border:none;background:transparent' value='".$file."' /></td><td class='".$rowSwitch."'><input type='hidden' name='".$field_name."[".$upload_count."][1]' /><input type='checkbox' value='del' /></td></tr>";
			}
		}
		$out .= '</tbody></table>';
		$out .= '<div id="'.$field_name.'_btn">You must have JavaScript enabled to upload files.</div>';
		
		
		$allow_multiple = isset($field_settings['vz_upload_multiple']);

		// Include the styles and scripts
		$this->include_css('uploadify/vz_upload.css');
		$this->include_js('uploadify/jquery.uploadify.js');
		$this->include_js('uploadify/vz_upload.js');
		$this->insert_js('jQuery(document).ready(function() { setupVzUpload("'.$field_name.'", "'.$script_path.'", "'.$upload_prefs['path'].'", "'.$upload_prefs['url'].'", "'.$upload_count.'", "'.$allow_multiple.'", "'.$field_settings['vz_upload_types'].'"); });');
	
		return $out;
	}
	

	/**
	 * Display Cell
	 * 
	 * @param  string  $cell_name      The field's name
	 * @param  mixed   $cell_data      The field's current value
	 * @param  array   $cell_settings  The field's settings
	 * @return string  The cell's HTML
	 */
	function display_cell($cell_name, $cell_data, $cell_settings)
	{		
		global $DSP;
		
		$this->insert_js("$.fn.ffMatrix.onDisplayCell['ff_vz_upload'] = function(td) { setupVzUploadCell(td); }");
		
		// Set default values for the preview display in Edit Custom Field 
		$defaults['vz_upload_dest'] = key($this->_get_upload_dests());
		$defaults['vz_upload_types'] = '';
		$cell_settings = array_merge($defaults, $cell_settings);
		
		// Get the server paths we will need later
		$upload_prefs = $this->_get_upload_paths($cell_settings['vz_upload_dest']);
		$script_path = str_replace(getcwd().'/', '', FT_PATH.'ff_vz_upload/uploadify/');

		$upload_count = -1;
		$out = '';
		
		// If there is an uploaded file, display it
		if ($cell_data) 
		{			
			$upload_count++;
			
			// Get the thumbnail or icon
			$file_ext = strtolower(preg_replace('/^.*\./', '', $cell_data));
			$img = "";
			if (array_search($file_ext, array('jpg','jpeg','png','gif')) == false) 
			{  // Show thumbnail
				$img = "<img src='".$upload_prefs['url'].$cell_data."' alt='Thumbnail' width='40' />";
			}
			elseif ($file_ext != '')
			{  // Show file-type icon
				$icon = (is_file(FT_PATH.'ff_vz_upload/icons/'.$file_ext.'.png')) ? FT_URL.'ff_vz_upload/icons/'.$file_ext.'.png' : FT_URL.'ff_vz_upload/icons/unknown.png';
				$img = "<img src='".$icon."' alt='Icon' width='16' />";
			}
			
			// Generate the html
			$out .= "<div>".$img."<input type='text' readonly='readonly' name='".$cell_name."[0]' style='border:none;background:transparent' value='".$cell_data."' /><label style='float:right'><input type='hidden' name='".$cell_name."[1]' /><input type='checkbox' value='del' /> Delete</label></div>";
		}
		$out .= '<span id="'.$cell_name.'_btn" class='.$cell_name.'>You must have JavaScript enabled to upload files.</span>';

		// Include the styles and scripts
		$this->include_css('uploadify/vz_upload.css');
		$this->include_js('uploadify/jquery.uploadify.js');
		$this->include_js('uploadify/vz_upload.js');
		$this->insert_js('var vz_upload_cell['.$cell_name.'] = {"script" : "'.$script_path.'", "upload_path" : "'.$upload_prefs['path'].'", "upload_url" : "'.$upload_prefs['url'].'", "upload_count" : "'.$upload_count.'", "file_types" : "'.$cell_settings['vz_upload_types'].'"};');
	
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
		
		$upload_path = $this->_get_upload_paths($field_settings['vz_upload_dest']);
		$upload_path = $upload_path['path'];
		
		// See if they checked "delete" for any of them
		foreach ($field_data as $file)
		{
			if ($file[1] == 'del')
			{
                // Check if we are using the file anywhere else
                if (!in_array($file[0], $field_data)) {
				    // Delete the file
				    $targetFile =  str_replace('//','/',$upload_path.$file[0]);
				    @unlink($targetFile);
				}
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
	 * Save Cell
	 * 
	 * @param  string  $cell_data		The field's post data
	 * @param  array  $cell_settings	The field settings
	 */
	function save_cell($cell_data, $cell_settings)
	{
		return $this->save_field($cell_data, $cell_settings);
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
				$out = $upload_url.$field_data[0];
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
					$file_tagdata = $TMPL->swap_var_single('file_url', $upload_url.$file, $file_tagdata);
					$file_tagdata = $TMPL->swap_var_single('total_results', count($field_data), $file_tagdata);
					$file_tagdata = $TMPL->swap_var_single('count', $count+1, $file_tagdata);

					$out .= $file_tagdata;

					$count++;
				}
				
			}
		}
		
		return $out;
	}

	
	/*
	 * Get Upload Destinations
	 */
	function _get_upload_dests() 
	{
		global $DB;
		$dests = array();
		
		$results = $DB->query("SELECT id, name FROM exp_upload_prefs ORDER BY name ASC");

		// If there are any upload destinations, put them in array...
		foreach($results->result as $row)
		{
			$dests += array($row['id'] => $row['name']);    
		}
		
		return $dests;
	}
	

	/*
	 * Get a single Upload Destination's path
	 *
	 * @param int	$field_id
	 */
	function _get_upload_paths($field_id) 
	{
		global $DB;
		
		if (!$field_id) return false;
		
		$results = $DB->query("SELECT server_path, url FROM exp_upload_prefs WHERE id = ".$field_id." LIMIT 1");
		$upload_paths['path'] = $results->row['server_path'];
		$upload_paths['url'] = $results->row['url'];
		
		return $upload_paths;
	}

}

/* End of file ft.ff_vz_upload.php */
/* Location: ./system/fieldtypes/vz_upload/ft.ff_vz_upload.php */