<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'Sorry, you cannot call this webpage directly.' );

if ( ! class_exists( 'ngfbSettingsGooglePlus' ) && class_exists( 'ngfbSettingsSocialSharing' ) ) {

	class ngfbSettingsGooglePlus extends ngfbSettingsSocialSharing {

		protected $ngfb;

		public function __construct( &$ngfb_plugin ) {
			$this->ngfb =& $ngfb_plugin;
			$this->ngfb->debug->mark();
		}

		public function get_rows() {
			return array(
				$this->ngfb->util->th( 'Add Button to', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_checkbox( 'gp_on_the_content' ) . ' the Content and / or ' . 
				$this->ngfb->admin->form->get_checkbox( 'gp_on_the_excerpt' ) . ' the Excerpt Text</td>',

				$this->ngfb->util->th( 'Preferred Order', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_select( 'gp_order', range( 1, count( $this->ngfb->social_prefix ) ), 'short' ) . '</td>',

				$this->ngfb->util->th( 'JavaScript in', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_select( 'gp_js_loc', $this->js_locations ) . '</td>',

				$this->ngfb->util->th( 'Default Language', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_select( 'gp_lang', $this->ngfb->util->get_lang( 'gplus' ) ) . '</td>',

				$this->ngfb->util->th( 'Button Type', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_select( 'gp_action', 
					array( 
						'plusone' => 'G +1', 
						'share' => 'G+ Share',
					) 
				) . '</td>',

				$this->ngfb->util->th( 'Button Size', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_select( 'gp_size', 
					array( 
						'small' => 'Small [ 15px ]',
						'medium' => 'Medium [ 20px ]',
						'standard' => 'Standard [ 24px ]',
						'tall' => 'Tall [ 60px ]',
					) 
				) . '</td>',

				$this->ngfb->util->th( 'Annotation', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_select( 'gp_annotation', 
					array( 
						'none' => '',
						'inline' => 'Inline',
						'bubble' => 'Bubble',
						'vertical-bubble' => 'Vertical Bubble',
					)
				) . '</td>',
			);
		}

	}
}

if ( ! class_exists( 'ngfbSocialGooglePlus' ) && class_exists( 'ngfbSocial' ) ) {

	class ngfbSocialGooglePlus {

		protected $ngfb;

		public function __construct( &$ngfb_plugin ) {
			$this->ngfb =& $ngfb_plugin;
			$this->ngfb->debug->mark();
		}

		public function get_html( $atts = array() ) {
			global $post; 
			$html = '';
			$use_post = empty( $atts['is_widget'] ) || is_singular() ? true : false;
			if ( empty( $atts['url'] ) ) $atts['url'] = $this->ngfb->util->get_sharing_url( 'notrack', null, $use_post );
			$gp_class = $this->ngfb->options['gp_action'] == 'share' ? 'class="g-plus" data-action="share"' : 'class="g-plusone"';
			$html = '<!-- GooglePlus Button --><div ' . $this->ngfb->social->get_css( 'gplus', $atts, 'g-plusone-button' ) . '><span '. $gp_class . ' data-size="' . $this->ngfb->options['gp_size'] . '" data-annotation="' . $this->ngfb->options['gp_annotation'] . '" data-href="' . $atts['url'] . '"></span></div>';
			$this->ngfb->debug->log( 'returning html (' . strlen( $html ) . ' chars)' );
			return $html;
		}
		
		public function get_js( $pos = 'id' ) {
			$prot = empty( $_SERVER['HTTPS'] ) ? 'http://' : 'https://';
			return '<script type="text/javascript" id="gplus-script-' . $pos . '">
				ngfb_header_js( "gplus-script-' . $pos . '", "' . $this->ngfb->util->get_cache_url( $prot . 'apis.google.com/js/plusone.js' ) . '" );
			</script>' . "\n";
		}
		
	}

}
?>
