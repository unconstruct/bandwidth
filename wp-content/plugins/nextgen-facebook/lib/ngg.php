<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'Sorry, you cannot call this webpage directly.' );

if ( ! class_exists( 'ngfbMediaNgg' ) ) {

	class ngfbMediaNgg {

		private $ngfb;		// ngfbPlugin

		public function __construct( &$ngfb_plugin ) {
			$this->ngfb =& $ngfb_plugin;
			$this->ngfb->debug->mark();

			// try and filter after other plugins that may clobber our changes
			add_filter( 'ngg_image_object', array( &$this, 'add_image_attributes' ), 20, 2 );
			add_filter( 'ngg_get_thumbcode', array( &$this, 'add_thumbcode' ), 20, 2 );
		}

		// apply_filters('ngg_get_thumbcode', $this->thumbcode, $this);
		public function add_thumbcode( $thumbcode, $image ) {
			if ( ! empty( $image->pid ) )
				$thumbcode .= ' data-ngfb-ngg-pid="' . $image->pid . '"';
			return $thumbcode;
		}

		// apply_filters('ngg_image_object', $picturelist[$key], $picture->pid);
		public function add_image_attributes( $image, $pid ) {
			foreach ( array( 'href', 'imageHTML', 'thumbHTML' ) as $key )
				if ( ! empty( $image->$key ) )
					$image->$key = preg_replace( '/<img /i', '<img data-ngfb-ngg-pid="' . $pid . '" ', $image->$key );
			$image->style .= ' data-ngfb-ngg-pid="' . $pid . '"';
			return $image;
		}

		// called to get an image url from an ngg picture id and a media size name (the pid must be formatted as 'ngg-#')
		// TODO find a way to get accurate size information back from NGG v2+
		public function get_image_src( $pid, $size_name = 'thumbnail', $check_dupes = true ) {
			if ( $this->ngfb->is_avail['ngg'] !== true ||
				! is_string( $pid ) || 
				substr( $pid, 0, 4 ) !== 'ngg-' )
					return array( null, null, null, null );

			global $nggdb;
			$size_info = $this->ngfb->media->get_size_info( $size_name );
			$pid = substr( $pid, 4 );
			$img_url = '';
			$img_crop = $size_info['crop'] == 1 ? 'true' : 'false';
			$crop_arg = $size_info['crop'] == 1 ? 'crop' : '';
			$image = $nggdb->find_image( $pid );	// returns an nggImage object
			if ( ! empty( $image ) ) {
				$img_url = $image->cached_singlepic_file( $size_info['width'], $size_info['height'], $crop_arg ); 
				// if the image file doesn't exist, use the dynamic image url
				if ( empty( $img_url ) ) {
					$img_url = trailingslashit( site_url() ) . 
						'index.php?callback=image&amp;pid=' . $pid .
						'&amp;width=' . $size_info['width'] . 
						'&amp;height=' . $size_info['height'] . 
						'&amp;mode=' . $crop_arg;
				} elseif ( version_compare( $this->ngfb->ngg_version, '2.0.0', '<' ) ) {
					// get the real image width and height for ngg pre-v2.0
					$cachename = $image->pid . '_' . $crop_arg . '_'. $size_info['width'] . 'x' . $size_info['height'] . '_' . $image->filename;
					$cachefolder = WINABSPATH . $this->ngfb->ngg_options['gallerypath'] . 'cache/';
					$cached_file = $cachefolder . $cachename;
					//$cached_url = site_url() . '/' . $this->ngfb->ngg_options['gallerypath'] . 'cache/' . $cachename;
					if ( file_exists( $cached_file ) ) {
						$file_info = getimagesize( $cached_file );
						if ( ! empty( $file_info[0] ) && ! empty( $file_info[1] ) ) {
							$size_info['width'] = $file_info[0];
							$size_info['height'] = $file_info[1];
						}
					}
				} else {
					$this->ngfb->debug->log( 'accurate image dimensions are not available for ngg v2' );
					$size_info['width'] = '';
					$size_info['height'] = '';
					$img_crop = '';
				}
			}
			$this->ngfb->debug->log( 'image for pid:' . $pid . ' size:' . $size_name . ' = ' . $img_url . 
				' (' . $size_info['width'] . 'x' . $size_info['height'] . ')' );
			$img_url = $this->ngfb->util->fix_relative_url( $img_url );
			if ( ! empty( $img_url ) ) {
				if ( $check_dupes == false || $this->ngfb->util->is_uniq_url( $img_url ) )
					return array( $this->ngfb->util->rewrite( $img_url ), $size_info['width'], $size_info['height'], $img_crop );
			} else $this->ngfb->debug->log( 'image rejected: image url is empty' );
			return array( null, null, null, null );
		}

		public function get_content_images( $num = 0, $size_name = 'thumbnail', $check_dupes = true, $content = null ) {
			if ( $this->ngfb->is_avail['ngg'] !== true ) return;
			$og_ret = array();
			// allow custom content to be passed
			if ( empty( $content ) ) {
				$this->ngfb->debug->log( 'calling this->ngfb->webpage->get_content()' );
				$content = $this->ngfb->webpage->get_content( $this->ngfb->options['ngfb_filter_content'] );
			}
			if ( empty( $content ) ) { 
				$this->ngfb->debug->log( 'exiting early: empty post content' ); 
				return $og_ret; 
			}
			if ( preg_match_all( '/<(div|a|img)[^>]*? (data-ngfb-ngg-pid)=[\'"]([0-9]+)[\'"][^>]*>/is', $content, $match, PREG_SET_ORDER ) ) {
				$this->ngfb->debug->log( count( $match ) . ' x matching <div|a|img/> html tag(s) found' );
				foreach ( $match as $img ) {
					list( $og_image['og:image'], $og_image['og:image:width'], $og_image['og:image:height'],
						$og_image['og:image:cropped'] ) = $this->get_image_src( 'ngg-' . $img[3], $size_name, $check_dupes );
					if ( ! empty( $og_image['og:image'] ) && $this->ngfb->util->push_max( $og_ret, $og_image, $num ) ) 
						break;
				}
				return $og_ret;	// return immediately and ignore any other type of image
			}
			$this->ngfb->debug->log( 'no matching <div|a|img/> html tag found' );
			return $og_ret;
		}

		// want_this can be either 'gallery' or 'pid' (returns image when browsing the gallery)
		public function get_gallery_images( $num = 0, $size_name = 'large', $want_this = 'gallery', $check_dupes = false ) {
			if ( $this->ngfb->is_avail['ngg'] !== true ) 
				return array();

			global $post, $wp_query, $nggdb;
			$og_ret = array();

			if ( empty( $post ) ) { 
				$this->ngfb->debug->log( 'exiting early: empty post object' ); 
				return $og_ret;
			} elseif ( empty( $post->post_content ) ) { 
				$this->ngfb->debug->log( 'exiting early: empty post content' ); 
				return $og_ret;
			}

			// sanitize possible ngg pre-v2 query values
			$ngg_album = empty( $wp_query->query['album'] ) ? '' : preg_replace( '/[^0-9]/', '', $wp_query->query['album'] );
			$ngg_gallery = empty( $wp_query->query['gallery'] ) ? '' : preg_replace( '/[^0-9]/', '', $wp_query->query['gallery'] );
			$ngg_pageid = empty( $wp_query->query['pageid'] ) ? '' : preg_replace( '/[^0-9]/', '', $wp_query->query['pageid'] );
			$ngg_pid = empty( $wp_query->query['pid'] ) ? '' : preg_replace( '/[^0-9]/', '', $wp_query->query['pid'] );

			if ( ! empty( $ngg_album ) || ! empty( $ngg_gallery ) || ! empty( $ngg_pid ) ) {
				$this->ngfb->debug->log( 'ngg query found = pageid:' . $ngg_pageid . ' album:' . $ngg_album . 
					' gallery:' . $ngg_gallery . ' pid:' . $ngg_pid );
			}
			if ( $want_this == 'gallery' && $ngg_pid > 0 ) {
				$this->ngfb->debug->log( 'exiting early: want gallery but have query for pid:' . $ngg_pid );
				return $og_ret;
			} elseif ( $want_this == 'pid' && empty( $ngg_pid ) ) {
				$this->ngfb->debug->log( 'exiting early: want pid but don\'t have a query for pid' );
				return $og_ret;
			}

			if ( preg_match( '/\[(nggalbum|album|nggallery|nggtags)(| [^\]]*id=[\'"]*([0-9]+)[\'"]*[^\]]*| [^\]]*)\]/im', $post->post_content, $match ) ) {
				$shortcode_type = strtolower( $match[1] );
				$shortcode_id = ! empty( $match[3] ) ? $match[3] : 0;
				$this->ngfb->debug->log( '[' . $shortcode_type . '] shortcode found (id:' . $shortcode_id . ')' );
				switch ( $shortcode_type ) {
					case 'nggtags' :
						$content = do_shortcode( $match[0] );
						$content = preg_replace( '/\[' . $shortcode_type . '[^\]]*\]/', '', $content );	// prevent loops, just in case
						$og_ret = array_merge( $og_ret, $this->ngfb->media->get_content_images( $num, $size_name, $check_dupes, $content ) );
						break;
					default :
						// always trust hard-coded shortcode ID more than query arguments
						$ngg_album = $shortcode_type == 'nggalbum' || $shortcode_type == 'album' ? $shortcode_id : $ngg_album;
						$ngg_gallery = $shortcode_type == 'nggallery' ? $shortcode_id : $ngg_gallery;
						// security checks
						if ( $ngg_gallery > 0 && $ngg_album > 0 ) {
							$nggAlbum = $nggdb->find_album( $ngg_album );
							if ( in_array( $ngg_gallery, $nggAlbum->gallery_ids, true ) ) {
								$this->ngfb->debug->log( 'security check passed = gallery:' . $ngg_gallery . ' is in album:' . $ngg_album );
							} else {
								$this->ngfb->debug->log( 'security check failed = gallery:' . $ngg_gallery . ' is not in album:' . $ngg_album );
								return $og_ret;
							}
						}
						if ( $ngg_pid > 0 && $ngg_gallery > 0 ) {
							$pids = $nggdb->get_ids_from_gallery( $ngg_gallery );
							if ( in_array( $ngg_pid, $pids, true ) ) {
								$this->ngfb->debug->log( 'security check passed = pid:' . $ngg_pid . ' is in gallery:' . $ngg_gallery );
							} else {
								$this->ngfb->debug->log( 'security check failed = pid:' . $ngg_pid . ' is not in gallery:' . $ngg_gallery );
								return $og_ret;
							}
						}
						switch ( $want_this ) {
							case 'gallery' :
								if ( $ngg_gallery > 0 ) {
									// get_ids_from_gallery($id, $order_by = 'sortorder', $order_dir = 'ASC', $exclude = true)
									foreach ( array_slice( $nggdb->get_ids_from_gallery( $ngg_gallery, 'sortorder', 'ASC', true ), 0, $num ) as $pid ) {
										list( $og_image['og:image'], $og_image['og:image:width'], $og_image['og:image:height'],
											$og_image['og:image:cropped'] )= $this->get_image_src( 'ngg-' . $pid, $size_name, $check_dupes );
										if ( $this->ngfb->util->push_max( $og_ret, $og_image, $num ) ) return $og_ret;
									}
								}
								break;
							case 'pid' :
								if ( $ngg_pid > 0 ) {
									list( $og_image['og:image'], $og_image['og:image:width'], $og_image['og:image:height'],
										$og_image['og:image:cropped'] )= $this->get_image_src( 'ngg-' . $ngg_pid, $size_name, $check_dupes );
									if ( $this->ngfb->util->push_max( $og_ret, $og_image, $num ) ) return $og_ret;
								}
								break;
						}
						break;
				}	
			} else $this->ngfb->debug->log( '[nggalbum|album|nggallery|nggtags] shortcode not found' );
			$this->ngfb->util->slice_max( $og_ret, $num );
			return $og_ret;
		}

		// parse ngg pre-v2 query arguments
		public function get_query_images( $num = 0, $size_name = 'thumbnail', $check_dupes = true ) {
			// exit if ngg is not active, or version is 2.0.0 or greater
			if ( $this->ngfb->is_avail['ngg'] !== true || version_compare( $this->ngfb->ngg_version, '2.0.0', '>=' ) )
				return array();

			global $post, $wp_query, $nggdb;
			$og_ret = array();

			if ( empty( $post ) ) { 
				$this->ngfb->debug->log( 'exiting early: empty post object' ); 
				return $og_ret; 
			} elseif ( empty( $post->post_content ) ) { 
				$this->ngfb->debug->log( 'exiting early: empty post content' ); 
				return $og_ret;
			}

			// sanitize possible query values
			$ngg_album = empty( $wp_query->query['album'] ) ? '' : preg_replace( '/[^0-9]/', '', $wp_query->query['album'] );
			$ngg_gallery = empty( $wp_query->query['gallery'] ) ? '' : preg_replace( '/[^0-9]/', '', $wp_query->query['gallery'] );
			$ngg_pageid = empty( $wp_query->query['pageid'] ) ? '' : preg_replace( '/[^0-9]/', '', $wp_query->query['pageid'] );
			$ngg_pid = empty( $wp_query->query['pid'] ) ? '' : preg_replace( '/[^0-9]/', '', $wp_query->query['pid'] );

			if ( empty( $ngg_album ) && empty( $ngg_gallery ) && empty( $ngg_pid ) ) {
				$this->ngfb->debug->log( 'exiting early: no ngg query values' ); 
				return $og_ret;
			} else {
				$this->ngfb->debug->log( 'ngg query found = pageid:' . $ngg_pageid . ' album:' . $ngg_album . 
					' gallery:' . $ngg_gallery . ' pid:' . $ngg_pid );
			}

			if ( preg_match( '/\[(nggalbum|album|nggallery)(| [^\]]*id=[\'"]*([0-9]+)[\'"]*[^\]]*| [^\]]*)\]/im', $post->post_content, $match ) ) {
				$shortcode_type = $match[1];
				$shortcode_id = ! empty( $match[3] ) ? $match[3] : 0;
				$this->ngfb->debug->log( 'ngg query with [' . $shortcode_type . '] shortcode (id:' . $shortcode_id . ')' );

				// always trust hard-coded shortcode ID more than query arguments
				$ngg_album = $shortcode_type == 'nggalbum' || $shortcode_type == 'album' ? $shortcode_id : $ngg_album;
				$ngg_gallery = $shortcode_type == 'nggallery' ? $shortcode_id : $ngg_gallery;

				// security checks
				if ( $ngg_gallery > 0 && $ngg_album > 0 ) {
					$nggAlbum = $nggdb->find_album( $ngg_album );
					if ( in_array( $ngg_gallery, $nggAlbum->gallery_ids, true ) ) {
						$this->ngfb->debug->log( 'security check passed = gallery:' . $ngg_gallery . ' is in album:' . $ngg_album );
					} else {
						$this->ngfb->debug->log( 'security check failed = gallery:' . $ngg_gallery . ' is not in album:' . $ngg_album );
						return $og_ret;
					}
				}
				if ( $ngg_pid > 0 && $ngg_gallery > 0 ) {
					$pids = $nggdb->get_ids_from_gallery( $ngg_gallery );
					if ( in_array( $ngg_pid, $pids, true ) ) {
						$this->ngfb->debug->log( 'security check passed = pid:' . $ngg_pid . ' is in gallery:' . $ngg_gallery );
					} else {
						$this->ngfb->debug->log( 'security check failed = pid:' . $ngg_pid . ' is not in gallery:' . $ngg_gallery );
						return $og_ret;
					}
				}
				if ( $ngg_pid > 0 ) {
					$this->ngfb->debug->log( 'getting image for ngg query pid:' . $ngg_pid );
					list( $og_image['og:image'], $og_image['og:image:width'], $og_image['og:image:height'], 
						$og_image['og:image:cropped'] ) = $this->get_image_src( 'ngg-' . $ngg_pid, $size_name, $check_dupes );
					if ( $this->ngfb->util->push_max( $og_ret, $og_image, $num ) ) return $og_ret;
				} elseif ( $ngg_gallery > 0 ) {
					$gallery = $nggdb->find_gallery( $ngg_gallery );
					if ( ! empty( $gallery ) ) {
						if ( ! empty( $gallery->previewpic ) ) {
							$this->ngfb->debug->log( 'getting previewpic:' . $gallery->previewpic . ' for gallery:' . $ngg_gallery );
							list( $og_image['og:image'], $og_image['og:image:width'], $og_image['og:image:height'], 
								$og_image['og:image:cropped'] ) = $this->get_image_src( 'ngg-' . $gallery->previewpic, $size_name, $check_dupes );
							if ( $this->ngfb->util->push_max( $og_ret, $og_image, $num ) ) return $og_ret;
						} else $this->ngfb->debug->log( 'no previewpic for gallery:' . $ngg_gallery );
					} else $this->ngfb->debug->log( 'no gallery:' . $ngg_gallery . ' found' );
				} elseif ( $ngg_album > 0 ) {
					$album = $nggfb->find_album( $ngg_album );
					if ( ! empty( $albums ) ) {
						if ( ! empty( $album->previewpic ) ) {
							$this->ngfb->debug->log( 'getting previewpic:' . $album->previewpic . ' for album:' . $ngg_album );
							list( $og_image['og:image'], $og_image['og:image:width'], $og_image['og:image:height'], 
								$og_image['og:image:cropped'] ) = $this->get_image_src( 'ngg-' . $album->previewpic, $size_name, $check_dupes );
							if ( $this->ngfb->util->push_max( $og_ret, $og_image, $num ) ) return $og_ret;
						} else $this->ngfb->debug->log( 'no previewpic for album:' . $ngg_album );
					} else $this->ngfb->debug->log( 'no album:' . $ngg_album . ' found' );
				}
			} else $this->ngfb->debug->log( 'ngg query without [nggalbum|album|nggallery] shortcode' );

			$this->ngfb->util->slice_max( $og_ret, $num );
			return $og_ret;
		}

		public function get_shortcode_images( $num = 0, $size_name = 'thumbnail', $check_dupes = true ) {
			if ( $this->ngfb->is_avail['ngg'] !== true ) 
				return array();

			global $post, $wpdb;
			$og_ret = array();

			if ( empty( $post ) ) { 
				$this->ngfb->debug->log( 'exiting early: empty post object' ); 
				return $og_ret; 
			} elseif ( empty( $post->post_content ) ) { 
				$this->ngfb->debug->log( 'exiting early: empty post content' ); 
				return $og_ret; 
			}

			if ( preg_match_all( '/\[(nggalbum|album)(| [^\]]*id=[\'"]*([0-9]+)[\'"]*[^\]]*| [^\]]*)\]/im', $post->post_content, $match, PREG_SET_ORDER ) ) {
				foreach ( $match as $album ) {
					$og_image = array();
					if ( empty( $album[3] ) ) {
						$ngg_album = 0;
						$this->ngfb->debug->log( 'album id zero or not found - setting album id to 0 (all)' );
					} else $ngg_album = $album[3];
					$this->ngfb->debug->log( '[' . $album[1] . '] shortcode found (id:' . $ngg_album . ')' );
					if ( $ngg_album > 0 ) 
						$albums = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->nggalbum . ' WHERE id IN (\'' . $ngg_album . '\')', OBJECT_K );
					else $albums = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->nggalbum, OBJECT_K );
					if ( is_array( $albums ) ) {
						foreach ( $albums as $row ) {
							if ( ! empty( $row->previewpic ) ) {
								$this->ngfb->debug->log( 'getting previewpic:' . $row->previewpic . ' for album:' . $row->id );
								list( $og_image['og:image'], $og_image['og:image:width'], $og_image['og:image:height'], 
									$og_image['og:image:cropped'] ) = $this->get_image_src( 'ngg-' . $row->previewpic, $size_name, $check_dupes );
								if ( $this->ngfb->util->push_max( $og_ret, $og_image, $num ) ) return $og_ret;
							} else $this->ngfb->debug->log( 'no previewpic for album:' . $row->id );
						}
					} else $this->ngfb->debug->log( 'no album(s) found' );
				}
			} else $this->ngfb->debug->log( 'no [nggalbum|album] shortcode found' );

			if ( preg_match_all( '/\[(nggallery) [^\]]*id=[\'"]*([0-9]+)[\'"]*[^\]]*\]/im', $post->post_content, $match, PREG_SET_ORDER ) ) {
				foreach ( $match as $gallery ) {
					$this->ngfb->debug->log( '[' . $gallery[1] . '] shortcode found (id:' . $gallery[2] . ')' );
					$og_image = array();
					$ngg_gallery = $gallery[2];
					$galleries = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->nggallery . ' WHERE gid IN (\'' . $ngg_gallery . '\')', OBJECT_K );
					if ( is_array( $galleries ) ) {
						foreach ( $galleries as $row ) {
							if ( ! empty( $row->previewpic ) ) {
								$this->ngfb->debug->log( 'getting previewpic:' . $row->previewpic . ' for gallery:' . $row->gid );
								list( $og_image['og:image'], $og_image['og:image:width'], $og_image['og:image:height'], 
									$og_image['og:image:cropped'] ) = $this->get_image_src( 'ngg-' . $row->previewpic, $size_name, $check_dupes );
								if ( $this->ngfb->util->push_max( $og_ret, $og_image, $num ) ) return $og_ret;
							} else $this->ngfb->debug->log( 'no previewpic for gallery:' . $row->gid );
						}
					} else $this->ngfb->debug->log( 'no gallery:' . $ngg_gallery . ' found' );
				}
			} else $this->ngfb->debug->log( 'no [nggallery] shortcode found' );

			if ( preg_match_all( '/\[(singlepic) [^\]]*id=[\'"]*([0-9]+)[\'"]*[^\]]*\]/im', $post->post_content, $match, PREG_SET_ORDER ) ) {
				foreach ( $match as $singlepic ) {
					$this->ngfb->debug->log( '[' . $singlepic[1] . '] shortcode found (id:' . $singlepic[2] . ')' );
					$og_image = array();
					$pid = $singlepic[2];
					$this->ngfb->debug->log( 'getting image for singlepic:' . $pid );
					list( $og_image['og:image'], $og_image['og:image:width'], $og_image['og:image:height'], 
						$og_image['og:image:cropped'] ) = $this->get_image_src( 'ngg-' . $pid, $size_name, $check_dupes );
					if ( $this->ngfb->util->push_max( $og_ret, $og_image, $num ) ) return $og_ret;
				}
			} else $this->ngfb->debug->log( 'no [singlepic] shortcode found' );

			$this->ngfb->util->slice_max( $og_ret, $num );
			return $og_ret;
		}

		public function get_singlepic_images( $num = 0, $size_name = 'thumbnail', $check_dupes = false ) {
			if ( $this->ngfb->is_avail['ngg'] !== true ) 
				return array();

			global $post;
			$og_ret = array();

			if ( empty( $post ) ) { 
				$this->ngfb->debug->log( 'exiting early: empty post object' ); 
				return $og_ret; 
			} elseif ( empty( $post->post_content ) ) { 
				$this->ngfb->debug->log( 'exiting early: empty post content' ); 
				return $og_ret; 
			}

			if ( preg_match_all( '/\[(singlepic) [^\]]*id=[\'"]*([0-9]+)[\'"]*[^\]]*\]/im', $post->post_content, $match, PREG_SET_ORDER ) ) {
				foreach ( $match as $singlepic ) {
					$this->ngfb->debug->log( '[' . $singlepic[1] . '] shortcode found (id:' . $singlepic[2] . ')' );
					$og_image = array();
					$pid = $singlepic[2];
					$this->ngfb->debug->log( 'getting image for singlepic:' . $pid );
					list( $og_image['og:image'], $og_image['og:image:width'], $og_image['og:image:height'], 
						$og_image['og:image:cropped'] ) = $this->get_image_src( 'ngg-' . $pid, $size_name, $check_dupes );
					if ( $this->ngfb->util->push_max( $og_ret, $og_image, $num ) ) return $og_ret;
				}
			} else $this->ngfb->debug->log( 'no [singlepic] shortcode found' );

			$this->ngfb->util->slice_max( $og_ret, $num );
			return $og_ret;
		}

		// called from the view/gallery-meta.php template
		public function get_from_images( $num = 0, $size_name = 'thumbnail', $ngg_images = array() ) {
			if ( $this->ngfb->is_avail['ngg'] !== true ) 
				return array();
			$og_ret = array();
			if ( is_array( $ngg_images ) ) {
				foreach ( $ngg_images as $image ) {
					if ( ! empty( $image->pid ) ) {
						$og_image = array();
						list( $og_image['og:image'], $og_image['og:image:width'], $og_image['og:image:height'], 
							$og_image['og:image:cropped'] ) = $this->get_image_src( 'ngg-' . $image->pid, $size_name );
						$this->ngfb->util->push_max( $og_ret, $og_image, $num );
					}
				}
			}
			return $og_ret;
		}

	}
}

?>
