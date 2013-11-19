<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'Sorry, you cannot call this webpage directly.' );

if ( ! class_exists( 'ngfbOptions' ) ) {

	class ngfbOptions {

		public $opts_ver = '68';	// increment when adding/removing default options

		public $defaults = array(
			'meta_desc_len' => 156,
			'link_author_field' => 'gplus',
			'link_def_author_id' => 0,
			'link_def_author_on_index' => 0,
			'link_def_author_on_search' => 0,
			'link_publisher_url' => '',
			'fb_admins' => '',
			'fb_app_id' => '',
			'og_publisher_url' => '',
			'og_art_section' => '',
			'og_img_width' => 300,
			'og_img_height' => 300,
			'og_img_crop' => 1,
			'og_img_max' => 1,
			'og_vid_max' => 1,
			'og_vid_https' => 1,
			'og_def_img_id_pre' => 'wp',
			'og_def_img_id' => '',
			'og_def_img_url' => '',
			'og_def_img_on_index' => 1,
			'og_def_img_on_search' => 1,
			'og_def_author_id' => 0,
			'og_def_author_on_index' => 0,
			'og_def_author_on_search' => 0,
			'og_ngg_tags' => 0,
			'og_page_parent_tags' => 0,
			'og_page_title_tag' => 0,
			'og_author_field' => 'facebook',
			'og_author_fallback' => 0,
			'og_title_sep' => '-',
			'og_title_len' => 70,
			'og_desc_len' => 300,
			'og_desc_strip' => 0,
			'og_empty_tags' => 0,
			'buttons_on_index' => 0,
			'buttons_on_front' => 1,
			'buttons_add_to_post' => 1,
			'buttons_add_to_page' => 1,
			'buttons_add_to_attachment' => 1,
			'buttons_location_the_excerpt' => 'bottom',
			'buttons_location_the_content' => 'bottom',
			'buttons_link_css' => 0,
			'buttons_css_excerpt' => '',
			'buttons_css_content' => '',
			'buttons_css_shortcode' => '',
			'buttons_css_social' => '',
			'buttons_css_widget' => '',
			'fb_on_the_excerpt' => 0,
			'fb_on_the_content' => 0,
			'fb_order' => 1,
			'fb_js_loc' => 'header',
			'fb_lang' => 'en_US',
			'fb_button' => 'like',
			'fb_markup' => 'xfbml',
			'fb_send' => 1,
			'fb_layout' => 'button_count',
			'fb_width' => 200,
			'fb_font' => 'arial',
			'fb_show_faces' => 0,
			'fb_colorscheme' => 'light',
			'fb_action' => 'like',
			'fb_type' => 'button_count',
			'gp_on_the_excerpt' => 0,
			'gp_on_the_content' => 0,
			'gp_order' => 2,
			'gp_js_loc' => 'header',
			'gp_lang' => 'en-US',
			'gp_action' => 'plusone',
			'gp_size' => 'medium',
			'gp_annotation' => 'bubble',
			'tc_enable' => 0,
			'tc_site' => '',
			'tc_desc_len' => 200,
			'tc_gal_min' => 4,
			'tc_gal_size' => 'medium',
			'tc_photo_size' => 'large',
			'tc_large_size' => 'medium',
			'tc_sum_size' => 'thumbnail',
			'tc_prod_size' => 'medium',
			'twitter_on_the_excerpt' => 0,
			'twitter_on_the_content' => 0,
			'twitter_order' => 3,
			'twitter_js_loc' => 'header',
			'twitter_lang' => 'en',
			'twitter_caption' => 'title',
			'twitter_cap_len' => 140,
			'twitter_count' => 'horizontal',
			'twitter_size' => 'medium',
			'twitter_via' => 1,
			'twitter_rel_author' => 1,
			'twitter_dnt' => 1,
			'twitter_shorten' => 1,
			'linkedin_on_the_excerpt' => 0,
			'linkedin_on_the_content' => 0,
			'linkedin_order' => 4,
			'linkedin_js_loc' => 'header',
			'linkedin_counter' => 'right',
			'linkedin_showzero' => 1,
			'pin_on_the_excerpt' => 0,
			'pin_on_the_content' => 0,
			'pin_order' => 5,
			'pin_js_loc' => 'header',
			'pin_count_layout' => 'horizontal',
			'pin_img_size' => 'large',
			'pin_caption' => 'both',
			'pin_cap_len' => 500,
			'tumblr_on_the_excerpt' => 0,
			'tumblr_on_the_content' => 0,
			'tumblr_order' => 7,
			'tumblr_js_loc' => 'footer',
			'tumblr_button_style' => 'share_1',
			'tumblr_desc_len' => 300,
			'tumblr_photo' => 1,
			'tumblr_img_size' => 'large',
			'tumblr_caption' => 'both',
			'tumblr_cap_len' => 500,
			'stumble_on_the_excerpt' => 0,
			'stumble_on_the_content' => 0,
			'stumble_order' => 6,
			'stumble_js_loc' => 'header',
			'stumble_badge' => 1,
			'inc_description' => 0,
			'inc_fb:admins' => 1,
			'inc_fb:app_id' => 1,
			'inc_og:locale' => 1,
			'inc_og:site_name' => 1,
			'inc_og:title' => 1,
			'inc_og:type' => 1,
			'inc_og:url' => 1,
			'inc_og:description' => 1,
			'inc_og:image' => 1,
			'inc_og:image:secure_url' => 1,
			'inc_og:image:width' => 1,
			'inc_og:image:height' => 1,
			'inc_og:video' => 1,
			'inc_og:video:secure_url' => 1,
			'inc_og:video:width' => 1,
			'inc_og:video:height' => 1,
			'inc_og:video:type' => 1,
			'inc_article:author' => 1,
			'inc_article:publisher' => 1,
			'inc_article:published_time' => 1,
			'inc_article:modified_time' => 1,
			'inc_article:section' => 1,
			'inc_article:tag' => 1,
			'inc_product:price:amount' => 1,
			'inc_product:price:currency' => 1,
			'inc_product:availability' => 1,
			'inc_twitter:card' => 1,
			'inc_twitter:creator' => 1,
			'inc_twitter:site' => 1,
			'inc_twitter:title' => 1,
			'inc_twitter:description' => 1,
			'inc_twitter:image' => 1,
			'inc_twitter:image:width' => 1,
			'inc_twitter:image:height' => 1,
			'inc_twitter:image0' => 1,
			'inc_twitter:image1' => 1,
			'inc_twitter:image2' => 1,
			'inc_twitter:image3' => 1,
			'inc_twitter:player' => 1,
			'inc_twitter:player:width' => 1,
			'inc_twitter:player:height' => 1,
			'inc_twitter:data1' => 1,
			'inc_twitter:label1' => 1,
			'inc_twitter:data2' => 1,
			'inc_twitter:label2' => 1,
			'inc_twitter:data3' => 1,
			'inc_twitter:label3' => 1,
			'inc_twitter:data4' => 1,
			'inc_twitter:label4' => 1,
			'ngfb_opts_ver' => '',
			'ngfb_plugin_ver' => '',
			'ngfb_pro_tid' => '',
			'ngfb_preserve' => 0,
			'ngfb_reset' => 0,
			'ngfb_debug' => 0,
			'ngfb_enable_shortcode' => 0,
			'ngfb_skip_small_img' => 1,
			'ngfb_filter_content' => 1,
			'ngfb_filter_excerpt' => 0,
			'ngfb_add_to_post' => 1,
			'ngfb_add_to_page' => 1,
			'ngfb_add_to_attachment' => 1,
			'ngfb_verify_certs' => 0,
			'ngfb_file_cache_hrs' => 0,
			'ngfb_object_cache_exp' => 180,
			'ngfb_googl_api_key' => '',
			'ngfb_cdn_urls' => '',
			'ngfb_cdn_folders' => 'wp-content, wp-includes',
			'ngfb_cdn_excl' => '',
			'ngfb_cdn_not_https' => 1,
			'ngfb_cdn_www_opt' => 1,
		);

		private $renamed = array(
			'add_meta_desc' => 'inc_description',
			'og_def_img' => 'og_def_img_url',
			'og_def_home' => 'og_def_img_on_index',
			'og_def_on_home' => 'og_def_img_on_index',
			'og_def_on_search' => 'og_def_img_on_search',
			'buttons_on_home' => 'buttons_on_index',
			'buttons_lang' => 'gp_lang',
			'ngfb_cache_hours' => 'ngfb_file_cache_hrs',
			'fb_enable' => 'fb_on_the_content', 
			'gp_enable' => 'gp_on_the_content',
			'twitter_enable' => 'twitter_on_the_content',
			'linkedin_enable' => 'linkedin_on_the_content',
			'pin_enable' => 'pin_on_the_content',
			'stumble_enable' => 'stumble_on_the_content',
			'tumblr_enable' => 'tumblr_on_the_content',
			'buttons_location' => 'buttons_location_the_content',
			'og_admins' => 'fb_admins',
			'og_app_id' => 'fb_app_id',
			'ngfb_version' => 'ngfb_opts_ver',
			'link_desc_len' => 'meta_desc_len',
		);

		private $ngfb;		// ngfbPlugin

		public function __construct( &$ngfb_plugin ) {
			$this->ngfb =& $ngfb_plugin;
			$this->ngfb->debug->mark();
		}

		public function get_defaults( $idx = '' ) {
			foreach ( $this->ngfb->css_names as $css_id => $css_name ) {
				$css_file = NGFB_PLUGINDIR . 'css/' . $css_id . '-buttons.css';
				if ( empty( $this->defaults['buttons_css_' . $css_id] ) ) {
					if ( ! $fh = @fopen( $css_file, 'rb' ) )
						$this->ngfb->notices->err( 'Failed to open <u>' . $css_file . '</u> for reading.' );
					else {
						$this->defaults['buttons_css_' . $css_id] = fread( $fh, filesize( $css_file ) );
						$this->ngfb->debug->log( 'read css from file ' . $css_file );
						fclose( $fh );
					}
				}
			}
			$this->defaults = $this->add_to_post_types( $this->defaults );
			if ( ! empty( $idx ) ) 
				return $this->defaults[$idx];
			else return $this->defaults;
		}

		public function add_to_post_types( &$opts = array() ) {
			foreach ( array( 'buttons_add_to', 'ngfb_add_to' ) as $opt_prefix ) {
				foreach ( get_post_types( array( 'show_ui' => true, 'public' => true ), 'objects' ) as $post_type ) {
					$key = $opt_prefix . '_' . $post_type->name;
					if ( ! array_key_exists( $key, $opts ) ) {
						switch ( $post_type->name ) {
							case 'shop_coupon' :
								$opts[$key] = 0;
								break;
							default :
								$opts[$key] = 1;
								break;
						}
					}
				}
			}
			return $opts;
		}

		public function quick_check( &$opts = array() ) {
			$err_msg = '';
			if ( ! empty( $opts ) && is_array( $opts ) ) {
				// add support for post types that may have been added
				$opts = $this->add_to_post_types( $opts );

				if ( ( empty( $opts['ngfb_plugin_ver'] ) || $opts['ngfb_plugin_ver'] !== $this->ngfb->version ) ||
					( empty( $opts['ngfb_opts_ver'] ) || $opts['ngfb_opts_ver'] !== $this->opts_ver ) ) {
					$this->ngfb->debug->log( 'plugin version different than options version: calling upgrade() method.' );
					$opts = $this->upgrade( $opts, $this->get_defaults() );
				}
			} else {
				if ( $opts === false )
					$err_msg = 'did not find an "' . NGFB_OPTIONS_NAME . '" entry in';
				elseif ( ! is_array( $opts ) )
					$err_msg = 'returned a non-array value when reading "' . NGFB_OPTIONS_NAME . '" from';
				elseif ( empty( $opts ) )
					$err_msg = 'returned an empty array when reading "' . NGFB_OPTIONS_NAME . '" from';
				else 
					$err_msg = 'returned an unknown condition when reading "' . NGFB_OPTIONS_NAME . '" from';

				$this->ngfb->debug->log( 'WordPress ' . $err_msg . ' the options database table.' );
				$opts = $this->get_defaults();
			}
			if ( is_admin() ) {
				if ( ! empty( $err_msg ) ) {
					$url = $this->ngfb->util->get_admin_url( 'general' );
					$this->ngfb->notices->err( 'WordPress ' . $err_msg . ' the options database table. 
						All plugin settings have been returned to their default values (though nothing has been saved back to the database yet). 
						<a href="' . $url . '">Please visit the plugin settings pages to review and save the options</a>.' );
				}
				if ( $this->ngfb->options['og_img_width'] < NGFB_MIN_IMG_SIZE || $this->ngfb->options['og_img_height'] < NGFB_MIN_IMG_SIZE ) {
					$url = $this->ngfb->util->get_admin_url( 'general' );
					$size_desc = $this->ngfb->options['og_img_width'] . 'x' . $this->ngfb->options['og_img_height'];
					$this->ngfb->notices->inf( 'The image size of ' . $size_desc . ' for images in the Open Graph meta tags
						is smaller than the minimum of ' . NGFB_MIN_IMG_SIZE . 'x' . NGFB_MIN_IMG_SIZE . '. 
						<a href="' . $url . '">Please enter a larger Image Size on the General Settings page</a>.' );
				}
				if ( $this->ngfb->is_avail['aop'] == true && empty( $this->ngfb->options['ngfb_pro_tid'] ) ) {
					$url = $this->ngfb->util->get_admin_url( 'advanced' );
					$this->ngfb->notices->nag( '<p>The ' . $this->ngfb->fullname . ' <em>Authentication ID</em> option value is empty. 
						In order for the plugin to authenticate itself for future updates,<br/><a href="' . $url . '">please enter 
						the unique Authenticaton ID you received by email on the Advanced Settings page</a>.</p>' );
				}

			}
			return $opts;
		}

		// sanitize and validate input
		public function sanitize( $opts = array(), $def_opts = array() ) {

			// make sure we have something to work with
			if ( ! empty( $def_opts ) && is_array( $def_opts ) ) {

				// loop through all the known option keys
				foreach ( $def_opts as $key => $def_val ) {

					// remove html, decode entities, and strip slashes
					if ( array_key_exists( $key, $opts ) )
						$opts[$key] = stripslashes( html_entity_decode( wp_filter_nohtml_kses( $opts[$key] ) ) );

					switch ( $key ) {

						// twitter-style usernames (a-z0-9, max 15 chars)
						case 'tc_site' :
							$opts[$key] = substr( preg_replace( '/[^a-z0-9_]/', '', 
								strtolower( $opts[$key] ) ), 0, 15 );
							if ( ! empty( $opts[$key] ) ) 
								$opts[$key] = '@' . $opts[$key];
							break;

						// stip leading urls off Facebook usernames
						case 'fb_admins' :
							$opts[$key] = preg_replace( '/(http|https):\/\/[^\/]*?\//', '', 
								$opts[$key] );
							break;

						// must be a url (reset to default if not)
						case 'og_img_url' :
						case 'og_vid_url' :
						case 'og_def_img_url' :
						case 'og_publisher_url' :
						case 'link_publisher_url' :
						case 'ngfb_cdn_urls' :
							if ( ! empty( $opts[$key] ) && 
								strpos( $opts[$key], '://' ) === false ) 
									$opts[$key] = $def_val;
							break;

						// must be numeric (blank or zero is ok)
						case 'link_def_author_id' :
						case 'og_desc_len' : 
						case 'og_img_max' :
						case 'og_vid_max' :
						case 'og_img_id' :
						case 'og_def_img_id' :
						case 'og_def_author_id' :
						case 'ngfb_file_cache_hrs' :
							if ( ! empty( $opts[$key] ) && 
								! is_numeric( $opts[$key] ) )
									$opts[$key] = $def_val;
							break;

						// integer options that must me 1 or more (not zero)
						case 'meta_desc_len' : 
						case 'og_img_width' : 
						case 'og_img_height' : 
						case 'og_title_len' : 
						case 'fb_order' : 
						case 'fb_width' : 
						case 'gp_order' : 
						case 'twitter_order' : 
						case 'linkedin_order' : 
						case 'pin_order' : 
						case 'pin_cap_len' : 
						case 'tumblr_order' : 
						case 'tumblr_desc_len' : 
						case 'tumblr_cap_len' :
						case 'stumble_order' : 
						case 'stumble_badge' :
						case 'ngfb_object_cache_exp' :
							if ( empty( $opts[$key] ) || 
								! is_numeric( $opts[$key] ) )
									$opts[$key] = $def_val;
							break;

						// needs to be filtered
						case 'og_title_sep' :
							$opts[$key] = $this->ngfb->util->decode( trim( wptexturize( ' ' . $opts[$key] . ' ' ) ) );

						// text strings that can be blank
						case 'fb_app_id' :
						case 'og_title' :
						case 'og_desc' :
						case 'meta_desc' :
						case 'tc_desc' :
						case 'pin_desc' :
						case 'tumblr_img_desc' :
						case 'tumblr_vid_desc' :
						case 'twitter_desc' :
						case 'ngfb_pro_tid' :
						case 'ngfb_googl_api_key' :
						case 'ngfb_cdn_folders' :
						case 'ngfb_cdn_excl' :
							if ( ! empty( $opts[$key] ) )
								$opts[$key] = trim( $opts[$key] );
							break;

						// options that cannot be blank
						case 'og_art_section' :
						case 'link_author_field' :
						case 'og_img_id_pre' : 
						case 'og_def_img_id_pre' : 
						case 'og_author_field' :
						case 'buttons_location_the_excerpt' : 
						case 'buttons_location_the_content' : 
						case 'buttons_css_excerpt' :
						case 'buttons_css_content' :
						case 'buttons_css_shortcode' :
						case 'buttons_css_social' :
						case 'buttons_css_widget' :
						case 'fb_js_loc' : 
						case 'fb_markup' : 
						case 'gp_js_loc' : 
						case 'gp_lang' : 
						case 'gp_action' : 
						case 'gp_size' : 
						case 'gp_annotation' : 
						case 'twitter_js_loc' : 
						case 'twitter_count' : 
						case 'twitter_size' : 
						case 'linkedin_js_loc' : 
						case 'linkedin_counter' :
						case 'pin_js_loc' : 
						case 'pin_count_layout' :
						case 'pin_img_size' :
						case 'pin_caption' :
						case 'tumblr_js_loc' : 
						case 'tumblr_button_style' :
						case 'tumblr_img_size' :
						case 'tumblr_caption' :
						case 'stumble_js_loc' : 
							if ( empty( $opts[$key] ) ) 
								$opts[$key] = $def_val;
							break;

						// everything else is assumed to be a true / false checkbox option
						default :
							// make sure the default option is true/false - just in case
							if ( $def_val === 0 || $def_val === 1 )
								$opts[$key] = empty( $opts[$key] ) ? 0 : 1;
							break;
					}
				}
				unset ( $key, $def_val );

				if ( array_key_exists( 'og_desc_len', $opts ) && $opts['og_desc_len'] < NGFB_MIN_DESC_LEN ) 
					$opts['og_desc_len'] = NGFB_MIN_DESC_LEN;
	
			}
			return $opts;
		}

		public function upgrade( &$opts = array(), $def_opts = array() ) {

			// make sure we have something to work with
			if ( empty( $opts ) || ! is_array( $opts ) ) {
				$this->ngfb->debug->log( 'exiting early: options variable is empty and/or not array' );
				return $opts;
			}

			$opts = $this->rename_keys( $this->renamed, $opts );

			// these option names may have been used in the past, so remove them, just in case
			if ( $opts['ngfb_opts_ver'] < 30 ) {
				unset( $opts['og_img_width'] );
				unset( $opts['og_img_height'] );
				unset( $opts['og_img_crop'] );
			}

			// upgrade the old 'og_img_size' name into width / height / crop values
			if ( array_key_exists( 'og_img_size', $opts ) ) {
				if ( ! empty( $opts['og_img_size'] ) && $opts['og_img_size'] !== 'medium' ) {
					$size_info = $this->ngfb->media->get_size_info( $opts['og_img_size'] );
					if ( $size_info['width'] > 0 && $size_info['height'] > 0 ) {
						$opts['og_img_width'] = $size_info['width'];
						$opts['og_img_height'] = $size_info['height'];
						$opts['og_img_crop'] = $size_info['crop'];
					}
					unset( $opts['og_img_size'] );
				}
			}

			// unset options that no longer exist
			foreach ( $opts as $key => $val )
				// check that the key doesn't exist in the default options (which is a complete list of the current options used)
				if ( ! empty( $key ) && ! array_key_exists( $key, $def_opts ) ) {
					if ( $this->ngfb->debug->is_on() == true )
						$this->ngfb->notices->inf( 'Removing deprecated option \'' . 
							$key . '\' with a value of \'' . $val . '\'.' );
					unset( $opts[$key] );
				}
			unset ( $key, $val );

			// add missing options and set to defaults
			foreach ( $def_opts as $key => $def_val ) {
				if ( ! empty( $key ) && ! array_key_exists( $key, $opts ) ) {
					$this->ngfb->debug->log( 'adding missing ' . $key . ' option.' );
					$opts[$key] = $def_val;
				}
			}

			// sanitize and verify the options - just in case
			$opts = $this->sanitize( $opts, $def_opts );

			// mark the new options as current
			$old_opts_ver = $opts['ngfb_opts_ver'];
			$opts['ngfb_opts_ver'] = $this->opts_ver;
			$opts['ngfb_plugin_ver'] = $this->ngfb->version;

			// don't save unless someone is there to see the success / error messages
			// plugin activation may hide notices, so main plugin class tests for activation and exits early
			if ( is_admin() ) {

				// update_option() returns false if options are the same or there was an error, 
				// so check to make sure they need to be updated to avoid throwing a false error
				if ( get_option( NGFB_OPTIONS_NAME ) !== $opts ) {

					if ( $this->ngfb->is_avail['aop'] !== true && empty( $this->ngfb->options['ngfb_pro_tid'] ) ) {
						$this->ngfb->debug->log( 'adding notices message update-nag \'pro_details\'' );
						$this->ngfb->notices->nag( $this->ngfb->msg->get( 'pro_details' ) );
					}

					if ( update_option( NGFB_OPTIONS_NAME, $opts ) == true ) {
						if ( $old_opts_ver !== $this->opts_ver ) {
							$this->ngfb->debug->log( 'upgraded plugin options have been saved' );
							$this->ngfb->notices->inf( 'Plugin settings have been upgraded and saved.' );
						}
					} else {
						$this->ngfb->debug->log( 'failed to save the upgraded plugin options' );
						$this->ngfb->notices->err( 'The plugin settings have been upgraded, 
							but WordPress returned an error when saving them.' );
						return $opts;
					}
				} else $this->ngfb->debug->log( 'new and old options array is identical' );
			} else $this->ngfb->debug->log( 'not in admin interface: postponing options save' );

			$this->ngfb->debug->log( 'options successfully upgraded' );
			return $opts;
		}

		public function rename_keys( $renamed = array(), $opts = array() ) {
			// move old option values to new option names
			foreach ( $renamed as $old => $new )
				// rename if the old array key exists, but not the new one (we don't want to overwrite current values)
				if ( ! empty( $old ) && ! empty( $new ) && array_key_exists( $old, $opts ) && ! array_key_exists( $new, $opts ) ) {
					if ( $this->ngfb->debug->is_on() == true )
						$this->ngfb->notices->inf( 'Renamed \'' . $old . '\' option to \'' . 
							$new . '\' with a value of \'' . $opts[$old] . '\'.' );
					$opts[$new] = $opts[$old];
					unset( $opts[$old] );
				}
			unset ( $old, $new );
			return $opts;
		}

	}

}
?>
