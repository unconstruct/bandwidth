<?php
/* 
Plugin Update Class, Copyright (c) 2012 by Rob Landry
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/

This script is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 3 of the License, or (at your option) any later
version.

This script is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE. See the GNU General Public License for more details at
http://www.gnu.org/licenses/.
*/

if (!class_exists('ngfbUpdate')) {
class ngfbUpdate {

	public $json_url = '';
	public $file_path = '';
	public $base_name = '';
	public $slug = '';
	public $cron_hook = 'plugin_updates';
	public $time_period = 12;
	public $sched_name = 'every12hours';
	public $update_info_option = '';
	public $debug;

	public function __construct( &$ngfb_plugin ) {
		$this->ngfb =& $ngfb_plugin;
		$this->ngfb->debug->mark();
		$this->json_url = $this->ngfb->urls['update'] . '?transaction=' . $this->ngfb->options['ngfb_pro_tid'];
		$this->file_path = NGFB_FILEPATH;
		$this->base_name = plugin_basename( $this->file_path );
		$this->slug = $this->ngfb->slug;
		$this->cron_hook = 'plugin_updates-' . $this->slug;
		$this->time_period = $this->ngfb->update_hours;
		$this->sched_name = 'every' . $this->time_period . 'hours';
		$this->update_info_option = 'external_updates-' . $this->slug;
		$this->install_hooks();
	}

	public function install_hooks() {
		add_filter( 'plugins_api', array( &$this, 'inject_data' ), 10, 3 );
		add_filter( 'site_transient_update_plugins', array(&$this,'inject_update'));
		if ($this->time_period > 0) {
			add_filter('cron_schedules', array(&$this, 'custom_schedule'));
			add_action($this->cron_hook, array(&$this, 'check_for_updates'));
			$schedule = wp_get_schedule( $this->cron_hook );
			// check for schedule mismatch
			if ( ! empty( $schedule ) && $schedule !== $this->sched_name ) {
				$this->debug->log('changing '.$this->cron_hook.' schedule from '.$schedule.' to '.$this->sched_name);
				wp_clear_scheduled_hook($this->cron_hook);
			}
			// add schedule if it doesn't exist
			if ( ! defined('WP_INSTALLING') && ! wp_next_scheduled( $this->cron_hook ) ) {
				// remove old schedule name (if it exists)
				if ( wp_get_schedule( 'pcfu_updates-' . $this->slug ) )
					wp_clear_scheduled_hook( 'pcfu_updates-' . $this->slug );
				wp_schedule_event( time(), $this->sched_name, $this->cron_hook );	// since wp 2.1.0
			}
		} else wp_clear_scheduled_hook( $this->cron_hook );
	}

	public function inject_data( $result, $action = null, $args = null ) {
	    	$found = ( $action == 'plugin_information' ) && isset( $args->slug ) && ( $args->slug == $this->slug );
		if ( ! $found ) return $result;
		$plugin_data = $this->get_json();
		if ( $plugin_data ) 
			return $plugin_data->json_to_wp();
		return $result;
	}

	public function inject_update( $updates ) {
		if ( ! empty( $updates->response[$this->base_name] ) ) {
			unset( $updates->response[$this->base_name] );
		}
		$option_data = get_site_option($this->update_info_option);
		if (!empty($option_data) && is_object($option_data->update) && !empty($option_data->update)) {
			if (version_compare($option_data->update->version, $this->get_installed_version(), '>')) {
				$updates->response[$this->base_name] = $option_data->update->json_to_wp();
			}
		}
		return $updates;
	}

	public function custom_schedule( $schedule ) {
		if ($this->time_period > 0) {
			$schedule[$this->sched_name] = array(
				'interval' => $this->time_period * 3600,
				'display' => sprintf('Every %d hours', $this->time_period)
			);
		}
		return $schedule;
	}

	public function check_for_updates() {
		$option_data = get_site_option( $this->update_info_option );
		if ( empty( $option_data ) ) {
			$option_data = new StdClass;
			$option_data->lastCheck = 0;
			$option_data->checkedVersion = 0;
			$option_data->update = null;
		}
		$option_data->lastCheck = time();
		$option_data->checkedVersion = $this->get_installed_version();
		update_site_option( $this->update_info_option, $option_data );

		$option_data->update = $this->get_update();
		update_site_option( $this->update_info_option, $option_data );
	}

	public function get_update() {
		$plugin_data = $this->get_json(
			array( 'checking_for_updates' => '1' )
		);
		if ( $plugin_data == null ) return null;
		$plugin_data = ngfbPluginUpdate::from_plugin_data( $plugin_data );
		return $plugin_data;
	}

	public function get_json( $query = array() ) {
		$query['installed_version'] = $this->get_installed_version();
		$options = array(
			'timeout' => 10, 
			'headers' => array( 'Accept' => 'application/json' )
		);
		$url = $this->json_url;
		if ( ! empty( $query ) ) 
			$url = add_query_arg( $query, $url );
		$result = wp_remote_get( $url, $options );
		$plugin_data = null;
		if ( ! is_wp_error( $result )
			&& isset( $result['response']['code'] )
			&& ( $result['response']['code'] == 200 )
			&& ! empty( $result['body'] ) ) {

			if ( ! empty( $result['headers']['x-smp-error'] ) )
				$this->ngfb->notices->err( json_decode( $result['body'] ), true, false );
			else
				$plugin_data = ngfbPluginData::from_json( $result['body'] );
		}
		return $plugin_data;
	}

	public function get_installed_version() {
		$version = 0;
		if ( ! function_exists( 'get_plugins' ) ) 
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		$plugins = get_plugins();
		if ( array_key_exists( $this->base_name, $plugins ) && 
			array_key_exists( 'Version', $plugins[$this->base_name] ) )
				$version = $plugins[$this->base_name]['Version'];
		return apply_filters( 'ngfb_installed_version', $version );
	}

} # End Class
} # End If


