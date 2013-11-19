<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'Sorry, you cannot call this webpage directly.' );

if ( ! class_exists( 'ngfbDebug' ) ) {

	class ngfbDebug {

		private $longname = '';
		private $shortname = '';
		private $active = false;	// true if at least one subsys is true
		private $buffer = array();	// accumulate text strings going to html output
		private $subsys = array();	// associative array to enable various outputs 

		public function __construct( $longname = '', $shortname = '', 
			$subsys = array( 'html' => false, 'wp' => false ) ) {

			$this->longname = $longname;
			$this->shortname = $shortname;
			$this->subsys = $subsys;
			$this->set_active();
			$this->mark();
		}

		public function is_on( $subsys_name = '' ) {
			if ( ! empty( $subsys_name ) )
				// returns the subsys value, or false if not found
				return array_key_exists( $subsys_name, $this->subsys ) ? 
					$this->subsys[$subsys_name] : false;
			else return $this->active;
		}

		public function switch_on( $subsys_name ) {
			// sets and returns $this->active (which is always true here)
			return $this->switch_to( $subsys_name, true );
		}

		public function switch_off( $subsys_name ) {
			// sets and returns $this->active (which is true, until all subsys are false)
			return $this->switch_to( $subsys_name, false );
		}

		public function mark() {
			$this->log( 'mark', 2 );
		}

		public function log( $input = '', $backtrace = 1 ) {
			if ( $this->active !== true ) return;

			$log_msg = '';
			$stack = debug_backtrace();

			$log_msg .= sprintf( '%-26s:: ', 
				( empty( $stack[$backtrace]['class'] ) ? '' : $stack[$backtrace]['class'] ) );

			$log_msg .= sprintf( '%-24s : ', 
				( empty( $stack[$backtrace]['function'] ) ? '' : $stack[$backtrace]['function'] ) );

			if ( is_array( $input ) || is_object( $input ) )
				$log_msg .= print_r( $input, true );
			else $log_msg .= $input;

			if ( $this->subsys['html'] == true )
				$this->buffer[] = $log_msg;

			if ( $this->subsys['wp'] == true )
				error_log( $this->shortname . ' ' . $log_msg );
		}

		public function show_html( $data = null, $title = null ) {
			echo $this->get_html( $data, $title, 2 );
		}

		public function get_html( $data = null, $title = null, $backtrace = 1 ) {
			$html = '';
			$from = '';
			if ( $this->active ) {
				$html .= "<!-- " . $this->longname . " debug";
				$stack = debug_backtrace();
				if ( ! empty( $stack[$backtrace]['class'] ) ) 
					$from .= $stack[$backtrace]['class'] . '::';
				if ( ! empty( $stack[$backtrace]['function'] ) )
					$from .= $stack[$backtrace]['function'];
				if ( empty( $data ) ) {
					$this->log( 'truncating debug log' );
					$data = $this->buffer;
					$this->buffer = array();
				}
				if ( ! empty( $from ) ) $html .= ' from ' . $from . '()';
				if ( ! empty( $title ) ) $html .= ' ' . $title;
				if ( ! empty( $data ) ) {
					$html .= ' : ';
					if ( is_array( $data ) ) {
						$html .= "\n";
						$is_assoc = $this->is_assoc( $data );
						if ( $is_assoc ) ksort( $data );
						foreach ( $data as $key => $val ) 
							$html .= $is_assoc ? "\t$key = $val\n" : "\t$val\n";
						unset ( $key, $val );
					} else {
						if ( preg_match( '/^Array/', $data ) ) $html .= "\n";	// check for print_r() output
						$html .= $data;
					}
				}
				$html .= ' -->' . "\n";
			}
			return $html;
		}

		private function switch_to( $subsys_name, $switch_to ) {
			if ( ! empty( $subsys_name ) )
				$this->subsys[$subsys_name] = $switch_to;
			return $this->set_active();
		}

		private function set_active() {
			$this->active = in_array( true, $this->subsys, true ) ? true : false;
			return $this->active;
		}

		private function is_assoc( $arr ) {
			if ( ! is_array( $arr ) ) return false;
			return is_numeric( implode( array_keys( $arr ) ) ) ? false : true;
		}


	}
}

?>
