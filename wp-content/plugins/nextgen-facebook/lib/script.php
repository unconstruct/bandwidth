<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'Sorry, you cannot call this webpage directly.' );

if ( ! class_exists( 'ngfbScript' ) ) {

	class ngfbScript {

		private $ngfb;

		public function __construct( &$ngfb_plugin ) {
			$this->ngfb =& $ngfb_plugin;
			$this->ngfb->debug->mark();

			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );
		}

		public function admin_enqueue_scripts( $hook ) {
			wp_register_script( 'jquery-qtip', NGFB_URLPATH . 'js/jquery-qtip.min.js', array( 'jquery' ), '1.0.0-RC3', true );
			wp_register_script( $this->ngfb->acronym . '_tooltips', NGFB_URLPATH . 'js/jquery-tooltips.min.js', array( 'jquery' ), $this->ngfb->version, true );
			wp_register_script( $this->ngfb->acronym . '_postmeta', NGFB_URLPATH . 'js/jquery-postmeta.min.js', array( 'jquery' ), $this->ngfb->version, true );

			// don't load our javascript where we don't need it
			switch ( $hook ) {
				case 'post.php' :
				case 'post-new.php' :
				case ( preg_match( '/_page_' . $this->ngfb->acronym . '-/', $hook ) ? true : false ) :
					wp_enqueue_script( 'jquery' );
					wp_enqueue_script( 'jquery-qtip' );
					wp_enqueue_script( $this->ngfb->acronym . '_tooltips' );
					wp_enqueue_script( $this->ngfb->acronym . '_postmeta' );
					break;
			}
		}

	}
}

?>
