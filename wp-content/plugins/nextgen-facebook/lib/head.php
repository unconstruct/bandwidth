<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'Sorry, you cannot call this webpage directly.' );

if ( ! class_exists( 'ngfbHead' ) ) {

	class ngfbHead {

		public $og;

		private $ngfb;

		public function __construct( &$ngfb_plugin ) {
			$this->ngfb =& $ngfb_plugin;
			$this->ngfb->debug->mark();
			$this->og = new ngfbOpenGraph( $ngfb_plugin );

			add_action( 'wp_head', array( &$this, 'add_header' ), NGFB_HEAD_PRIORITY );
		}

		// called by WP wp_head action
		public function add_header() {

			if ( $this->ngfb->debug->is_on() ) {
				$this->ngfb->debug->log( 'is_archive() = ' . ( is_archive() ? 'true' : 'false' ) );
				$this->ngfb->debug->log( 'is_attachment() = ' . ( is_attachment() ? 'true' : 'false' ) );
				$this->ngfb->debug->log( 'is_category() = ' . ( is_category() ? 'true' : 'false' ) );
				$this->ngfb->debug->log( 'is_home() = ' . ( is_home() ? 'true' : 'false' ) );
				$this->ngfb->debug->log( 'is_search() = ' . ( is_search() ? 'true' : 'false' ) );
				$this->ngfb->debug->log( 'is_singular() = ' . ( is_singular() ? 'true' : 'false' ) );
			}

			$this->html( $this->og->get() );

			if ( $this->ngfb->debug->is_on() ) {
				//$defined_constants = get_defined_constants( true );
				//$this->ngfb->debug->show_html( $this->ngfb->util->preg_grep_keys( '/^NGFB_/', $defined_constants['user'] ), 'NGFB Constants' );

				$opts = $this->ngfb->options;
				foreach ( array( 'ngfb_pro_tid', 'ngfb_googl_api_key', ) as $key ) $opts[$key] = '********';

				$this->ngfb->debug->show_html( $this->ngfb->is_avail, 'Available Features' );
				$this->ngfb->debug->show_html( null, 'Debug Log' );
				$this->ngfb->debug->show_html( $opts, 'NGFB Settings' );
			}
		}

		// called from add_header() and the work/header.php template
		public function html( $meta_tags = array() ) {
			global $post;
			$author_url = '';
		
			echo "\n<!-- ", $this->ngfb->fullname, " meta tags BEGIN -->\n";

			// show the array structure before the html block
			$this->ngfb->debug->show_html( print_r( $meta_tags, true ), 'Open Graph Array' );
			$this->ngfb->debug->show_html( print_r( $this->ngfb->util->get_urls_found(), true ), 'Media URLs Found' );

			echo '<meta name="generator" content="', $this->ngfb->fullname, ' ', $this->ngfb->version, '" />', "\n";

			/*
			 * Meta Tags for Google
			 */
			$link_rel = array();
			if ( array_key_exists( 'link:publisher', $meta_tags ) ) {
				$link_rel['publisher'] = $meta_tags['link:publisher'];
				unset ( $meta_tags['link:publisher'] );
			} elseif ( ! empty( $this->ngfb->options['link_publisher_url'] ) )
				$link_rel['publisher'] = $this->ngfb->options['link_publisher_url'];

			if ( array_key_exists( 'link:author', $meta_tags ) ) {
				$link_rel['author'] = $meta_tags['link:author'];
				unset ( $meta_tags['link:author'] );
			} else {
				if ( is_singular() ) {
					if ( ! empty( $post ) && $post->post_author )
						$link_rel['author'] = $this->ngfb->user->get_author_url( $post->post_author, 
							$this->ngfb->options['link_author_field'] );
					elseif ( ! empty( $this->ngfb->options['link_def_author_id'] ) )
						$link_rel['author'] = $this->ngfb->user->get_author_url( $this->ngfb->options['link_def_author_id'], 
							$this->ngfb->options['link_author_field'] );
				// check for default author info on indexes and searches
				} elseif ( ( ! is_singular() && ! is_search() && ! empty( $this->ngfb->options['link_def_author_on_index'] ) && ! empty( $this->ngfb->options['link_def_author_id'] ) )
					|| ( is_search() && ! empty( $this->ngfb->options['link_def_author_on_search'] ) && ! empty( $this->ngfb->options['link_def_author_id'] ) ) ) {

					$link_rel['author'] = $this->ngfb->user->get_author_url( $this->ngfb->options['link_def_author_id'], 
						$this->ngfb->options['link_author_field'] );
				}
			}
			$link_rel = apply_filters( 'ngfb_link_rel', $link_rel );
			foreach ( $link_rel as $key => $val )
				if ( ! empty( $val ) )
					echo '<link rel="', $key, '" href="', $val, '" />', "\n";

			if ( ! empty( $this->ngfb->options['inc_description'] ) ) {
				if ( ! array_key_exists( 'description', $meta_tags ) ) {
					if ( is_singular() && ! empty( $post ) )
						$meta_tags['description'] = $this->ngfb->meta->get_options( $post->ID, 'meta_desc' );
					if ( empty( $meta_tags['description'] ) )
						$meta_tags['description'] = $this->ngfb->webpage->get_description( $this->ngfb->options['meta_desc_len'], '...' );
				}
				if ( ! empty( $meta_tags['description'] ) ) {
					// get_description is already decoded and html clean, so just encode html entities
					$charset = get_bloginfo( 'charset' );
					$meta_tags['description'] = htmlentities( $meta_tags['description'], ENT_QUOTES, $charset, false );
				}
			}

			/*
			 * Print the Multi-Dimensional Array as HTML
			 */
			$this->ngfb->debug->log( count( $meta_tags ) . ' meta_tags to process' );
			foreach ( $meta_tags as $first_name => $first_val ) {			// 1st-dimension array (associative)
				if ( is_array( $first_val ) ) {
					if ( empty( $first_val ) ) {
						echo $this->get_meta_html( $first_name );	// possibly show an empty tag (depends on og_empty_tags value)
					} else {
						//$this->ngfb->debug->log( 'foreach 1st-dimension element: ' . $first_name . ' (array)' );
						foreach ( $first_val as $second_num => $second_val ) {			// 2nd-dimension array
							if ( $this->ngfb->util->is_assoc( $second_val ) ) {
								//$this->ngfb->debug->log( 'foreach 2nd-dimension element: ' . $second_num . ' (array)' );
								ksort( $second_val );
								foreach ( $second_val as $third_name => $third_val ) {	// 3rd-dimension array (associative)
									//$this->ngfb->debug->log( 'formatting 3rd-dimension element: ' . $third_name );
									echo $this->get_meta_html( $third_name, $third_val, $first_name . ':' . ( $second_num + 1 ) );
								}
								unset ( $third_name, $third_val );
							} else {
								//$this->ngfb->debug->log( 'formatting 2nd-dimension element: ' . $second_num );
								echo $this->get_meta_html( $first_name, $second_val, $first_name . ':' . ( $second_num + 1 ) );
							}
						}
						unset ( $second_num, $second_val );
					}
				} else {
					//$this->ngfb->debug->log( 'formatting 1st-dimension element: ' . $first_name );
					echo $this->get_meta_html( $first_name, $first_val );
				}
			}
			unset ( $first_name, $first_val );

			echo "<!-- ", $this->ngfb->fullname, " meta tags END -->\n";
		}

		private function get_meta_html( $name, $val = '', $cmt = '' ) {
			$meta_html = '';
			if ( ! empty( $this->ngfb->options['inc_' . $name] ) ) {
				if ( ! empty( $val ) || ( ! empty( $this->ngfb->options['og_empty_tags'] ) && strpos( $name, 'og:' ) === 0 ) ) {
					$charset = get_bloginfo( 'charset' );
					$val = htmlentities( $this->ngfb->util->cleanup_html_tags( $this->ngfb->util->decode( $val ) ), 
						ENT_QUOTES, $charset, false );
					$this->ngfb->debug->log( 'meta ' . $name . ' = "' . $val . '"' );
					if ( $cmt ) $meta_html .= "<!-- $cmt -->";

					// by default, echo a <meta property="" content=""> html tag
					if ( $name == 'description' || strpos( $name, 'twitter:' ) === 0 ) {
						$meta_html .= '<meta name="' . $name . '" content="' . $val . '" />' . "\n";
					} elseif ( ( $name == 'og:image' || $name == 'og:video' ) && 
						strpos( $val, 'https:' ) === 0 && ! empty( $this->ngfb->options['inc_'.$name] ) ) {

						$non_sec = preg_replace( '/^https:/', 'http:', $val );
						$meta_html .= '<meta property="' . $name . '" content="' . $non_sec . '" />' . "\n";
						// add an additional secure_url meta tag
						if ( $cmt ) $meta_html .= "<!-- $cmt -->";
						$meta_html .= '<meta property="' . $name . ':secure_url" content="' . $val . '" />' . "\n";
					} else {
						$meta_html .= '<meta property="' . $name . '" content="' . $val . '" />' . "\n";
					}
				} else $this->ngfb->debug->log( 'meta ' . $name . ' is empty - skipping' );
			} else $this->ngfb->debug->log( 'meta ' . $name . ' is disabled - skipping' );
			return $meta_html;
		}

	}

}
?>
