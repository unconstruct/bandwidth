<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'Sorry, you cannot call this webpage directly.' );

if ( ! class_exists( 'ngfbWidgetSocialSharing' ) && class_exists( 'WP_Widget' ) ) {

	class ngfbWidgetSocialSharing extends WP_Widget {

		public static $fullname = 'NGFB Social Sharing';

		public function __construct() {
			global $ngfb;
			$widget_ops = array( 
				'classname' => 'ngfb-widget-buttons',
				'description' => 'The ' . $ngfb->fullname . ' social sharing buttons widget.'
			);
			$this->WP_Widget( 'ngfb-widget-buttons', self::$fullname, $widget_ops );
		}
	
		public function widget( $args, $instance ) {
			global $ngfb;
			if ( is_object( $ngfb->social ) && 
				$ngfb->social->is_disabled() ) return;
			extract( $args );
			$sharing_url = $ngfb->util->get_sharing_url( 'notrack' );
			$cache_salt = __METHOD__.'(lang:'.get_locale().'_widget:'.$this->id.'_sharing_url:'.$sharing_url.')';
			$cache_id = 'ngfb_' . md5( $cache_salt );
			$cache_type = 'object cache';
			$widget_html = get_transient( $cache_id );
			$ngfb->debug->log( $cache_type . ': widget_html transient id salt "' . $cache_salt . '"' );

			if ( $widget_html !== false ) {
				$ngfb->debug->log( $cache_type . ': widget_html retrieved from transient for id "' . $cache_id . '"' );
			} else {
				$widget_html = '';
				$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
				$sorted_ids = array();
				foreach ( $ngfb->social_prefix as $id => $prefix )
					if ( (int) $instance[$id] )
						$sorted_ids[$ngfb->options[$prefix.'_order'] . '-' . $id] = $id;
				ksort( $sorted_ids );
	
				$widget_html .= "\n<!-- " . $ngfb->fullname . " widget BEGIN -->\n";
				$widget_html .= $before_widget . "\n";
				if ( $title ) $widget_html .= $before_title . $title . $after_title . "\n";
				$widget_html .= $ngfb->social->get_html( $sorted_ids, array( 'is_widget' => 1, 'css_id' => $args['widget_id'] ) );
				$widget_html .= $after_widget . "\n";
				$widget_html .= "<!-- " . $ngfb->fullname . " widget END -->\n";
	
				set_transient( $cache_id, $widget_html, $ngfb->cache->object_expire );
				$ngfb->debug->log( $cache_type . ': widget_html saved to transient for id "' . $cache_id . '" (' . $ngfb->cache->object_expire . ' seconds)');
			}
			$ngfb->debug->show_html();
			echo $widget_html;
		}
	
		public function update( $new_instance, $old_instance ) {
			global $ngfb;
			$instance = $old_instance;
			$instance['title'] = strip_tags( $new_instance['title'] );
			foreach ( $ngfb->website_libs as $id => $name ) {
				$instance[$id] = empty( $new_instance[$id] ) ? 0 : 1;
			}
			unset( $name, $id );
			return $instance;
		}
	
		public function form( $instance ) {
			global $ngfb;
			$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : 'Share It';
			echo "\n", '<p><label for="', $this->get_field_id( 'title' ), '">Title (Leave Blank for No Title):</label>',
				'<input class="widefat" id="', $this->get_field_id( 'title' ), 
					'" name="', $this->get_field_name( 'title' ), 
					'" type="text" value="', $title, '" /></p>', "\n";
	
			foreach ( $ngfb->website_libs as $id => $name ) {
				echo '<p><label for="', $this->get_field_id( $id ), '">', 
					'<input id="', $this->get_field_id( $id ), 
					'" name="', $this->get_field_name( $id ), 
					'" value="1" type="checkbox" ';
				if ( ! empty( $instance[$id] ) )
					echo checked( 1 , $instance[$id] );
				echo ' /> ', $name;
				switch ( $id ) {
					case 'pinterest' :
						echo ' (not added on indexes)';
						break;
					case 'tumblr' :
						echo ' (shares link on indexes)';
						break;
				}
				echo '</label></p>', "\n";
			}
			unset( $id, $name );
		}
	}
	
	add_action( 'widgets_init', create_function( '', 'return register_widget( "ngfbWidgetSocialSharing" );' ) );
}

?>
