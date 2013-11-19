<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'Sorry, you cannot call this webpage directly.' );

if ( ! class_exists( 'ngfbStyle' ) ) {

	class ngfbStyle {

		public $social_css_min_file;
		public $social_css_min_url;

		private $ngfb;

		public function __construct( &$ngfb_plugin ) {
			$this->ngfb =& $ngfb_plugin;
			$this->ngfb->debug->mark();

			$this->social_css_min_url = NGFB_URLPATH . 'cache/' . $this->ngfb->acronym . '-social-styles.min.css';
			$this->social_css_min_file = NGFB_PLUGINDIR . 'cache/' . $this->ngfb->acronym . '-social-styles.min.css';

			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_styles' ) );
			add_action( 'wp_enqueue_scripts', array( &$this, 'wp_enqueue_styles' ) );
		}

		public function admin_enqueue_styles( $hook ) {
			wp_register_style( $this->ngfb->acronym . '_settings_pages', NGFB_URLPATH . 'css/settings-pages.min.css', false, $this->ngfb->version );
			wp_register_style( $this->ngfb->acronym . '_table_settings', NGFB_URLPATH . 'css/table-settings.min.css', false, $this->ngfb->version );
			wp_register_style( $this->ngfb->acronym . '_metabox_tabs', NGFB_URLPATH . 'css/metabox-tabs.min.css', false, $this->ngfb->version );

			switch ( $hook ) {
				case 'post.php' :
				case 'post-new.php' :
					wp_enqueue_style( $this->ngfb->acronym . '_table_settings' );
					wp_enqueue_style( $this->ngfb->acronym . '_metabox_tabs' );
					break;
				case ( preg_match( '/_page_' . $this->ngfb->acronym . '-/', $hook ) ? true : false ) :
					wp_enqueue_style( $this->ngfb->acronym . '_settings_pages' );
					wp_enqueue_style( $this->ngfb->acronym . '_table_settings' );
					wp_enqueue_style( $this->ngfb->acronym . '_metabox_tabs' );
					break;
			}
		}

		public function wp_enqueue_styles( $hook ) {
			if ( ! empty( $this->ngfb->options['buttons_link_css'] ) ) {
				wp_register_style( $this->ngfb->acronym . '_social_buttons', $this->social_css_min_url, false, $this->ngfb->version );
				if ( ! file_exists( $this->social_css_min_file ) ) 
					$this->update_social( $this->ngfb->options );
				$this->ngfb->debug->log( 'wp_enqueue_style = ' . $this->ngfb->acronym . '_social_buttons' );
				wp_enqueue_style( $this->ngfb->acronym . '_social_buttons' );
			}
		}

		public function update_social( &$opts ) {
			if ( ! $fh = @fopen( $this->social_css_min_file, 'wb' ) )
				add_settings_error( NGFB_OPTIONS_NAME, 'notarray', 
					'<b>' . $this->ngfb->acronym_uc . '</b> : Error opening 
						<u>' . $this->social_css_min_file . '</u> for writing.', 'error' );
			else {
				$css_data = '';
				foreach ( $this->ngfb->css_names as $css_id => $css_name )
					$css_data .= $opts['buttons_css_' . $css_id];
				require_once ( NGFB_PLUGINDIR . 'lib/ext/compressor.php' );
				$css_data = ngfbMinifyCssCompressor::process( $css_data );
				fwrite( $fh, $css_data );
				fclose( $fh );
				$this->ngfb->debug->log( 'updated css file ' . $this->social_css_min_file );
			}
		}

		public function unlink_social() {
			if ( file_exists( $this->social_css_min_file ) ) {
				if ( ! @unlink( $this->social_css_min_file ) )
					add_settings_error( NGFB_OPTIONS_NAME, 'cssnotrm', 
						'<b>' . $this->ngfb->acronym_uc . '</b> : Error removing minimized stylesheet. 
							Does the web server have sufficient privileges?', 'error' );
			}
		}

	}
}

?>
