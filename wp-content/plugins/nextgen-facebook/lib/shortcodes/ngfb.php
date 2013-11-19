<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'Sorry, you cannot call this webpage directly.' );

if ( ! class_exists( 'ngfbShortCodeNGFB' ) ) {

	class ngfbShortCodeNgfb {

		private $ngfb;
		private $name = 'ngfb';

		public function __construct( &$ngfb_plugin ) {
			$this->ngfb =& $ngfb_plugin;
			$this->ngfb->debug->mark();
			$this->add();
		}

		public function add() {
			if ( ! empty( $this->ngfb->options[$this->name . '_enable_shortcode'] ) ) {
        			add_shortcode( $this->name, array( &$this, 'shortcode' ) );
				$this->ngfb->debug->log( '[' . $this->name . '] shortcode added' );
			}
		}

		public function remove() {
			if ( ! empty( $this->ngfb->options[$this->name . '_enable_shortcode'] ) ) {
				remove_shortcode( $this->name );
				$this->ngfb->debug->log( '[' . $this->name . '] shortcode removed' );
			}
		}

		public function shortcode( $atts, $content = null ) { 

			$atts = apply_filters( 'ngfb_shortcode', $atts, $content );

			global $post;
			$html = '';

			$atts['url'] = empty( $atts['url'] ) ? $this->ngfb->util->get_sharing_url( 'notrack', null, true ) : $atts['url'];
			$atts['css_id'] = empty( $atts['css_id'] ) && ! empty( $post->ID ) ? 'shortcode-post-' . $post->ID : $atts['css_id'];
			$atts['css_class'] = empty( $atts['css_class'] ) ? 'button' : $atts['css_class'];

			if ( ! empty( $atts['buttons'] ) ) {
				$keys = implode( '|', array_keys( $atts ) );
				$vals = preg_replace( '/[, ]+/', '_', implode( '|', array_values( $atts ) ) );
				$cache_salt = __METHOD__.'(lang:'.get_locale().'_post:'.$post->ID.'_keys:'.$keys. '_vals:'.$vals.')';
				$cache_id = 'ngfb_' . md5( $cache_salt );
				$cache_type = 'object cache';
				$html = get_transient( $cache_id );
				$this->ngfb->debug->log( $cache_type . ': shortcode transient id salt "' . $cache_salt . '"' );

				if ( $html !== false ) {
					$this->ngfb->debug->log( $cache_type . ': html retrieved from transient for id "' . $cache_id . '"' );
				} else {
					if ( ! empty( $atts['buttons'] ) && $this->ngfb->social->is_disabled() == false ) {
						$ids = array_map( 'trim', explode( ',', $atts['buttons'] ) );
						unset ( $atts['buttons'] );

						$html .= "\n<!-- " . $this->ngfb->fullname . " shortcode BEGIN -->\n" .
							$this->ngfb->social->get_js( 'pre-shortcode', $ids ) .
							"<div class=\"" . $this->ngfb->acronym . "-shortcode-buttons\">\n" . 
							$this->ngfb->social->get_html( $ids, $atts ) . "</div>\n" .
							$this->ngfb->social->get_js( 'post-shortcode', $ids ) .
							"<!-- " . $this->ngfb->fullname . " shortcode END -->\n";
					}

					set_transient( $cache_id, $html, $this->ngfb->cache->object_expire );
					$this->ngfb->debug->log( $cache_type . ': html saved to transient for id "' . 
						$cache_id . '" (' . $this->ngfb->cache->object_expire . ' seconds)');
				}
			}
			return $this->ngfb->debug->get_html() . $html;
		}
	}
}

?>
