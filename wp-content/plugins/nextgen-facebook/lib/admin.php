<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'Sorry, you cannot call this webpage directly.' );

if ( ! class_exists( 'ngfbAdmin' ) ) {

	class ngfbAdmin {
	
		public $lang = array();
		public $settings = array();

		protected $js_locations = array(
			'header' => 'Header',
			'footer' => 'Footer',
		);

		protected $captions = array(
			'none' => '',
			'title' => 'Title Only',
			'excerpt' => 'Excerpt Only',
			'both' => 'Title and Excerpt',
		);

		protected $ngfb;	// ngfbPlugin
		protected $form;	// ngfbForm
		protected $menu_id;
		protected $menu_name;
		protected $pagehook;
		protected $readme;

		public function __construct( &$ngfb_plugin ) {
			$this->ngfb =& $ngfb_plugin;
			$this->ngfb->debug->mark();
			$this->setup_vars();
			$def_opts = $this->ngfb->opt->get_defaults();
			$this->form = new ngfbForm( $this->ngfb, NGFB_OPTIONS_NAME, $this->ngfb->options, $def_opts );

			add_action( 'admin_init', array( &$this, 'register_settings' ) );
			add_action( 'admin_menu', array( &$this, 'add_admin_menus' ) );

			add_filter( 'plugin_action_links', array( &$this, 'add_plugin_links' ), 10, 2 );
		}

		private function setup_vars() {
			foreach ( $this->ngfb->setting_libs as $id => $name ) {
				$classname = 'ngfbSettings' . preg_replace( '/ /', '', $name );
				if ( class_exists( $classname ) )
					$this->settings[$id] = new $classname( $this->ngfb, $id, $name );
			}
			unset ( $id, $name );
		}

		public function set_readme( $expire_secs = false ) {
			if ( empty( $this->readme ) )
				$this->readme = $this->ngfb->util->parse_readme( $expire_secs );
		}

		public function add_admin_menus() {

			reset( $this->ngfb->setting_libs );
			$this->menu_id = key( $this->ngfb->setting_libs );
			$this->menu_name = $this->ngfb->setting_libs[$this->menu_id];
			$this->settings[$this->menu_id]->add_menu( $this->menu_id );

			foreach ( $this->ngfb->setting_libs as $id => $name )
				$this->settings[$id]->add_submenu( $this->menu_id );
			unset ( $id, $name );
		}

		protected function add_menu( $parent_id ) {
			// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
			$this->pagehook = add_menu_page( 
				$this->ngfb->fullname . ' Settings : ' . $this->menu_name, 
				$this->ngfb->menuname, 
				'manage_options', 
				$this->ngfb->acronym . '-' . $parent_id, 
				array( &$this, 'show_page' ), null, NGFB_MENU_PRIORITY);
			add_action( 'load-' . $this->pagehook, array( &$this, 'load_page' ) );
		}

		protected function add_submenu( $parent_id ) {
			// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
			$this->pagehook = add_submenu_page( 
				$this->ngfb->acronym . '-' . $parent_id, 
				$this->ngfb->fullname . ' Settings : ' . $this->menu_name, 
				$this->menu_name, 
				'manage_options', 
				$this->ngfb->acronym . '-' . $this->menu_id, 
				array( &$this, 'show_page' ) 
			);
			add_action( 'load-' . $this->pagehook, array( &$this, 'load_page' ) );
		}

		protected function add_meta_boxes() {
		}

		// display a settings link on the main plugins page
		public function add_plugin_links( $links, $file ) {
			// only add links when filter is called for this plugin
			if ( $file == NGFB_PLUGINBASE ) {
				foreach ( $links as $num => $val )
					if ( preg_match( '/>Edit</', $val ) )
						unset ( $links[$num] );
				array_push( $links, '<a href="' . $this->ngfb->util->get_admin_url( 'about' ) . '">' . __( 'About' ) . '</a>' );
				array_push( $links, '<a href="' . $this->ngfb->urls['support_forum'] . '">' . __( 'Support' ) . '</a>' );
				if ( $this->ngfb->is_avail['aop'] == false ) 
					array_push( $links, '<a href="' . $this->ngfb->urls['plugin'] . '">' . __( 'Purchase Pro' ) . '</a>' );
			}
			return $links;
		}

		public function register_settings() {
			register_setting( $this->ngfb->acronym . '_settings', NGFB_OPTIONS_NAME, array( &$this, 'sanitize_options' ) );
		} 

		// this method receives only a partial options array, so re-create a full one
		public function sanitize_options( $opts ) {

			if ( ! is_array( $opts ) ) {
				add_settings_error( NGFB_OPTIONS_NAME, 'notarray', '<b>' . $this->ngfb->acronym_uc . ' Error </b> : 
					Submitted settings are not an array.', 'error' );
				return $opts;
			}

			// get default values, including css from default stylesheets
			$def_opts = $this->ngfb->opt->get_defaults();

			// unchecked checkboxes are not provided, so re-create them here based on hidden values
			$checkbox = $this->ngfb->util->preg_grep_keys( '/^is_checkbox_/', $opts, false, true );
			foreach ( $checkbox as $key => $val ) {
				if ( ! array_key_exists( $key, $opts ) )
					$opts[$key] = 0;	// add missing checkbox as empty
				unset ( $opts['is_checkbox_'.$key] );
			}

			$opts = array_merge( $this->ngfb->options, $opts );
			$opts = $this->ngfb->opt->sanitize( $opts, $def_opts );

			// remove any options that shouldn't exist
			foreach ( $opts as $key => $val )
				if ( ! empty( $key ) && ! array_key_exists( $key, $def_opts ) )
					unset( $opts[$key] );
			unset ( $key, $val );

			// update the social stylesheet
			if ( empty( $opts['buttons_link_css'] ) ) 
				$this->ngfb->style->unlink_social();
			else $this->ngfb->style->update_social( $opts );

			add_settings_error( NGFB_OPTIONS_NAME, 'updated', '<b>' . $this->ngfb->acronym_uc . ' Info </b> : 
				Settings updated.', 'updated' );

			return $opts;
		}

		public function load_page() {
			wp_enqueue_script( 'postbox' );

			$upload_dir = wp_upload_dir();	// returns assoc array with path info
			$old_css_file = trailingslashit( $upload_dir['basedir'] ) . 'ngfb-social-buttons.css';

			if ( ! empty( $_GET['settings-updated'] ) ) {
				// we have a transaction ID, but we are not using the pro version (yet) - force an update
				if ( $this->ngfb->is_avail['aop'] == false && ! empty( $this->ngfb->options['ngfb_pro_tid'] ) )
					$this->ngfb->update->check_for_updates();
			}

			if ( ! empty( $_GET['action'] ) ) {
				switch ( $_GET['action'] ) {
					case 'remove_old_css' : 
						if ( file_exists( $old_css_file ) )
							if ( @unlink( $old_css_file ) )
								add_settings_error( NGFB_OPTIONS_NAME, 'cssnotrm', 
									'<b>' . $this->ngfb->acronym_uc . '</b> : The old <u>' . $old_css_file . '</u> 
										stylesheet has been removed.', 'updated' );
							else
								add_settings_error( NGFB_OPTIONS_NAME, 'cssnotrm', 
									'<b>' . $this->ngfb->acronym_uc . '</b> : Error removing the old <u>' . $old_css_file . '</u> 
										stylesheet. Does the web server have sufficient privileges?', 'error' );

						break;
					case 'check_for_updates' : 
						if ( ! empty( $this->ngfb->options['ngfb_pro_tid'] ) ) {
							$this->ngfb->update->check_for_updates();
							$this->ngfb->admin->set_readme( 0 );
							$this->ngfb->notices->inf( 'Plugin update information has been checked and updated.' );
						}
						break;
					case 'clear_all_cache' : 
						$deleted_cache = $this->ngfb->util->delete_expired_file_cache( true );
						$deleted_transient = $this->ngfb->util->delete_expired_transients( true );
						wp_cache_flush();
						if ( function_exists('w3tc_pgcache_flush') ) 
							w3tc_pgcache_flush();
						elseif ( function_exists('wp_cache_clear_cache') ) 
							wp_cache_clear_cache();
						$this->ngfb->notices->inf( 'Cached files, WP object cache, transient cache, and any 
							additional caches, like APC, Memcache, Xcache, W3TC, Super Cache, etc. have all been cleared.' );
						break;
				}
			}

			$this->ngfb->admin->set_readme();	// the version info metabox on all settings pages needs this

			foreach ( $this->ngfb->setting_libs as $id => $name )
				$this->ngfb->admin->settings[$id]->add_meta_boxes();

			add_meta_box( $this->pagehook . '_news', 'News Feed', array( &$this, 'show_metabox_news' ), $this->pagehook, 'side' );
			add_meta_box( $this->pagehook . '_info', 'Plugin Information', array( &$this, 'show_metabox_info' ), $this->pagehook, 'side' );
			add_meta_box( $this->pagehook . '_help', 'Help and Support', array( &$this, 'show_metabox_help' ), $this->pagehook, 'side' );

			if ( file_exists( $old_css_file ) ) {
				$this->ngfb->notices->inf( 
					sprintf( __( 'The <u>%s</u> stylesheet is no longer used.', 
						NGFB_TEXTDOM ), $old_css_file ) . ' ' .
					sprintf( __( 'Styling for social buttons is now managed <a href="%s">on the Social Style settings page</a>.', 
						NGFB_TEXTDOM ), $this->ngfb->util->get_admin_url( 'style' ) ) . ' ' .
					sprintf( __( 'When you are ready, you can <a href="%s">click this link to remove the old stylesheet</a>.', 
						NGFB_TEXTDOM ), $this->ngfb->util->get_admin_url( '?action=remove_old_css' ) ) 
				);
			}

			if ( $this->ngfb->is_avail['aop'] == true )
				add_meta_box( $this->pagehook . '_thankyou', 'Pro Installed', array( &$this, 'show_metabox_thankyou' ), $this->pagehook, 'side' );

		}

		public function show_page() {
			settings_errors( NGFB_OPTIONS_NAME );	// display "error" and "updated" messages

			$this->ngfb->debug->show_html( null, 'Debug Log' );

			// add meta box here to prevent removal
			if ( $this->ngfb->is_avail['aop'] !== true ) {
				add_meta_box( $this->pagehook . '_purchase', 'Pro Version', array( &$this, 'show_metabox_purchase' ), $this->pagehook, 'side' );
				add_filter( 'postbox_classes_' . $this->pagehook . '_' . $this->pagehook . '_purchase', array( &$this, 'add_class_postbox_purchase_side' ) );
			}

			?>
			<div class="wrap" id="<?php echo $this->pagehook; ?>">
				<?php screen_icon('options-general'); ?>
				<h2><?php echo $this->ngfb->fullname; ?></h2>
				<div id="poststuff" class="metabox-holder <?php echo 'has-right-sidebar'; ?>">
					<div id="side-info-column" class="inner-sidebar">
						<?php do_meta_boxes( $this->pagehook, 'side', null ); ?>
					</div><!-- .inner-sidebar -->
					<div id="post-body" class="has-sidebar">
						<div id="post-body-content" class="has-sidebar-content">
							<?php $this->show_form(); ?>
						</div><!-- .has-sidebar-content -->
					</div><!-- .has-sidebar -->
				</div><!-- .metabox-holder -->
			</div><!-- .wrap -->
			<script type="text/javascript">
				//<![CDATA[
					jQuery(document).ready( 
						function($) {
							// close postboxes that should be closed
							$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
							// postboxes setup
							postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
						}
					);
				//]]>
			</script>
			<?php
		}

		public function add_class_postbox_purchase_side( $classes ) {
			array_push( $classes, 'postbox_purchase_side' );
			return $classes;
		}

		protected function show_form() {
			echo '<form name="ngfb" method="post" action="options.php" id="settings">', "\n";
			settings_fields( $this->ngfb->acronym . '_settings' ); 
			wp_nonce_field( plugin_basename( __FILE__ ), NGFB_NONCE );
			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );

			// always include the version number of the options
			echo $this->ngfb->admin->form->get_hidden( 'ngfb_opts_ver', $this->ngfb->opt->opts_ver );
			echo $this->ngfb->admin->form->get_hidden( 'ngfb_plugin_ver', $this->ngfb->version );
			do_meta_boxes( $this->pagehook, 'normal', null ); 
			foreach ( range( 1, ceil( count( $this->ngfb->website_libs ) / 2 ) ) as $row ) {
				echo '<div class="website-row">', "\n";
				foreach ( range( 1, 2 ) as $col ) {
					$pos_id = 'website-row-' . $row . '-col-' . $col;
					echo '<div class="website-col-', $col, '" id="', $pos_id, '" >';
					do_meta_boxes( $this->pagehook, $pos_id, null ); 
					echo '</div>', "\n";
				}
				echo '</div>';
			}
			echo '<div style="clear:both;"></div>';
			do_meta_boxes( $this->pagehook, 'bottom', null ); 
			echo $this->get_submit_button();
			echo '</form>', "\n";
		}

		protected function get_submit_button( $submit_text = '', $class = 'save-all-button' ) {
			if ( empty( $submit_text ) ) 
				$submit_text = __( 'Save All Changes', NGFB_TEXTDOM );
			return '<div class="'.$class.'"><input type="submit" class="button-primary" value="'.$submit_text.'" /></div>' . "\n";
		}

		public function show_metabox_news() {
			$this->show_feed( $this->ngfb->urls['feed'], 3, 'ngfb_feed' );
		}

		protected function show_feed( $url, $max_num = 5, $class = 'rss_feed' ) {
			include_once( ABSPATH . WPINC . '/feed.php' );
			$have_items = 0;
			$rss_items = array();

			add_filter( 'wp_feed_cache_transient_lifetime' , array( &$this, 'feed_cache_expire' ) );
			$rss_feed = fetch_feed( $url );		// since wp 2.8
			remove_filter( 'wp_feed_cache_transient_lifetime' , array( &$this, 'feed_cache_expire' ) );

			if ( ! is_wp_error( $rss_feed ) ) {
				$have_items = $rss_feed->get_item_quantity( $max_num ); 
				$rss_items = $rss_feed->get_items( 0, $have_items );
			}
			echo '<div class="', $class, '"><ul>', "\n";
			if ( $have_items == 0 ) {
				echo '<li>', __( 'No items found.', NGFB_TEXTDOM ), '</li>', "\n";
			} else {
				foreach ( $rss_items as $item ) {
					$desc = $item->get_description();
					$desc = preg_replace( '/^\.rss-manager [^<]*/m', '', $desc );		// remove the inline styling
					$desc = preg_replace( '/ cellspacing=["\'][0-9]*["\']/im', '', $desc );	// remove table cellspacing
					echo '<li><div class="title"><a href="', esc_url( $item->get_permalink() ), '" title="', 
						printf( 'Posted %s', $item->get_date('j F Y | g:i a') ), '">',
						esc_html( $item->get_title() ), '</a></div><div class="description">', 
						$desc, '</div></li>', "\n";
				}
			}
			echo '</ul></div>', "\n";
		}

		public function feed_cache_expire( $seconds ) {
			return $this->ngfb->update_hours * 60 * 60;
		}

		public function show_metabox_info() {
			$stable_tag = 'N/A';
			$latest_version = 'N/A';
			$latest_notice = '';
			if ( ! empty( $this->ngfb->admin->readme['stable_tag'] ) ) {
				$stable_tag = $this->ngfb->admin->readme['stable_tag'];
				$upgrade_notice = $this->ngfb->admin->readme['upgrade_notice'];
				if ( is_array( $upgrade_notice ) ) {
					reset( $upgrade_notice );
					$latest_version = key( $upgrade_notice );
					$latest_notice = $upgrade_notice[$latest_version];
				}
			}
			?>
			<table class="ngfb-settings">
			<tr><th class="side">Installed:</th>
			<td><?php 
				echo $this->ngfb->version; 
				echo $this->ngfb->is_avail['aop'] ? ' (Pro)' : ' (<em>Free</em>)'; 
			?></td></tr>
			<tr><th class="side">Stable:</th><td><?php echo $stable_tag; ?></td></tr>
			<tr><th class="side">Latest:</th><td><?php echo $latest_version; ?></td></tr>
			<tr>
				<td colspan="2" id="latest_notice">
					<p><?php echo $latest_notice; ?></p>
					<p><?php echo $this->ngfb->util->get_admin_url( 'about', 
						__( 'See the Changelog for additional details...', NGFB_TEXTDOM ) ); ?></p>
				</td>
			</tr>
			<?php
			echo '<tr><td colspan="2">';
			echo '<p class="centered">';
			if ( ! empty( $this->ngfb->options['ngfb_pro_tid'] ) )
				echo $this->ngfb->admin->form->get_button( __( 'Check for Updates', NGFB_TEXTDOM ), 
					'button-primary', null, $this->ngfb->util->get_admin_url() . '&amp;action=check_for_updates' );
			echo $this->ngfb->admin->form->get_button( __( 'Clear All Cache', NGFB_TEXTDOM ), 
				'button-primary', null, $this->ngfb->util->get_admin_url() . '&amp;action=clear_all_cache' );
			echo '</p></td></tr></table>';
		}

		public function show_metabox_purchase() {
			echo '<table class="ngfb-settings"><tr><td>';
			echo $this->ngfb->msg->get( 'purchase_box' ), "\n";
			echo $this->ngfb->msg->get( 'review_plugin' ), "\n";
			echo '<p>Thank you,</p>', "\n";
			echo '<p class="sig">js.</p>', "\n";
			echo '<p class="centered">';
			echo $this->ngfb->admin->form->get_button( __( 'Purchase the Pro Version', NGFB_TEXTDOM ), 
				'button-primary', null, $this->ngfb->urls['plugin'] );
			echo '</p></td></tr></table>';
		}

		public function show_metabox_thankyou() {
			echo '<table class="ngfb-settings"><tr><td>';
			echo $this->ngfb->msg->get( 'thankyou' ), "\n";
			echo '<p class="sig">js.</p>', "\n";
			echo '</td></tr></table>';
		}

		public function show_metabox_help() {
			echo '<table class="ngfb-settings"><tr><td>';
			echo $this->ngfb->msg->get( 'help_boxes' ), "\n";
			if ( $this->ngfb->is_avail['aop'] == true )
				echo $this->ngfb->msg->get( 'help_email' ), "\n";
			else
				echo $this->ngfb->msg->get( 'help_forum' ), "\n";
			echo '</td></tr></table>';
		}

	}
}

?>
