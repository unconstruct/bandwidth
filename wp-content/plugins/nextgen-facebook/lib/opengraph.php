<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'Sorry, you cannot call this webpage directly.' );

if ( ! class_exists( 'ngfbOpenGraph' ) ) {

	class ngfbOpenGraph {

		private $ngfb;

		public function __construct( &$ngfb_plugin ) {
			$this->ngfb =& $ngfb_plugin;
			$this->ngfb->debug->mark();

			add_filter( 'language_attributes', array( &$this, 'add_doctype' ) );
		}
	
		public function add_doctype( $out ) {
			return $out . ' xmlns:og="http://ogp.me/ns" xmlns:fb="http://ogp.me/ns/fb"';
		}

		public function get() {
			if ( ( defined( 'DISABLE_NGFB_OPEN_GRAPH' ) && DISABLE_NGFB_OPEN_GRAPH ) 
				|| ( defined( 'NGFB_OPEN_GRAPH_DISABLE' ) && NGFB_OPEN_GRAPH_DISABLE ) ) {
				echo "\n<!-- ", $this->ngfb->fullname, " meta tags DISABLED -->\n\n";
				return array();
			}

			$sharing_url = $this->ngfb->util->get_sharing_url( 'notrack' );
			$cache_salt = __METHOD__.'(lang:'.get_locale().'_sharing_url:'.$sharing_url.')';
			$cache_id = $this->ngfb->acronym . '_' . md5( $cache_salt );
			$cache_type = 'object cache';
			$this->ngfb->debug->log( $cache_type . ': og array transient id salt "' . $cache_salt . '"' );
			$og = get_transient( $cache_id );
			if ( $og !== false ) {
				$this->ngfb->debug->log( $cache_type . ': og array retrieved from transient for id "' . $cache_id . '"' );
				return $og;
			}

			global $post;
			$post_type = '';
			$has_video_image = '';
			$og_max = $this->get_max_nums();
			$og = apply_filters( 'ngfb_og_seed', array() );

			if ( ! array_key_exists( 'fb:admins', $og ) )
				$og['fb:admins'] = $this->ngfb->options['fb_admins'];

			if ( ! array_key_exists( 'fb:app_id', $og ) )
				$og['fb:app_id'] = $this->ngfb->options['fb_app_id'];

			if ( ! array_key_exists( 'og:locale', $og ) )
				$og['og:locale'] = $this->ngfb->options['fb_lang'];

			if ( ! array_key_exists( 'og:site_name', $og ) )
				$og['og:site_name'] = get_bloginfo( 'name', 'display' );	

			if ( ! array_key_exists( 'og:url', $og ) )
				$og['og:url'] = $sharing_url;

			if ( ! array_key_exists( 'og:title', $og ) )
				$og['og:title'] = $this->ngfb->webpage->get_title( $this->ngfb->options['og_title_len'], '...' );

			if ( ! array_key_exists( 'og:description', $og ) )
				$og['og:description'] = $this->ngfb->webpage->get_description( $this->ngfb->options['og_desc_len'], '...' );

			if ( ! array_key_exists( 'og:type', $og ) ) {
				// singular posts/pages are articles by default
				// check post_type for exceptions (like product pages)
				if ( is_singular() ) {
					if ( ! empty( $post ) )
						$post_type = $post->post_type;
					switch ( $post_type ) {
						case 'product' :
							$og['og:type'] = 'product';
							break;
						default :
							$og['og:type'] = 'article';
							break;
					}
				// check for default author info on indexes and searches
				} elseif ( ( ! is_singular() && ! is_search() && ! empty( $this->ngfb->options['og_def_author_on_index'] ) && ! empty( $this->ngfb->options['og_def_author_id'] ) )
					|| ( is_search() && ! empty( $this->ngfb->options['og_def_author_on_search'] ) && ! empty( $this->ngfb->options['og_def_author_id'] ) ) ) {
	
					$og['og:type'] = "article";
					if ( ! array_key_exists( 'article:author', $og ) )
						$og['article:author'] = $this->ngfb->user->get_author_url( $this->ngfb->options['og_def_author_id'], 
							$this->ngfb->options['og_author_field'] );

				// default for everything else is 'website'
				} else $og['og:type'] = 'website';
			}

			// if the page is an article, then define the other article meta tags
			if ( array_key_exists( 'og:type', $og ) && $og['og:type'] == 'article' ) {
				if ( is_singular() && ! array_key_exists( 'article:author', $og ) ) {
					if ( ! empty( $post ) && $post->post_author )
						$og['article:author'] = $this->ngfb->user->get_author_url( $post->post_author, 
							$this->ngfb->options['og_author_field'] );
					elseif ( ! empty( $this->ngfb->options['og_def_author_id'] ) )
						$og['article:author'] = $this->ngfb->user->get_author_url( $this->ngfb->options['og_def_author_id'], 
							$this->ngfb->options['og_author_field'] );
				}
				if ( ! array_key_exists( 'article:publisher', $og ) )
					$og['article:publisher'] = $this->ngfb->options['og_publisher_url'];

				if ( ! array_key_exists( 'article:tag', $og ) )
					$og['article:tag'] = $this->ngfb->tags->get();

				if ( ! array_key_exists( 'article:section', $og ) )
					$og['article:section'] = $this->ngfb->webpage->get_section();

				if ( ! array_key_exists( 'article:published_time', $og ) )
					$og['article:published_time'] = get_the_date('c');

				if ( ! array_key_exists( 'article:modified_time', $og ) )
					$og['article:modified_time'] = get_the_modified_date('c');
			}

			// get all videos
			// check first, to add video preview images
			if ( ! array_key_exists( 'og:video', $og ) ) {
				if ( $og_max['og_vid_max'] > 0 ) {
					$this->ngfb->debug->log( 'calling this->get_all_videos(' . $og_max['og_vid_max'] . ')' );
					$og['og:video'] = $this->get_all_videos( $og_max['og_vid_max'] );
					if ( is_array( $og['og:video'] ) ) {
						foreach ( $og['og:video'] as $val ) {
							if ( is_array( $val ) && ! empty( $val['og:image'] ) ) {
								$this->ngfb->debug->log( 'og:image found in og:video array (no default image required)' );
								$has_video_image = 1;
							}
						}
						unset ( $vid );
					}
				} else $this->ngfb->debug->log( 'videos disabled: maximum videos = 0' );
			}

			// get all images
			if ( ! array_key_exists( 'og:image', $og ) ) {
				if ( $og_max['og_img_max'] > 0 ) {
					$this->ngfb->debug->log( 'calling this->get_all_images(' . $og_max['og_img_max'] . ',"' . NGFB_OG_SIZE_NAME . '")' );
					$og['og:image'] = $this->get_all_images( $og_max['og_img_max'], NGFB_OG_SIZE_NAME );
	
					// if we didn't find any images, then use the default image
					if ( empty( $og['og:image'] ) && empty( $has_video_image ) ) {
						$this->ngfb->debug->log( 'calling this->ngfb->media->get_default_image(' . $og_max['og_img_max'] . ',"' . NGFB_OG_SIZE_NAME . '")' );
						$og['og:image'] = $this->ngfb->media->get_default_image( $og_max['og_img_max'], NGFB_OG_SIZE_NAME );
					}
				} else $this->ngfb->debug->log( 'images disabled: maximum images = 0' );
			}

			// run filter before saving to transient cache
			$og = apply_filters( 'ngfb_og', $og );
			set_transient( $cache_id, $og, $this->ngfb->cache->object_expire );
			$this->ngfb->debug->log( $cache_type . ': og array saved to transient for id "' . $cache_id . '" (' . $this->ngfb->cache->object_expire . ' seconds)');
			return $og;
		}

		private function get_all_videos( $num = 0 ) {
			global $post;
			$og_ret = array();

			if ( ! empty( $post ) ) {
				$num_remains = $this->num_remains( $og_ret, $num );
				$this->ngfb->debug->log( 'calling this->ngfb->media->get_meta_video(' . $num_remains . ',' . $post->ID . ')' );
				$og_ret = array_merge( $og_ret, $this->ngfb->media->get_meta_video( $num_remains, $post->ID ) );
			}

			// if we haven't reached the limit of images yet, keep going
			if ( ! $this->ngfb->util->is_maxed( $og_ret, $num ) ) {
				$num_remains = $this->num_remains( $og_ret, $num );
				$this->ngfb->debug->log( 'calling this->ngfb->media->get_content_videos(' . $num_remains . ')' );
				$og_ret = array_merge( $og_ret, $this->ngfb->media->get_content_videos( $num_remains ) );
			}

			$this->ngfb->util->slice_max( $og_ret, $num );
			return $og_ret;
		}

		private function get_all_images( $num = 0, $size_name = 'thumbnail' ) {
			global $post;
			$og_ret = array();

			// check for attachment page
			// since wp 2.0.0 
			if ( ! empty( $post ) && is_attachment( $post->ID ) ) {
				$og_image = array();
				$num_remains = $this->num_remains( $og_ret, $num );
				$this->ngfb->debug->log( 'calling this->ngfb->media->get_attachment_image(' . $num_remains . ',"' . $size_name . '",' . $post->ID . ')' );
				$og_image = $this->ngfb->media->get_attachment_image( $num_remains, $size_name, $post->ID );

				// if an attachment is not an image, then use the default image instead
				if ( empty( $og_ret ) ) {
					$num_remains = $this->num_remains( $og_ret, $num );
					$this->ngfb->debug->log( 'calling this->ngfb->media->get_default_image(' . $num_remains . ',"' . $size_name . '")' );
					$og_ret = array_merge( $og_ret, $this->ngfb->media->get_default_image( $num_remains, $size_name ) );
				} else $og_ret = array_merge( $og_ret, $og_image );

				return $og_ret;
			}

			// check for attachment page without an image, or index-type pages with og_def_img_on_index enabled to force a default image
			if ( ( ! is_singular() && ! is_search() && ! empty( $this->ngfb->options['og_def_img_on_index'] ) ) || 
				( is_search() && ! empty( $this->ngfb->options['og_def_img_on_search'] ) ) ) {

				$num_remains = $this->num_remains( $og_ret, $num );
				$this->ngfb->debug->log( 'calling this->ngfb->media->get_default_image(' . $num_remains . ',"' . $size_name . '")' );
				$og_ret = array_merge( $og_ret, $this->ngfb->media->get_default_image( $num_remains, $size_name ) );
				return $og_ret;	// stop here and return the image array
			}

			// check for custom meta, featured, or attached image(s)
			if ( ! empty( $post ) ) {

				$num_remains = $this->num_remains( $og_ret, $num );
				$this->ngfb->debug->log( 'calling this->ngfb->media->get_meta_image(' . $num_remains . ',"' . $size_name . '",' . $post->ID . ')' );
				$og_ret = array_merge( $og_ret, $this->ngfb->media->get_meta_image( $num_remains, $size_name, $post->ID ) );

				$num_remains = $this->num_remains( $og_ret, $num );
				$this->ngfb->debug->log( 'calling this->ngfb->media->get_featured(' . $num_remains . ',"' . $size_name . '",' . $post->ID . ')' );
				$og_ret = array_merge( $og_ret, $this->ngfb->media->get_featured( $num_remains, $size_name, $post->ID ) );

				if ( ! $this->ngfb->util->is_maxed( $og_ret, $num ) ) {
					$num_remains = $this->num_remains( $og_ret, $num );
					$this->ngfb->debug->log( 'calling this->ngfb->media->get_attached_images(' . $num_remains . ',"' . $size_name . '",' . $post->ID . ')' );
					$og_ret = array_merge( $og_ret, $this->ngfb->media->get_attached_images( $num_remains, $size_name, $post->ID ) );
				}
				// keep going to find more images
				// the featured / attached image(s) will be listed first in the open graph meta property tags
				// and duplicates will be filtered out
			}

			// check for ngg shortcodes and query vars
			if ( $this->ngfb->is_avail['ngg'] == true && ! $this->ngfb->util->is_maxed( $og_ret, $num ) ) {
				$ngg_query_og_ret = array();
				$num_remains = $this->num_remains( $og_ret, $num );
				if ( version_compare( $this->ngfb->ngg_version, '2.0.0', '<' ) ) {
					$this->ngfb->debug->log( 'calling this->ngfb->media->ngg->get_query_images(' . $num_remains . ',"' . $size_name . '")' );
					$ngg_query_og_ret = $this->ngfb->media->ngg->get_query_images( $num_remains, $size_name );
				}
				// if we found images in the query, skip content shortcodes
				if ( count( $ngg_query_og_ret ) > 0 ) {
					$this->ngfb->debug->log( count( $ngg_query_og_ret ) . ' image(s) returned - skipping additional shortcode images' );
					$og_ret = array_merge( $og_ret, $ngg_query_og_ret );
				// if no query images were found, continue with ngg shortcodes in content
				} elseif ( ! $this->ngfb->util->is_maxed( $og_ret, $num ) ) {
					$num_remains = $this->num_remains( $og_ret, $num );
					$this->ngfb->debug->log( 'calling this->ngfb->media->ngg->get_shortcode_images(' . $num_remains . ',"' . $size_name . '")' );
					$og_ret = array_merge( $og_ret, $this->ngfb->media->ngg->get_shortcode_images( $num_remains, $size_name ) );
				}
			}

			// if we haven't reached the limit of images yet, keep going
			if ( ! $this->ngfb->util->is_maxed( $og_ret, $num ) ) {
				$num_remains = $this->num_remains( $og_ret, $num );
				$this->ngfb->debug->log( 'calling this->ngfb->media->get_content_images(' . $num_remains . ',"' . $size_name . '")' );
				$og_ret = array_merge( $og_ret, $this->ngfb->media->get_content_images( $num_remains, $size_name ) );
			}

			$this->ngfb->util->slice_max( $og_ret, $num );
			return $og_ret;
		}

		public function get_max_nums() {
			$og_max = array();
			foreach ( array( 'og_vid_max', 'og_img_max' ) as $max_name ) {
				$num_meta = false;
				if ( ! empty( $post ) )
					$num_meta = $this->ngfb->meta->get_options( $post->ID, $max_name );
				if ( $num_meta !== false ) {
					$og_max[$max_name] = $num_meta;
					$this->ngfb->debug->log( 'found custom meta ' . $max_name . ' = ' . $num_meta );
				} else $og_max[$max_name] = $this->ngfb->options[$max_name];
			}
			unset ( $max_name );
			return $og_max;
		}

		private function num_remains( &$arr, $num = 0 ) {
			$remains = 0;
			if ( ! is_array( $arr ) ) return false;
			if ( $num > 0 && $num >= count( $arr ) ) {
				$remains = $num - count( $arr );
				$this->ngfb->debug->log( 'images count = ' . count( $arr ) . ' of ' . $num . ' max (' . $remains . ' remaining)' );
			}
			return $remains;
		}
	}
}

?>
