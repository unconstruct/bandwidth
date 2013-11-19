<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'Sorry, you cannot call this webpage directly.' );

if ( ! class_exists( 'ngfbSettingsAdvanced' ) && class_exists( 'ngfbAdmin' ) ) {

	class ngfbSettingsAdvanced extends ngfbAdmin {

		protected $ngfb;
		protected $menu_id;
		protected $menu_name;
		protected $pagehook;

		// executed by ngfbSettingsAdvancedPro() as well
		public function __construct( &$ngfb_plugin, $id, $name ) {
			$this->ngfb =& $ngfb_plugin;
			$this->ngfb->debug->mark();
			$this->menu_id = $id;
			$this->menu_name = $name;
		}

		protected function add_meta_boxes() {
			// add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
			add_meta_box( $this->pagehook . '_plugin', 'Plugin Settings', array( &$this, 'show_metabox_plugin' ), $this->pagehook, 'normal' );
			add_meta_box( $this->pagehook . '_cache', 'Cache Settings', array( &$this, 'show_metabox_cache' ), $this->pagehook, 'normal' );
			add_meta_box( $this->pagehook . '_rewrite', 'Rewrite Settings', array( &$this, 'show_metabox_rewrite' ), $this->pagehook, 'normal' );
		}

		public function show_metabox_plugin() {
			echo '<table class="ngfb-settings"><tr>';
			foreach ( $this->get_pre_plugin() as $row ) echo '<tr>' . $row . '</tr>';
			echo $this->ngfb->util->th( 'Preserve Settings on Uninstall', 'highlight', null, '
				Check this option if you would like to preserve all ' . $this->ngfb->fullname . '
				settings when you <em>uninstall</em> the plugin (default is unchecked).' ); 
			echo '<td>', $this->ngfb->admin->form->get_checkbox( 'ngfb_preserve' ), '</td>';
			echo '</tr><tr>';
			echo $this->ngfb->util->th( 'Reset Settings on Activate', null, null, '
				Check this option if you would like to reset the ' . $this->ngfb->fullname . '
				settings to their default values when you <em>deactivate</em>, and then 
				<em>re-activate</em> the plugin (default is unchecked).' ); 
			echo '<td>', $this->ngfb->admin->form->get_checkbox( 'ngfb_reset' ), '</td>';
			echo '</tr><tr>';
			echo $this->ngfb->util->th( 'Add Hidden Debug Info', null, null, '
				Include hidden debug information with the Open Graph meta tags (default is unchecked).' ); 
			echo '<td>', $this->ngfb->admin->form->get_checkbox( 'ngfb_debug' ), '</td>';
			echo '</tr><tr>';
			echo $this->ngfb->util->th( 'Enable Shortcode(s)', 'highlight', null, '
				Enable the ' . $this->ngfb->fullname . ' content shortcode(s) (default is unchecked).' ); 
			echo '<td>', $this->ngfb->admin->form->get_checkbox( 'ngfb_enable_shortcode' ), '</td>';
			echo '</tr><tr>';
			echo $this->ngfb->util->th( 'Ignore Small Images', 'highlight', null, '
				' . $this->ngfb->fullname . ' will attempt to include images from img html tags it finds in the content.
				The img html tags must have a width and height attribute, and their size must be equal to or larger than the 
				<em>Image Dimensions</em> you\'ve chosen (on the General settings page). 
				You can uncheck this option to include smaller images from the content, 
				or refer to the <a href="http://wordpress.org/extend/plugins/nextgen-facebook/faq/">FAQ</a> 
				for additional solutions.' ); 
			echo '<td>', $this->ngfb->admin->form->get_checkbox( 'ngfb_skip_small_img' ), '</td>';
			echo '</tr><tr>';
			echo $this->ngfb->util->th( 'Apply Content Filters', null, null, '
				Apply the standard WordPress filters to render the content (default is checked).
				This renders all shortcodes, and allows ' . $this->ngfb->fullname . ' to detect images and 
				embedded videos that may be provided by these shortcodes.' ); 
			echo '<td>', $this->ngfb->admin->form->get_checkbox( 'ngfb_filter_content' ), '</td>';
			echo '</tr><tr>';
			echo $this->ngfb->util->th( 'Apply Excerpt Filters', null, null, '
				Apply the standard WordPress filters to render the excerpt (default is unchecked).
				Check this option if you use shortcodes in your excerpt, for example.' ); 
			echo '<td>', $this->ngfb->admin->form->get_checkbox( 'ngfb_filter_excerpt' ), '</td>';
			echo '</tr>';
			foreach ( $this->get_more_plugin() as $row ) echo '<tr>' . $row . '</tr>';
			echo '</table>';
		}

		protected function get_pre_plugin() {
			return array(
				$this->ngfb->util->th( 'Authentication ID', 'highlight', null, '
				After purchasing of the Pro version, an email will be sent to you with an Authentication ID and installation instructions.
				Enter your unique Authentication ID here, and after saving the changes, an update for \'' . $this->ngfb->fullname . '\' 
				will appear on the <a href="' . get_admin_url( null, 'update-core.php' ) . '">WordPress Updates</a> page. 
				Update the \'' . $this->ngfb->fullname . '\' plugin to download and activate the new Pro version.' ) .
				'<td class="blank">' . $this->ngfb->admin->form->get_input( 'ngfb_pro_tid' ) . '</td>',
			);
		}

		protected function get_more_plugin() {
			$add_to_checkboxes = '';
			foreach ( get_post_types( array( 'show_ui' => true, 'public' => true ), 'objects' ) as $post_type )
				$add_to_checkboxes .= '<p>' . $this->ngfb->admin->form->get_hidden( 'ngfb_add_to_' . $post_type->name ) . ' ' . $post_type->label . '</p>';

			return array(
				'<td colspan="2" align="center">' . $this->ngfb->msg->get( 'pro_feature' ) . '</td>',

				$this->ngfb->util->th( 'Add Custom Settings To', null, null, '
				The Custom Settings metabox, which allows you to enter custom Open Graph values 
				(among other options), is available on the Post, Page, Media and most custom post 
				type admin webpages by default. 
				If your theme (or another plugin) supports additional custom post types, 
				and you would like to <em>exclude</em> the Custom Settings metabox from these 
				admin webpages, uncheck the appropriate options here.' ) . 
				'<td class="blank">' . $add_to_checkboxes . '</td>',
			);
		}

		public function show_metabox_cache() {
			echo '<table class="ngfb-settings"><tr>';
			echo $this->ngfb->util->th( 'Object Cache Expiry', null, null, '
				' . $this->ngfb->fullname . ' saves the rendered (filtered) content to a non-presistant cache (wp_cache), 
				and the completed Open Graph meta tags and social buttons to a persistant (transient) cache. 
				Changes to the website content and webpages will not be reflected in the Open Graph and NGFB social sharing 
				buttons until the object cache has expired. 
				Decrease this value if your content is often revised after publishing, or increase it to improve performance. 
				The default is ' . $this->ngfb->opt->defaults['ngfb_object_cache_exp'] . ' seconds, and the minimum value is 
				1 second (such a low value is not recommended).
				' );
			echo '<td nowrap>', $this->ngfb->admin->form->get_input( 'ngfb_object_cache_exp', 'short' ), ' Seconds</td>';
			echo '</tr>';
			foreach ( $this->get_more_cache() as $row ) echo '<tr>' . $row . '</tr>';
			echo '</table>';
		}

		protected function get_more_cache() {
			return array(
				'<td colspan="2" align="center">' . $this->ngfb->msg->get( 'pro_feature' ) . '</td>',

				$this->ngfb->util->th( 'File Cache Expiry', 'highlight', null, '
				' . $this->ngfb->fullname . ' can save social sharing images and JavaScript to a cache folder, 
				providing URLs to these files instead of the originals. 
				If your hosting infrastructure performs reasonably well, this option can improve page load times significantly.' ) .
				'<td class="blank">' .  $this->ngfb->admin->form->get_hidden( 'ngfb_file_cache_hrs' ) . '</td>',

				$this->ngfb->util->th( 'Verify SSL Certificates', null, null, '
				An option to enable verification of peer SSL certificates when fetching content to be cached using HTTPS.' ) .
				'<td class="blank">' .  $this->ngfb->admin->form->get_hidden( 'ngfb_verify_certs' ) . '</td>',
			);
		}

		public function show_metabox_rewrite() {
			echo '<table class="ngfb-settings"><tr>';

			echo $this->ngfb->util->th( 'Goo.gl Simple API Access Key', 'highlight', null, '
				The "Google URL Shortener API Key" for this website. If you don\'t already have one, visit Google\'s 
				<a href="https://developers.google.com/url-shortener/v1/getting_started#APIKey" target="_blank">acquiring 
				and using an API Key</a> documentation, and follow the directions to acquire your <em>Simple API Access Key</em>.
				' );
			echo '<td>', $this->ngfb->admin->form->get_input( 'ngfb_googl_api_key', 'wide' ), '</td>';

			echo '</tr>';

			foreach ( $this->get_more_rewrite() as $row ) echo '<tr>' . $row . '</tr>';

			echo '</table>';
		}

		protected function get_more_rewrite() {
			return array(
				'<td colspan="2" align="center">' . $this->ngfb->msg->get( 'pro_feature' ) . '</td>',

				$this->ngfb->util->th( 'Static Content URL(s)', 'highlight', null, '
				Rewrite image URLs in the Open Graph meta tags, image URLs shared by social buttons (Pinterest and Tumblr), 
				and cached social media files (see the <em>File Cache Expiry</em> option above).' ) . 
				'<td class="blank">' .  $this->ngfb->admin->form->get_hidden( 'ngfb_cdn_urls' ) . '</td>',

				$this->ngfb->util->th( 'Include Folders', null, null, '
				A comma delimited list of patterns to match. These patterns must be present in the URL for the rewrite to take place 
				(the default value is "<em>wp-content, wp-includes</em>").') .
				'<td class="blank">' .  $this->ngfb->admin->form->get_hidden( 'ngfb_cdn_folders' ) . '</td>',

				$this->ngfb->util->th( 'Exclude Patterns', null, null, '
				A comma delimited list of patterns to match. If these patterns are found in the URL, the rewrite will be skipped 
				(the default value is blank).' ) .
				'<td class="blank">' .  $this->ngfb->admin->form->get_hidden( 'ngfb_cdn_excl' ) . '</td>',

				$this->ngfb->util->th( 'Not when Using HTTPS', null, null, '
				Skip rewriting URLs when using HTTPS (useful if your CDN provider does not offer HTTPS, for example).' ) .
				'<td class="blank">' .  $this->ngfb->admin->form->get_hidden( 'ngfb_cdn_not_https' ) . '</td>',

				$this->ngfb->util->th( 'www is Optional', null, null, '
				The www hostname prefix (if any) in the WordPress site URL is optional (default is checked).' ) .
				'<td class="blank">' .  $this->ngfb->admin->form->get_hidden( 'ngfb_cdn_www_opt' ) . '</td>',
			);
		}

	}
}

?>
