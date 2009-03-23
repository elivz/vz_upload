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
    
	var $hooks = array('publish_form_headers');


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

		$out = '';
	
	
		// Get the file upload destinations
		$results = $DB->query("SELECT id, name FROM exp_upload_prefs ORDER BY name ASC");
		
		if ($results->num_rows > 0)
		{
			// If there are any upload destinations, put them in a select box...
			$out .= '<label for="vz_upload_dest">'.$LANG->line('select_destination').':</label> ';
			$out .= '<select name="vz_upload_dest" class="select">';
			foreach($results->result as $row)
			{
				$out .= '<option value='.$row['id'].'>'.$row['name'].'</option>';    
			}
			$out .= '</select>';
		}
		else
		{
			$out .= '<p class="hightlight">'.$LANG->line('no_destinations_found').'</p>';
		}

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
		
		return '<input type="file" name="ff_vz_upload_'.$field_name.'" class="vz_upload" />';
		
	}

}

/* End of file ft.ff_vz_upload.php */
/* Location: ./system/fieldtypes/vz_upload/ft.ff_vz_upload.php */