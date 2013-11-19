<?php
/*
Plugin Name: NGFB Open Graph+
Plugin URI: http://surniaulula.com/extend/plugins/nextgen-facebook/
Author: Jean-Sebastien Morisset
Author URI: http://surniaulula.com/
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Description: Adds HTML header tags for better Google Search results and Social Sharing posts. An essential plugin for every WordPress website!
Version: 6.7.4.2

Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'Sorry, you cannot call this webpage directly.' );

if ( ! class_exists( 'ngfbPlugin' ) ) {

	class ngfbPlugin {

		public $version = '6.7.4.2';
		public $acronym = 'ngfb';
		public $acronym_uc = 'NGFB';
		public $menuname = 'Open Graph+';
		public $fullname = 'NGFB Open Graph+';
		public $fullname_pro = 'NGFB Open Graph+ Pro';
		public $slug = 'nextgen-facebook';
		public $update_hours = 12;

		public $debug;		// ngfbDebug
		public $util;		// ngfbUtil
		public $notices;	// ngfbNotices
		public $opt;		// ngfbOptions
		public $user;		// ngfbUser
		public $media;		// ngfbMedia
		public $meta;		// ngfbPostMeta
		public $style;		// ngfbStyle
		public $script;		// ngfbStyle
		public $cache;		// ngfbCache
		public $admin;		// ngfbAdmin
		public $head;		// ngfbHead
		public $tags;		// ngfbTags
		public $webpage;	// ngfbWebPage
		public $social;		// ngfbSocial
		public $seo;		// ngfbSeo*
		public $pro;		// ngfbAddOnPro
		public $update;		// ngfbUpdate

		public $is_avail = array();	// assoc array for function/class/method/etc. checks
		public $options = array();
		public $ngg_options = array();
		public $ngg_version = 0;

		public $urls = array(
			'email' => 'jsm@surniaulula.com',
			'website' => 'http://surniaulula.com/',
			'feed' => 'http://feed.surniaulula.com/category/application/wordpress/wp-plugins/ngfb/feed/',
			'plugin' => 'http://plugin.surniaulula.com/extend/plugins/nextgen-facebook/',
			'update' => 'http://update.surniaulula.com/extend/plugins/nextgen-facebook/update/',
			'readme' => 'http://plugins.svn.wordpress.org/nextgen-facebook/trunk/readme.txt',
			'support_forum' => 'http://wordpress.org/support/plugin/nextgen-facebook',
			'support_feed' => 'http://wordpress.org/support/rss/plugin/nextgen-facebook',
			'review' => 'http://wordpress.org/support/view/plugin-reviews/nextgen-facebook',
		);

		public $css_names = array(
			'social' => 'Buttons Style',
			'excerpt' => 'Excerpt Style',
			'content' => 'Content Style',
			'shortcode' => 'Shortcode Style',
			'widget' => 'Widget Style',
		);

		public $social_prefix = array(
			'facebook' => 'fb', 
			'gplus' => 'gp',
			'twitter' => 'twitter',
			'linkedin' => 'linkedin',
			'pinterest' => 'pin',
			'stumbleupon' => 'stumble',
			'tumblr' => 'tumblr',
		);

		public $website_libs = array(
			'facebook' => 'Facebook', 
			'gplus' => 'GooglePlus',
			'twitter' => 'Twitter',
			'linkedin' => 'LinkedIn',
			'pinterest' => 'Pinterest',
			'stumbleupon' => 'StumbleUpon',
			'tumblr' => 'Tumblr',
		);

		public $shortcode_libs = array(
			'ngfb' => 'Ngfb',
		);

		public $widget_libs = array(
			'social' => 'SocialSharing',
		);

		public $setting_libs = array(
			'general' => 'General',
			'advanced' => 'Advanced',
			'social' => 'Social Sharing',
			'style' => 'Social Style',
			'about' => 'About',
		);

		public $seo_libs = array(
			'aioseop' => 'All in One SEO Pack',
			'seou' => 'SEO Ultimate',
			'wpseo' => 'WordPress SEO',
		);

		public function __construct() {
			$this->define_constants();	// define constants first for option defaults
			$this->load_libs();		// keep in __construct() to extend widgets etc.

			// since wp 3.1 : register_activation_hook is now fired only when the user 
			// activates the plugin and not when an automatic plugin update occurs
			register_activation_hook( __FILE__, array( &$this, 'activate' ) );		// since wp 2.0
			register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );		// since wp 2.0
			register_uninstall_hook( __FILE__, array( 'ngfbPlugin', 'uninstall' ) );	// since wp 2.7

			add_action( 'init', array( &$this, 'init_plugin' ), NGFB_INIT_PRIORITY );	// since wp 1.2.0
		}

		public function activate() {
			$this->check_wp_version();
			$this->setup_vars( true );
		}

		public function deactivate() {
			$this->debug->mark();
			wp_clear_scheduled_hook( 'plugin_updates-' . $this->slug );	// since wp 2.1.0
		}

		// delete options table entries only when plugin deactivated and deleted
		public static function uninstall() {
			$options = get_option( NGFB_OPTIONS_NAME );
			if ( empty( $options['ngfb_preserve'] ) ) {
				delete_option( NGFB_OPTIONS_NAME );
				delete_option( 'external_updates-nextgen-facebook' );
				// remove all "stored" admin notices
				foreach ( array( 'nag', 'err', 'inf' ) as $type ) {
					$msg_opt = 'ngfb_notices_' . $type;
					delete_option( $msg_opt );
					foreach ( get_users( array( 'meta_key' => $msg_opt ) ) as $user )
						delete_user_option( $user->ID, $msg_opt );
				}
				// remove metabox preferences from all users
				foreach ( array( 'meta-box-order', 'metaboxhidden', 'closedpostboxes' ) as $meta_name ) {
					foreach ( array( 'toplevel_page', 'open-graph_page' ) as $page_prefix ) {
						foreach ( array( 'general', 'advanced', 'social', 'style', 'about' ) as $settings_page ) {
							$meta_key = $meta_name . '_' . $page_prefix . '_ngfb-' . $settings_page;
							foreach ( get_users( array( 'meta_key' => $meta_key ) ) as $user )
								delete_user_option( $user->ID, $meta_key, true );
						}
					}
				}
			}
		}

		public function filter_version_number( $version ) {
			if ( $this->is_avail['aop'] == true )
				return $version;
			else return '0.' . $version . '-free';
		}

		// called by WP init action
		public function init_plugin() {
			load_plugin_textdomain( NGFB_TEXTDOM, false, dirname( NGFB_PLUGINBASE ) . '/languages/' );
			$this->setup_vars();
			$this->error_checks();
			if ( $this->debug->is_on() == true ) {
				foreach ( array( 'wp_head', 'wp_footer' ) as $action ) {
					foreach ( array( 1, 9999 ) as $prio )
						add_action( $action, create_function( '', 
							"echo '<!-- " . $this->fullname . " add_action( \'$action\' ) Priority $prio Test = PASSED -->\n';" ), $prio );
				}
			}
		}

		private function define_constants() { 

			define( 'NGFB_FILEPATH', __FILE__ );
			define( 'NGFB_PLUGINDIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );	// since wp 1.2.0 
			define( 'NGFB_PLUGINBASE', plugin_basename( __FILE__ ) );			// since wp 1.5
			define( 'NGFB_TEXTDOM', $this->slug );
			define( 'NGFB_URLPATH', trailingslashit( plugins_url( '', __FILE__ ) ) );
			define( 'NGFB_NONCE', md5( NGFB_PLUGINDIR ) );
			define( 'AUTOMATTIC_README_MARKDOWN', NGFB_PLUGINDIR . 'lib/ext/markdown.php' );

			// allow some constants to be pre-defined in wp-config.php

			// NGFB_RESET
			// NGFB_DEBUG
			// NGFB_WP_DEBUG
			// NGFB_OPEN_GRAPH_DISABLE
			// NGFB_MIN_IMG_SIZE_DISABLE
			// NGFB_CURL_DISABLE
			// NGFB_CURL_PROXY
			// NGFB_CURL_PROXYUSERPWD
			// NGFB_WISTIA_API_PWD

			if ( ! defined( 'NGFB_CACHEDIR' ) )
				define( 'NGFB_CACHEDIR', NGFB_PLUGINDIR . 'cache/' );

			if ( ! defined( 'NGFB_CACHEURL' ) )
				define( 'NGFB_CACHEURL', NGFB_URLPATH . 'cache/' );

			if ( ! defined( 'NGFB_OPTIONS_NAME' ) )
				define( 'NGFB_OPTIONS_NAME', 'ngfb_options' );

			if ( ! defined( 'NGFB_META_NAME' ) )
				define( 'NGFB_META_NAME', 'ngfb_meta' );

			if ( ! defined( 'NGFB_MENU_PRIORITY' ) )
				define( 'NGFB_MENU_PRIORITY', '99.10' );

			if ( ! defined( 'NGFB_INIT_PRIORITY' ) )
				define( 'NGFB_INIT_PRIORITY', 12 );

			if ( ! defined( 'NGFB_HEAD_PRIORITY' ) )
				define( 'NGFB_HEAD_PRIORITY', 10 );

			if ( ! defined( 'NGFB_SOCIAL_PRIORITY' ) )
				define( 'NGFB_SOCIAL_PRIORITY', 100 );
			
			if ( ! defined( 'NGFB_FOOTER_PRIORITY' ) )
				define( 'NGFB_FOOTER_PRIORITY', 100 );
			
			if ( ! defined( 'NGFB_OG_SIZE_NAME' ) )
				define( 'NGFB_OG_SIZE_NAME', 'ngfb-open-graph' );

			if ( ! defined( 'NGFB_MIN_DESC_LEN' ) )
				define( 'NGFB_MIN_DESC_LEN', 156 );

			if ( ! defined( 'NGFB_MIN_IMG_SIZE' ) )
				define( 'NGFB_MIN_IMG_SIZE', 200 );

			if ( ! defined( 'NGFB_MAX_IMG_OG' ) )
				define( 'NGFB_MAX_IMG_OG', 20 );

			if ( ! defined( 'NGFB_MAX_VID_OG' ) )
				define( 'NGFB_MAX_VID_OG', 20 );

			if ( ! defined( 'NGFB_MAX_CACHE_HRS' ) )
				define( 'NGFB_MAX_CACHE_HRS', 24 );

			if ( ! defined( 'NGFB_DEBUG_OBJ_EXP' ) )
				define( 'NGFB_DEBUG_OBJ_EXP', 3 );

			if ( ! defined( 'NGFB_DEBUG_FILE_EXP' ) )
				define( 'NGFB_DEBUG_FILE_EXP', 5 );

			if ( ! defined( 'NGFB_CONTACT_FIELDS' ) )
				define( 'NGFB_CONTACT_FIELDS', 'facebook:Facebook URL,gplus:Google+ URL,twitter:Twitter @username' );

			if ( ! defined( 'NGFB_TWITTER_FIELD_ID' ) )
				define( 'NGFB_TWITTER_FIELD_ID', 'twitter' );

			if ( ! defined( 'NGFB_CURL_USERAGENT' ) )
				define( 'NGFB_CURL_USERAGENT', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:18.0) Gecko/20100101 Firefox/18.0' );

			if ( ! defined( 'NGFB_CURL_CAINFO' ) )
				define( 'NGFB_CURL_CAINFO', NGFB_PLUGINDIR . 'share/curl/cacert.pem' );

		}

		private function load_libs() {

			require_once ( NGFB_PLUGINDIR . 'lib/debug.php' );
			require_once ( NGFB_PLUGINDIR . 'lib/util.php' );
			require_once ( NGFB_PLUGINDIR . 'lib/notices.php' );
			require_once ( NGFB_PLUGINDIR . 'lib/options.php' );
			require_once ( NGFB_PLUGINDIR . 'lib/user.php' );
			require_once ( NGFB_PLUGINDIR . 'lib/media.php' );
			require_once ( NGFB_PLUGINDIR . 'lib/webpage.php' );
			require_once ( NGFB_PLUGINDIR . 'lib/postmeta.php' );
			require_once ( NGFB_PLUGINDIR . 'lib/style.php' );
			require_once ( NGFB_PLUGINDIR . 'lib/script.php' );
			require_once ( NGFB_PLUGINDIR . 'lib/cache.php' );
			require_once ( NGFB_PLUGINDIR . 'lib/ext/googl.php' );
			require_once ( NGFB_PLUGINDIR . 'lib/ext/plugin-updates.php' );

			if ( is_admin() ) {
				require_once ( NGFB_PLUGINDIR . 'lib/messages.php' );
				require_once ( NGFB_PLUGINDIR . 'lib/admin.php' );
				// settings classes extend lib/admin.php and are created by lib/admin.php
				foreach ( $this->setting_libs as $id => $name )
					require_once ( NGFB_PLUGINDIR . 'lib/settings/' . $id . '.php' );
				unset ( $id, $name );
				require_once ( NGFB_PLUGINDIR . 'lib/form.php' );
				require_once ( NGFB_PLUGINDIR . 'lib/ext/parse-readme.php' );
			} else {
				require_once ( NGFB_PLUGINDIR . 'lib/head.php' );
				require_once ( NGFB_PLUGINDIR . 'lib/opengraph.php' );
				require_once ( NGFB_PLUGINDIR . 'lib/tags.php' );
				require_once ( NGFB_PLUGINDIR . 'lib/functions.php' );
				require_once ( NGFB_PLUGINDIR . 'lib/social.php' );
				// ngfb_shortcode class object is created by lib/webpage.php
				foreach ( $this->shortcode_libs as $id => $name )
					require_once ( NGFB_PLUGINDIR . 'lib/shortcodes/' . $id . '.php' );
				unset ( $id, $name );
			}

			// website classes extend both lib/social.php and lib/settings/social.php
			foreach ( $this->website_libs as $id => $name )
				require_once ( NGFB_PLUGINDIR . 'lib/websites/' . $id . '.php' );
			unset ( $id, $name );

			// widgets are added to wp when library file is loaded
			foreach ( $this->widget_libs as $id => $name )
				require_once ( NGFB_PLUGINDIR . 'lib/widgets/' . $id . '.php' );
			unset ( $id, $name );

			// pro version classes
			// additional classes are loaded and created by pro construct
			if ( file_exists( NGFB_PLUGINDIR . 'lib/pro/addon.php' ) )
				require_once ( NGFB_PLUGINDIR . 'lib/pro/addon.php' );

		}

		// get the options, upgrade the options (if necessary), and validate their values
		private function setup_vars( $activate = false ) {

			/*
			 * Allow override of default variables
			 */
			if ( defined( 'NGFB_UPDATE_URL' ) && NGFB_UPDATE_URL )
				$this->urls['update'] = NGFB_UPDATE_URL;

			/*
			 * load all plugin options
			 */
			$this->is_avail = $this->check_deps();
			if ( $this->is_avail['aop'] == true ) 
				$this->fullname = $this->fullname_pro;
			if ( $this->is_avail['ngg'] == true ) {
				$this->ngg_options = get_option( 'ngg_options' );
				if ( defined( 'NEXTGEN_GALLERY_PLUGIN_VERSION' ) && NEXTGEN_GALLERY_PLUGIN_VERSION )
					$this->ngg_version = NEXTGEN_GALLERY_PLUGIN_VERSION;
			}
			$this->options = get_option( NGFB_OPTIONS_NAME );
	
			/*
			 * create essential class objects
			 */
			$this->debug = new ngfbDebug( $this->fullname, 'NGFB', array( 
					'html' => ( ! empty( $this->options['ngfb_debug'] ) || 
						( defined( 'NGFB_DEBUG' ) && NGFB_DEBUG ) ? true : false ),
					'wp' => ( defined( 'NGFB_WP_DEBUG' ) && NGFB_WP_DEBUG ? true : false ),
				)
			);
			$this->util = new ngfbUtil( $this );
			$this->notices = new ngfbNotices( $this );
			$this->opt = new ngfbOptions( $this );

			/*
			 * plugin is being activated - create default options, if necessary, and exit
			 */
			if ( $activate == true || ( 
				! empty( $_GET['action'] ) && $_GET['action'] == 'activate-plugin' &&
				! empty( $_GET['plugin'] ) && $_GET['plugin'] == NGFB_PLUGINBASE ) ) {
				$this->debug->log( 'plugin activation detected' );
				if ( ! is_array( $this->options ) || empty( $this->options ) ||
					! empty( $this->options['ngfb_reset'] ) || ( defined( 'NGFB_RESET' ) && NGFB_RESET ) ) {
					$this->options = $this->opt->get_defaults();
					$this->options['ngfb_opts_ver'] = $this->opt->opts_ver;
					$this->options['ngfb_plugin_ver'] = $this->version;
					delete_option( NGFB_OPTIONS_NAME );
					add_option( NGFB_OPTIONS_NAME, $this->options, null, 'yes' );
					$this->debug->log( 'default options have been added to the database' );
				}
				$this->debug->log( 'exiting early: init_plugin() to follow' );
				// no need to continue, init_plugin() will handle the rest
				return;
			}

			/*
			 * continue creating remaining object classes
			 */
			$this->user = new ngfbUser( $this );
			$this->media = new ngfbMedia( $this );
			$this->webpage = new ngfbWebPage( $this );		// title, desc, etc., plus shortcodes
			$this->meta = new ngfbPostMeta( $this );
			$this->style = new ngfbStyle( $this );
			$this->script = new ngfbScript( $this );
			$this->cache = new ngfbCache( $this );

			if ( is_admin() ) {
				$this->msg = new ngfbMessages( $this );
				$this->admin = new ngfbAdmin( $this );
			} else {
				$this->head = new ngfbHead( $this );		// wp_head / opengraph
				$this->tags = new ngfbTags( $this );		// ngg image tags and wp post/page tags
				$this->social = new ngfbSocial( $this );	// wp_head and wp_footer js and buttons
			}

			// create pro class object last - it extends several previous classes
			if ( $this->is_avail['aop'] == true )
				$this->pro = new ngfbAddOnPro( $this );

			/*
			 * check options array read from database - upgrade options if necessary
			 */
			$this->options = $this->opt->quick_check( $this->options );

			/*
			 * setup class properties, etc. based on option values
			 */
			add_image_size( NGFB_OG_SIZE_NAME, 
				$this->options['og_img_width'], 
				$this->options['og_img_height'], 
				$this->options['og_img_crop'] 
			);

			// set the file cache expiration value
			if ( is_admin() )
				if ( $this->debug->is_on( 'wp' ) == true ) 
					$this->cache->file_expire = NGFB_DEBUG_FILE_EXP;
				else $this->cache->file_expire = $this->update_hours * 60 * 60;
			elseif ( $this->is_avail['aop'] == true )
				$this->cache->file_expire = ! empty( $this->options['ngfb_file_cache_hrs'] ) ? 
					$this->options['ngfb_file_cache_hrs'] * 60 * 60 : 0;
			else $this->cache->file_expire = 0;

			if ( $this->debug->is_on( 'wp' ) == true ) {
				$this->debug->log( 'NGFB WP debug mode is ON' );
				$this->debug->log( 'File cache expiration set to ' . $this->cache->file_expire . ' second(s)' );
			}

			// set the object cache expiration value
			if ( $this->debug->is_on( 'html' ) == true ) {
				$this->cache->object_expire = NGFB_DEBUG_OBJ_EXP;
				$this->debug->log( 'NGFB HTML debug mode is ON' );
				$this->debug->log( 'WP object cache expiration set to ' . $this->cache->object_expire . ' second(s) for new objects' );
				$this->notices->inf( __( 'NGFB HTML debug mode is ON.', NGFB_TEXTDOM ) . ' ' .
					__( 'Activity messages are being added to webpages as hidden HTML comments.', NGFB_TEXTDOM ) .
					sprintf( __( 'WP object cache expiration has been <em>temporarily</em> set at %d second(s).' ), $this->cache->object_expire ) );
			} else $this->cache->object_expire = $this->options['ngfb_object_cache_exp'];

			// setup update checks if we have an Authentication ID
			if ( ! empty( $this->options['ngfb_pro_tid'] ) ) {
				add_filter( 'ngfb_installed_version', array( &$this, 'filter_version_number' ), 10, 1 );
				$this->update = new ngfbUpdate( $this );
			}

		}

		private function error_checks() {

			$conflict_prefix =  __( 'Plugin conflict detected', NGFB_TEXTDOM ) . ' -- ';

			// PHP
			if ( $this->is_avail['mbdecnum'] !== true ) {
				$this->debug->log( 'mb_decode_numericentity() function missing (required to decode UTF8 entities)' );
				$this->notices->err( sprintf( __( 'The <code><a href="%s" target="_blank">mb_decode_numericentity()</a></code> function (available since PHP v4.0.6) is missing.', NGFB_TEXTDOM ), __( 'http://php.net/manual/en/function.mb-decode-numericentity.php', NGFB_TEXTDOM ) ) . ' ' .
					__( 'This function is required to decode UTF8 entities.', NGFB_TEXTDOM ) . ' ' .
					__( 'Please update your PHP installation (hint: you may need to install the \'php-mbstring\' package on some Linux distros).', NGFB_TEXTDOM ) );
			}

			// Yoast WordPress SEO
			if ( $this->is_avail['wpseo'] == true ) {
				$wpseo_social = get_option( 'wpseo_social' );
				if ( ! empty( $wpseo_social['opengraph'] ) ) {
					$this->debug->log( 'plugin conflict detected - wpseo opengraph meta data option is enabled' );
					$this->notices->err( $conflict_prefix . sprintf( __( 'Please uncheck the \'<em>Open Graph meta data</em>\' Facebook option in the <a href="%s">Yoast WordPress SEO plugin Social settings</a>.', NGFB_TEXTDOM ), get_admin_url( null, 'admin.php?page=wpseo_social' ) ) );
				}
				if ( ! empty( $this->options['tc_enable'] ) && ! empty( $wpseo_social['twitter'] ) ) {
					$this->debug->log( 'plugin conflict detected - wpseo twitter meta data option is enabled' );
					$this->notices->err( $conflict_prefix . sprintf( __( 'Please uncheck the \'<em>Twitter Card meta data</em>\' Twitter option in the <a href="%s">Yoast WordPress SEO plugin Social settings</a>.', NGFB_TEXTDOM ), get_admin_url( null, 'admin.php?page=wpseo_social' ) ) );
				}

				if ( ! empty( $this->options['link_publisher_url'] ) && ! empty( $wpseo_social['plus-publisher'] ) ) {
					$this->debug->log( 'plugin conflict detected - wpseo google plus publisher option is defined' );
					$this->notices->err( $conflict_prefix . sprintf( __( 'Please remove the \'<em>Google Publisher Page</em>\' value entered in the <a href="%s">Yoast WordPress SEO plugin Social settings</a>.', NGFB_TEXTDOM ), get_admin_url( null, 'admin.php?page=wpseo_social' ) ) );
				}
			}

			// SEO Ultimate
			if ( $this->is_avail['seou'] == true ) {
				$seo_ultimate = get_option( 'seo_ultimate' );
				if ( ! empty( $seo_ultimate['modules'] ) && is_array( $seo_ultimate['modules'] ) ) {
					if ( array_key_exists( 'opengraph', $seo_ultimate['modules'] ) && $seo_ultimate['modules']['opengraph'] !== -10 ) {
						$this->debug->log( 'plugin conflict detected - seo ultimate opengraph module is enabled' );
						$this->notices->err( $conflict_prefix . sprintf( __( 'Please disable the \'<em>Open Graph Integrator</em>\' module in the <a href="%s">SEO Ultimate plugin Module Manager</a>.', NGFB_TEXTDOM ), get_admin_url( null, 'admin.php?page=seo' ) ) );
					}
				}
			}

			// Wordbooker
			if ( function_exists( 'wordbooker_og_tags' ) ) {
				$wordbooker_settings = get_option( 'wordbooker_settings' );
				if ( empty( $wordbooker_settings['wordbooker_fb_disable_og'] ) ) {
					$this->debug->log( 'plugin conflict detected - wordbooker opengraph is enabled' );
					$this->notices->err( $conflict_prefix . sprintf( __( 'Please check the \'<em>Disable in-line production of OpenGraph Tags</em>\' option on the <a href="%s">Wordbooker Options Page</a>.', NGFB_TEXTDOM ), get_admin_url( null, 'options-general.php?page=wordbooker' ) ) );
				}
			}

			// Facebook
  			if ( class_exists( 'Facebook_Loader' ) ) {
                                $this->debug->log( 'plugin conflict detected - facebook plugin is active' );
                                $this->notices->err( $conflict_prefix . sprintf( __( 'Please <a href="%s">deactivate the Facebook plugin</a> to prevent duplicate Open Graph meta tags in your webpage headers.', NGFB_TEXTDOM ), get_admin_url( null, 'plugins.php' ) ) );
                        }

			// AddThis Social Bookmarking Widget
			if ( defined( 'ADDTHIS_INIT' ) && ADDTHIS_INIT && ( ! empty( $this->options['ngfb_filter_content'] ) || ! empty( $this->options['ngfb_filter_excerpt'] ) ) ) {
				$this->debug->log( 'plugin conflict detected - addthis has broken excerpt / content filters' );
				$this->notices->err( $conflict_prefix . __( 'The AddThis Social Bookmarking Widget has incorrectly coded content and excerpt filters.' ) . ' ' .
					sprintf( __( 'Please uncheck the \'<em>Apply Content and Excerpt Filters</em>\' options on the <a href="%s">%s Advanced settings page</a>.', NGFB_TEXTDOM ),  $this->util->get_admin_url( 'advanced' ), $this->fullname ) );
			}

		}

		// used before any class objects are created, so keep in main class
		private function check_deps( $is_avail = array() ) {

			// ngfb pro
			$is_avail['aop'] = class_exists( 'ngfbAddOnPro' ) ? true : false;

			// available since php v4.0.6+
			$is_avail['mbdecnum'] = function_exists( 'mb_decode_numericentity' ) ? true : false;

			// php curl
			$is_avail['curl'] = function_exists( 'curl_init' ) ? true : false;

			// post thumbnail feature is supported by wp theme // since wp 2.9.0
			$is_avail['postthumb'] = function_exists( 'has_post_thumbnail' ) ? true : false;

			// nextgen gallery plugin
			// use in combination with $this->ngg_version
			$is_avail['ngg'] = class_exists( 'nggdb' ) || class_exists( 'C_NextGEN_Bootstrap' ) ? true : false;

			// woocommerce
			$is_avail['woocom'] = class_exists( 'Woocommerce' ) ? true : false;

			// by default, define any_seo value as false
			$is_avail['any_seo'] = false;

			foreach ( $this->seo_libs as $key => $name ) {
				$func_name = '';
				$class_name = '';
				switch ( $key ) {
					case 'aioseop' :
						$class_name = 'All_in_One_SEO_Pack';
						break;
					case 'seou' :
						$class_name = 'SEO_Ultimate';
						break;
					case 'wpseo' :
						$func_name = 'wpseo_init';
						break;
				}
				if ( ! empty( $func_name ) && function_exists( $func_name ) ) 
					$is_avail['any_seo'] = $is_avail[$key] = true;
				elseif ( ! empty( $class_name ) && class_exists( $class_name ) ) 
					$is_avail['any_seo'] = $is_avail[$key] = true;
				else
					$is_avail[$key] = false;
			}
			unset ( $id, $name );

			return $is_avail;
		}

		private function check_wp_version() {
			global $wp_version;
			$min_wp_version = '3.0';
			if ( version_compare( $wp_version, $min_wp_version, '<' ) ) {
				deactivate_plugins( NGFB_PLUGINBASE );
				error_log( NGFB_PLUGINBASE.' requires WordPress '.$min_wp_version.' or higher ('.$wp_version.' reported).' );
				wp_die( '<p>'. sprintf( __( 'The %1$s plugin cannot be activated - it requires WordPress %2$s or higher.' ), $this->fullname, $min_wp_version ) .'</p>' );
			}
		}

	}

        global $ngfb;
	$ngfb = new ngfbPlugin();
}

?>
