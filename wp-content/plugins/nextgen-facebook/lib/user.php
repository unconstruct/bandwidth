<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'Sorry, you cannot call this webpage directly.' );

if ( ! class_exists( 'ngfbUser' ) ) {

	class ngfbUser {

		private $ngfb;		// ngfbPlugin

		public function __construct( &$ngfb_plugin ) {
			$this->ngfb =& $ngfb_plugin;
			$this->ngfb->debug->mark();

			add_action( 'edit_user_profile_update', array( &$this, 'sanitize_contact_methods' ) );
			add_action( 'personal_options_update', array( &$this, 'sanitize_contact_methods' ) );

			add_filter( 'user_contactmethods', array( &$this, 'add_contact_methods' ), 20, 1 );
		}

		public function add_contact_methods( $fields = array() ) { 
			foreach ( preg_split( '/ *, */', NGFB_CONTACT_FIELDS ) as $field_list ) {
				$field_info = preg_split( '/ *: */', $field_list );
				$fields[$field_info[0]] = $field_info[1];
			}
			ksort( $fields, SORT_STRING );
			return $fields;
		}

		public function sanitize_contact_methods( $user_id ) {
			if ( current_user_can( 'edit_user', $user_id ) ) {
				foreach ( preg_split( '/ *, */', NGFB_CONTACT_FIELDS ) as $field_list ) {
					$field_info = preg_split( '/ *: */', $field_list );
					$field_id = $field_info[0];
					$field_val = wp_filter_nohtml_kses( $_POST[$field_id] );
					if ( ! empty( $field_val ) ) {
						switch ( $field_id ) {
							case NGFB_TWITTER_FIELD_ID :
								$field_val = substr( preg_replace( '/[^a-z0-9]/', '', 
									strtolower( $field_val ) ), 0, 15 );
								if ( ! empty( $field_val ) )
									$field_val = '@' . $field_val;
								break;
							default :
								if ( strpos( $field_val, '://' ) === false )
									$field_val = '';
								break;
						}
					}
					$_POST[$field_id] = $field_val;
				}
			}
		}

		// called from head and opengraph classes
		public function get_author_url( $author_id, $field_id = 'url' ) {
			switch ( $field_id ) {
				case 'none' :
					break;
				case 'index' :
					$url = get_author_posts_url( $author_id );
					break;
				default :
					$url = get_the_author_meta( $field_id, $author_id );	// since wp 2.8.0 
					// if empty or not a url, then fallback to the author index page
					if ( $this->ngfb->options['og_author_fallback'] && ( empty( $url ) || ! preg_match( '/:\/\//', $url ) ) )
						$url = get_author_posts_url( $author_id );
					break;
			}
			return $url;
		}

		public function reset_metaboxes( $page, $box_ids = array(), $force = false ) {
			$user_id = get_current_user_id();				// since wp 3.0

			if ( $force == true )
				foreach ( array( 'meta-box-order', 'metaboxhidden', 'closedpostboxes' ) as $meta_name )
					delete_user_option( $user_id, $meta_name . '_' . $page, true );

			$meta_key = 'closedpostboxes_' . $page;
			$option_arr = get_user_option( $meta_key, $user_id );	// since wp 2.0.0 

			if ( ! is_array( $option_arr ) )
				$option_arr = array();

			if ( empty( $option_arr ) )
				foreach ( $box_ids as $id ) 
					$option_arr[] = $page . '_' . $id;

			update_user_option( $user_id, $meta_key, array_unique( $option_arr ), true );	// since wp 2.0
		}

	}

}
?>
