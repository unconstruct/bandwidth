<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'Sorry, you cannot call this webpage directly.' );

if ( ! class_exists( 'ngfbSettingsPinterest' ) && class_exists( 'ngfbSettingsSocialSharing' ) ) {

	class ngfbSettingsPinterest extends ngfbSettingsSocialSharing {

		protected $ngfb;

		public function __construct( &$ngfb_plugin ) {
			$this->ngfb =& $ngfb_plugin;
			$this->ngfb->debug->mark();
		}

		public function get_rows() {
			return array(
				$this->ngfb->util->th( 'Add Button to', 'short', null,
				'The Pinterest "Pin It" button appears only on Posts and Pages with a <em>featured</em> or <em>attached</em> image.' ) . '<td>' . 
				$this->ngfb->admin->form->get_checkbox( 'pin_on_the_content' ) . ' the Content and / or ' . 
				$this->ngfb->admin->form->get_checkbox( 'pin_on_the_excerpt' ) . ' the Excerpt Text</td>',

				$this->ngfb->util->th( 'Preferred Order', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_select( 'pin_order', range( 1, count( $this->ngfb->social_prefix ) ), 'short' ) . '</td>',

				$this->ngfb->util->th( 'JavaScript in', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_select( 'pin_js_loc', $this->js_locations ) . '</td>',

				$this->ngfb->util->th( 'Pin Count Layout', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_select( 'pin_count_layout', 
					array( 
						'none' => '',
						'horizontal' => 'Horizontal',
						'vertical' => 'Vertical',
					)
				) . '</td>',

				$this->ngfb->util->th( 'Image Size to Share', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_select_img_size( 'pin_img_size' ) . '</td>',

				$this->ngfb->util->th( 'Image Caption Text', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_select( 'pin_caption', $this->captions ) . '</td>',

				$this->ngfb->util->th( 'Caption Length', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_input( 'pin_cap_len', 'short' ) . ' Characters or less</td>',
			);
		}

	}
}

if ( ! class_exists( 'ngfbSocialPinterest' ) && class_exists( 'ngfbSocial' ) ) {

	class ngfbSocialPinterest {

		protected $ngfb;

		public function __construct( &$ngfb_plugin ) {
			$this->ngfb =& $ngfb_plugin;
			$this->ngfb->debug->mark();
		}

		public function get_html( $atts = array() ) {
			global $post; 
			$html = '';
			$query = '';
			$prot = empty( $_SERVER['HTTPS'] ) ? 'http://' : 'https://';
			$use_post = empty( $atts['is_widget'] ) || is_singular() ? true : false;
			if ( empty( $atts['url'] ) ) $atts['url'] = $this->ngfb->util->get_sharing_url( 'notrack', null, $use_post );
			if ( empty( $atts['size'] ) ) $atts['size'] = $this->ngfb->options['pin_img_size'];
			if ( empty( $atts['photo'] ) ) {
				// get the pid
				if ( empty( $atts['pid'] ) ) {
					// allow on index pages only if in content (not a widget)
					if ( ! empty( $post ) && $use_post == true ) {
						$pid = $this->ngfb->meta->get_options( $post->ID, 'og_img_id' );
						$pre = $this->ngfb->meta->get_options( $post->ID, 'og_img_id_pre' );
						if ( ! empty( $pid ) ) 
							$atts['pid'] = $pre == 'ngg' ? 'ngg-' . $pid : $pid;
						elseif ( $this->ngfb->is_avail['postthumb'] == true && has_post_thumbnail( $post->ID ) )
							$atts['pid'] = get_post_thumbnail_id( $post->ID );
						else $atts['pid'] = $this->ngfb->media->get_first_attached_image_id( $post->ID );
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
			if ( empty( $atts['photo'] ) ) return;

			if ( empty( $atts['pin_count_layout'] ) ) 
				$atts['pin_count_layout'] = $this->ngfb->options['pin_count_layout'];

			if ( empty( $atts['caption'] ) && $use_post == true ) 
				$atts['caption'] = $this->ngfb->meta->get_options( $post->ID, 'pin_desc' );

			if ( empty( $atts['caption'] ) ) 
				$atts['caption'] = $this->ngfb->webpage->get_caption( $this->ngfb->options['pin_caption'], 
					$this->ngfb->options['pin_cap_len'], $use_post );

			$query .= 'url=' . urlencode( $atts['url'] );
			$query .= '&amp;media='. urlencode( $atts['photo'] );
			$query .= '&amp;description=' . urlencode( $this->ngfb->util->decode( $atts['caption'] ) );

			$html = '<!-- Pinterest Button --><div ' . $this->ngfb->social->get_css( 'pinterest', $atts ) . '><a href="' . $prot . 'pinterest.com/pin/create/button/?' . $query . '" class="pin-it-button" count-layout="' . $atts['pin_count_layout'] . '" title="Share on Pinterest"><img border="0" alt="Pin It" src="' . $this->ngfb->util->get_cache_url( $prot . 'assets.pinterest.com/images/PinExt.png' ) . '" /></a></div>';
			$this->ngfb->debug->log( 'returning html (' . strlen( $html ) . ' chars)' );
			return $html;
		}

		public function get_js( $pos = 'id' ) {
			$prot = empty( $_SERVER['HTTPS'] ) ? 'http://' : 'https://';
			return '<script type="text/javascript" id="pinterest-script-' . $pos . '">
				ngfb_header_js( "pinterest-script-' . $pos . '", "' . $this->ngfb->util->get_cache_url( $prot . 'assets.pinterest.com/js/pinit.js' ) . '" );
			</script>' . "\n";
		}
		
	}

}
?>
