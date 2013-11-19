<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'Sorry, you cannot call this webpage directly.' );

if ( ! class_exists( 'ngfbSettingsLinkedIn' ) && class_exists( 'ngfbSettingsSocialSharing' ) ) {

	class ngfbSettingsLinkedIn extends ngfbSettingsSocialSharing {

		protected $ngfb;

		public function __construct( &$ngfb_plugin ) {
			$this->ngfb =& $ngfb_plugin;
			$this->ngfb->debug->mark();
		}

		public function get_rows() {
			return array(
				$this->ngfb->util->th( 'Add Button to', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_checkbox( 'linkedin_on_the_content' ) . ' the Content and / or ' . 
				$this->ngfb->admin->form->get_checkbox( 'linkedin_on_the_excerpt' ) . ' the Excerpt Text</td>',

				$this->ngfb->util->th( 'Preferred Order', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_select( 'linkedin_order', range( 1, count( $this->ngfb->social_prefix ) ), 'short' ) . '</td>',

				$this->ngfb->util->th( 'JavaScript in', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_select( 'linkedin_js_loc', $this->js_locations ) . '</td>',

				$this->ngfb->util->th( 'Counter Mode', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_select( 'linkedin_counter', 
					array( 
						'none' => '',
						'right' => 'Horizontal',
						'top' => 'Vertical',
					)
				) . '</td>',

				$this->ngfb->util->th( 'Zero in Counter', 'short' ) . '<td>' . 
				$this->ngfb->admin->form->get_checkbox( 'linkedin_showzero' ) . '</td>',
			);
		}

	}
}

if ( ! class_exists( 'ngfbSocialLinkedIn' ) && class_exists( 'ngfbSocial' ) ) {

	class ngfbSocialLinkedIn {

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
			$html = '<!-- LinkedIn Button --><div ' . $this->ngfb->social->get_css( 'linkedin', $atts ) . '><script type="IN/Share" data-url="' . $atts['url'] . '"';

			if ( ! empty( $this->ngfb->options['linkedin_counter'] ) ) 
				$html .= ' data-counter="' . $this->ngfb->options['linkedin_counter'] . '"';

			if ( ! empty( $this->ngfb->options['linkedin_showzero'] ) ) 
				$html .= ' data-showzero="true"';

			$html .= '></script></div>';
			$this->ngfb->debug->log( 'returning html (' . strlen( $html ) . ' chars)' );
			return $html;
		}
		
		public function get_js( $pos = 'id' ) {
			$prot = empty( $_SERVER['HTTPS'] ) ? 'http://' : 'https://';
			return  '<script type="text/javascript" id="linkedin-script-' . $pos . '">
				ngfb_header_js( "linkedin-script-' . $pos . '", "' . $this->ngfb->util->get_cache_url( $prot . 'platform.linkedin.com/in.js' ) . '" );
			</script>' . "\n";
		}

	}

}
?>
