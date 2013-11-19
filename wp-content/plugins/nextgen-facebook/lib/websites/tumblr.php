<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'Sorry, you cannot call this webpage directly.' );

if ( ! class_exists( 'ngfbSettingsTumblr' ) && class_exists( 'ngfbSettingsSocialSharing' ) ) {

	class ngfbSettingsTumblr extends ngfbSettingsSocialSharing {

		protected $ngfb;

		public function __construct( &$ngfb_plugin ) {
			$this->ngfb =& $ngfb_plugin;
			$this->ngfb->debug->mark();
		}

		public function get_rows() {
			$buttons = '<div class="btn_wizard_row clearfix" id="button_styles">' . "\n";
			foreach ( range( 1, 4 ) as $i ) {
				$buttons .= '<div class="btn_wizard_column share_' . $i . '">' . "\n";
				foreach ( array( '', 'T' ) as $t ) {
					$buttons .= '
						<div class="btn_wizard_example clearfix">
						<label for="share_' . $i . $t . '">
						<input type="radio" id="share_' . $i . $t . '" 
							name="' . $this->ngfb->admin->form->options_name . '[tumblr_button_style]" 
							value="share_' . $i . $t . '" ' . 
							checked( 'share_' . $i . $t, $this->ngfb->options['tumblr_button_style'], false ) . '/>
						<img src="' . $this->ngfb->util->get_cache_url( 'http://platform.tumblr.com/v1/share_' . $i . $t . '.png' ) . '" 
							height="20" class="share_button_image"/>
						</label>
						</div>
					';
				}
				$buttons .= '</div>' . "\n";
			}
			$buttons .= '</div>' . "\n";

			return array(
				$this->ngfb->util->th( 'Add Button to', 'short', null,
				'The Tumblr button shares a <em>featured</em> or <em>attached</em> image (when the
				<em>Use Featured Image</em> option is checked), embedded video, the content of <em>quote</em> 
				custom Posts, or the webpage link.' ) . '<td>' . 
				$this->ngfb->admin->form->get_checkbox( 'tumblr_on_the_content' ) . ' the Content and / or ' . 
				$this->ngfb->admin->form->get_checkbox( 'tumblr_on_the_excerpt' ) . ' the Excerpt Text</td>',

				$this->ngfb->util->th( 'Preferred Order', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_select( 'tumblr_order', range( 1, count( $this->ngfb->social_prefix ) ), 'short' ) . '</td>',

				$this->ngfb->util->th( 'JavaScript in', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_select( 'tumblr_js_loc', $this->js_locations ) . '</td>',

				$this->ngfb->util->th( 'Button Style', 'short' ) . '<td>' . $buttons . '</td>',

				$this->ngfb->util->th( 'Use Featured Image', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_checkbox( 'tumblr_photo' ) . '</td>',

				$this->ngfb->util->th( 'Image Size to Share', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_select_img_size( 'tumblr_img_size' ) . '</td>',

				$this->ngfb->util->th( 'Media Caption', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_select( 'tumblr_caption', $this->captions ) . '</td>',

				$this->ngfb->util->th( 'Caption Length', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_input( 'tumblr_cap_len', 'short' ) . ' Characters or less</td>',

				$this->ngfb->util->th( 'Link Description', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_input( 'tumblr_desc_len', 'short' ) . ' Characters or less</td>',
			);
		}

	}
}

if ( ! class_exists( 'ngfbSocialTumblr' ) && class_exists( 'ngfbSocial' ) ) {

	class ngfbSocialTumblr {

		protected $ngfb;

		public function __construct( &$ngfb_plugin ) {
			$this->ngfb =& $ngfb_plugin;
			$this->ngfb->debug->mark();
		}

		public function get_html( $atts = array() ) {
			global $post; 
			$html = '';
			$query = '';
			$use_post = empty( $atts['is_widget'] ) || is_singular() ? true : false;
			if ( empty( $atts['url'] ) ) $atts['url'] = $this->ngfb->util->get_sharing_url( 'notrack', null, $use_post );
			if ( empty( $atts['tumblr_button_style'] ) ) $atts['tumblr_button_style'] = $this->ngfb->options['tumblr_button_style'];
			if ( empty( $atts['size'] ) ) $atts['size'] = $this->ngfb->options['tumblr_img_size'];

			// only use featured image if 'tumblr_photo' option allows it
			if ( empty( $atts['photo'] ) && $this->ngfb->options['tumblr_photo'] ) {
				if ( empty( $atts['pid'] ) ) {
					// allow on index pages only if in content (not a widget)
					if ( ! empty( $post ) && $use_post == true ) {
						$pid = $this->ngfb->meta->get_options( $post->ID, 'og_img_id' );
						$pre = $this->ngfb->meta->get_options( $post->ID, 'og_img_id_pre' );
						if ( ! empty( $pid ) )
							$atts['pid'] = $pre == 'ngg' ? 'ngg-' . $pid : $pid;
						else {
							if ( $this->ngfb->is_avail['postthumb'] == true && has_post_thumbnail( $post->ID ) )
								$atts['pid'] = get_post_thumbnail_id( $post->ID );
							else $atts['pid'] = $this->ngfb->media->get_first_attached_image_id( $post->ID );
						}
					}
				}
				if ( ! empty( $atts['pid'] ) ) {
					// if the post thumbnail id has the form 'ngg-' then it's a NextGEN image
					if ( is_string( $atts['pid'] ) && substr( $atts['pid'], 0, 4 ) == 'ngg-' ) {
						$this->ngfb->debug->log( 'calling ngfb->media->ngg->get_image_src("' . 
							$atts['pid'] . '", "' . $atts['size'] . '", false)' );
						list( $atts['photo'], $atts['width'], $atts['height'], 
							$atts['cropped'] ) = $this->ngfb->media->ngg->get_image_src( $atts['pid'], $atts['size'], false );
					} else {
						$this->ngfb->debug->log( 'calling ngfb->media->get_attachment_image_src("' . 
							$atts['pid'] . '", "' . $atts['size'] . '", false)' );
						list( $atts['photo'], $atts['width'], $atts['height'],
							$atts['cropped'] ) = $this->ngfb->media->get_attachment_image_src( $atts['pid'], $atts['size'], false );
					}
				}
			}

			// check for custom or embedded videos
			if ( empty( $atts['photo'] ) && empty( $atts['embed'] ) ) {
				// allow on index pages only if in content (not a widget)
				if ( ! empty( $post ) && $use_post == true ) {
					$atts['embed'] = $this->ngfb->meta->get_options( $post->ID, 'og_vid_url' );
					if ( empty( $atts['embed'] ) ) {
						$videos = array();
						$videos = $this->ngfb->media->get_content_videos( 1, false );	// get the first video, if any
						if ( ! empty( $videos[0]['og:video'] ) ) 
							$atts['embed'] = $videos[0]['og:video'];
					}
				}
			}

			// if no image or video, then check for a 'quote'
			if ( empty( $atts['photo'] ) && empty( $atts['embed'] ) && empty( $atts['quote'] ) ) {
				// allow on index pages only if in content (not a widget)
				if ( $use_post == true ) {
					if ( ! empty( $post ) && get_post_format( $post->ID ) == 'quote' ) 
						$atts['quote'] = $this->ngfb->webpage->get_quote();
				}
			}

			// we only need the caption, title, or description for some types of shares
			if ( ! empty( $atts['photo'] ) || ! empty( $atts['embed'] ) ) {
				// check for custom image or video caption
				if ( empty( $atts['caption'] ) && $use_post == true ) 
					$atts['caption'] = $this->ngfb->meta->get_options( $post->ID, 
						( ! empty( $atts['photo'] ) ? 'tumblr_img_desc' : 'tumblr_vid_desc' ) );
				if ( empty( $atts['caption'] ) ) 
					$atts['caption'] = $this->ngfb->webpage->get_caption( $this->ngfb->options['tumblr_caption'], 
						$this->ngfb->options['tumblr_cap_len'], $use_post );
			} else {
				if ( empty( $atts['title'] ) ) 
					$atts['title'] = $this->ngfb->webpage->get_title( null, null, $use_post);
				if ( empty( $atts['description'] ) ) 
					$atts['description'] = $this->ngfb->webpage->get_description( $this->ngfb->options['tumblr_desc_len'], '...', $use_post );
			}

			// define the button, based on what we have
			if ( ! empty( $atts['photo'] ) ) {
				$query .= 'photo?source='. urlencode( $atts['photo'] );
				$query .= '&amp;clickthru=' . urlencode( $atts['url'] );
				$query .= '&amp;caption=' . urlencode( $this->ngfb->util->decode( $atts['caption'] ) );
			} elseif ( ! empty( $atts['embed'] ) ) {
				$query .= 'video?embed=' . urlencode( $atts['embed'] );
				$query .= '&amp;caption=' . urlencode( $this->ngfb->util->decode( $atts['caption'] ) );
			} elseif ( ! empty( $atts['quote'] ) ) {
				$query .= 'quote?quote=' . urlencode( $atts['quote'] );
				$query .= '&amp;source=' . urlencode( $this->ngfb->util->decode( $atts['title'] ) );
			} elseif ( ! empty( $atts['url'] ) ) {
				$query .= 'link?url=' . urlencode( $atts['url'] );
				$query .= '&amp;name=' . urlencode( $this->ngfb->util->decode( $atts['title'] ) );
				$query .= '&amp;description=' . urlencode( $this->ngfb->util->decode( $atts['description'] ) );
			}
			if ( empty( $query ) ) return;

			$html = '<!-- Tumblr Button --><div ' . $this->ngfb->social->get_css( 'tumblr', $atts ) . '><a href="http://www.tumblr.com/share/'. $query . '" title="Share on Tumblr"><img border="0" alt="Share on Tumblr" src="' . $this->ngfb->util->get_cache_url( 'http://platform.tumblr.com/v1/' . $atts['tumblr_button_style'] . '.png' ) . '" /></a></div>';
			$this->ngfb->debug->log( 'returning html (' . strlen( $html ) . ' chars)' );
			return $html;
		}

		// the tumblr host does not have a valid SSL cert, and it's javascript does not work in async mode
		public function get_js( $pos = 'id' ) {
			return '<script type="text/javascript" id="tumblr-script-' . $pos . '"
				src="' . $this->ngfb->util->get_cache_url( 'http://platform.tumblr.com/v1/share.js' ) . '"></script>' . "\n";
		}
		
	}

}
?>