if (!class_exists('ngfbPluginData')) {
class ngfbPluginData {

	public $id = 0;
	public $name;
	public $slug;
	public $version;
	public $homepage;
	public $sections;
	public $download_url;
	public $author;
	public $author_homepage;
	public $requires;
	public $tested;
	public $upgrade_notice;
	public $rating;
	public $num_ratings;
	public $downloaded;
	public $last_updated;

	public static function from_json( $json ) {
		$json_data = json_decode( $json );
		if ( empty( $json_data ) || ! is_object( $json_data ) ) return null;
		$exists = isset( $json_data->name ) && !empty( $json_data->name )
			&& isset( $json_data->version ) && !empty( $json_data->version );
		if ( ! $exists ) return null;
		$plugin_data = new ngfbPluginData();
		foreach( get_object_vars( $json_data ) as $key => $value) {
			$plugin_data->$key = $value;
		}
		return $plugin_data;
	}

	public function json_to_wp(){
		$fields = array(
			'name', 
			'slug', 
			'version', 
			'requires', 
			'tested', 
			'rating', 
			'upgrade_notice',
			'num_ratings', 
			'downloaded', 
			'homepage', 
			'last_updated',
			'download_url',
			'author_homepage');
		$data = new StdClass;
		foreach ($fields as $field) {
			if (isset($this->$field)) {
				if ($field == 'download_url') {
					$data->download_link = $this->download_url; }
				elseif ($field == 'author_homepage') {
					$data->author = sprintf('<a href="%s">%s</a>', $this->author_homepage, $this->author); }
				else { $data->$field = $this->$field; }
			} elseif ($field == 'author_homepage') 
				$data->author = $this->author;
		}
		if (is_array($this->sections)) 
			$data->sections = $this->sections;
		elseif (is_object($this->sections)) 
			$data->sections = get_object_vars($this->sections);
		else 
			$data->sections = array('description' => '');
		return apply_filters( 'ngfb_wp_plugin_data', $data );
	}
} # End Class
} # End If


if (!class_exists('ngfbPluginUpdate')) {
class ngfbPluginUpdate {

	public $id = 0;
	public $slug;
	public $version = 0;
	public $homepage;
	public $download_url;
	public $upgrade_notice;

	public function from_json($json){
		$plugin_data = ngfbPluginData::from_json($json);
		if ($plugin_data != null) 
			return self::from_plugin_data($plugin_data);
		else return null;
	}

	public static function from_plugin_data($data){
		$plugin_update = new ngfbPluginUpdate();
		$fields = array(
			'id', 
			'slug', 
			'version', 
			'homepage', 
			'download_url', 
			'upgrade_notice');
		foreach( $fields as $field )
			$plugin_update->$field = $data->$field;
		return $plugin_update;
	}

	public function json_to_wp() {
		$data = new StdClass;
		$fields = array(
			'id'=>'id',
			'slug'=>'slug',
			'new_version'=>'version',
			'url'=>'homepage',
			'package'=>'download_url',
			'upgrade_notice'=>'upgrade_notice');
		foreach ( $fields as $new_field => $old_field ) {
			if ( isset( $this->$old_field ) )
				$data->$new_field = $this->$old_field;
		}
		return apply_filters( 'ngfb_wp_plugin_update', $data );
	}
} # End Class
} # End If

?>
