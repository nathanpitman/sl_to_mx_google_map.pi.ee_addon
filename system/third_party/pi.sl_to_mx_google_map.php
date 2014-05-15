<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
  'pi_name'         => 'SL to MX Google Map',
  'pi_version'      => '1.0',
  'pi_author'       => 'Nine Four',
  'pi_author_url'   => 'http://ninefour.co.uk/labs/',
  'pi_description'  => 'Migrates SL Google Maps data to MX Google Maps format',
  'pi_usage'        => Sl_to_mx_google_map::usage()
);

class sl_to_mx_google_map
{

    // --------------------------------------------------------------------

    public function __construct()
    {
		$this->EE =& get_instance();

		$migrate_from_field_ref = "field_id_30";
		$migrate_to_field_ref = "field_id_67";
		$migrate_to_field_id = 67;

		$sql = "SELECT entry_id, ".$migrate_from_field_ref." FROM exp_channel_data WHERE (field_id_67 != '')";
		$query = $this->EE->db->query($sql);
		$result = $query->result();
		
		
		foreach($result AS $row) {
			if (!empty($row->$migrate_from_field_ref)) {

				$tmp = unserialize($row->$migrate_from_field_ref);
				$row->$migrate_to_field_ref = $tmp['map_lat']."|".$tmp['map_lng']."|".$tmp['map_zoom'];
				
				
				// Update the exp_channel_data table with the pipe delimited data
				$update_channel_data_sql = "UPDATE exp_channel_data SET ".$migrate_to_field_ref." = '".$row->$migrate_to_field_ref."' WHERE entry_id = $row->entry_id";
				$this->EE->db->query($update_channel_data_sql);
				
				// Update the exp_mx_google_map table with a single point
				$mx_google_map_sql = "SELECT point_id FROM exp_mx_google_map WHERE entry_id = '".$row->entry_id."'";
				$mx_google_map_query = $this->EE->db->query($mx_google_map_sql);
				$mx_google_map_entry_exists = $mx_google_map_query->row();
				
				if ($mx_google_map_entry_exists) {
				
					$update_channel_data_sql = "UPDATE exp_mx_google_map";
					$update_channel_data_sql.= " SET latitude = '".$tmp['map_lat']."',";
					$update_channel_data_sql.= " longitude = '".$tmp['map_lng']."',";
					$update_channel_data_sql.= " field_id = '".$migrate_to_field_id."'";
					$update_channel_data_sql.= " WHERE point_id = ".$mx_google_map_entry_exists->point_id."";
					$this->EE->db->query($update_channel_data_sql);
					unset($update_channel_data_sql);
				
				} else {
				
					$insert_channel_data_sql = "INSERT INTO exp_mx_google_map";
					$insert_channel_data_sql.= " (entry_id, latitude, longitude, field_id, icon)";
					$insert_channel_data_sql.= " VALUES (".$row->entry_id.", ".$tmp['map_lat'].", ".$tmp['map_lng'].", ".$migrate_to_field_id.", 'home.png')";
					$this->EE->db->query($insert_channel_data_sql);
					unset($insert_channel_data_sql);
				
				}
				
				unset($mx_google_map_entry_exists);
				unset($tmp);
				
			}
		}
		
		echo("<pre>");
		print_r($result);
		echo("</pre>");		
		echo("<hr>");
		echo("Success!");
		exit;
		
    }
    

    // --------------------------------------------------------------------

    /**
     * Usage
     *
     * This function describes how the plugin is used.
     *
     * @access  public
     * @return  string
     */
    public static function usage()
    {
        ob_start();  ?>

	This plug-in can be used to migrate data (serialized array) from SL Google Maps to MX Google Maps data format (pipe delimited string). Just modify the plug-in field references above as required and then drop the {exp:sl_to_mx_google_map} tag in a page template to run the migration.

    <?php
        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
    }
    // END
}
/* End of file pi.sl_to_mx_google_map.php */
/* Location: ./system/expressionengine/third_party/sl_to_mx_google_map/pi.sl_to_mx_google_map.php */