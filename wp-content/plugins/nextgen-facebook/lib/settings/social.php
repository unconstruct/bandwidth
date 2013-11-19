<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'Sorry, you cannot call this webpage directly.' );

if ( ! class_exists( 'ngfbSettingsSocialSharing' ) && class_exists( 'ngfbAdmin' ) ) {

	class ngfbSettingsSocialSharing extends ngfbAdmin {

		public $website = array();

		protected $ngfb;
		protected $menu_id;
		protected $menu_name;
		protected $pagehook;

		public function __construct( &$ngfb_plugin, $id, $name ) {
			$this->ngfb =& $ngfb_plugin;
			$this->ngfb->debug->mark();
			$this->menu_id = $id;
			$this->menu_name = $name;
			$this->setup_vars();
		}

		private function setup_vars() {
			foreach ( $this->ngfb->website_libs as $id => $name ) {
				$classname = 'ngfbSettings' . preg_replace( '/ /', '', $name );
				if ( class_exists( $classname ) )
					$this->website[$id] = new $classname( $this->ngfb );
			}
			unset ( $id, $name );
		}

		protected function add_meta_boxes() {
			// add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
			add_meta_box( $this->pagehook . '_social', 'Social Buttons', array( &$this, 'show_metabox_social' ), $this->pagehook, 'normal' );

			$col = 0;
			$row = 0;
			foreach ( $this->ngfb->website_libs as $id => $name ) {
				$col = $col == 1 ? 2 : 1;
				$row = $col == 1 ? $row + 1 : $row;
				$pos_id = 'website-row-' . $row . '-col-' . $col;
				$name = $name == 'GooglePlus' ? 'Google+' : $name;
				add_meta_box( $this->pagehook . '_' . $id, $name, array( &$this->website[$id], 'show_metabox_website' ), $this->pagehook, $pos_id );
				add_filter( 'postbox_classes_' . $this->pagehook . '_' . $this->pagehook . '_' . $id, array( &$this, 'add_class_postbox_website' ) );
			}
			$reset_ids = array_diff( array_keys( $this->ngfb->website_libs ), array( 'facebook', 'gplus' ) );
			$this->ngfb->user->reset_metaboxes( $this->pagehook, $reset_ids );
		}

		public function add_class_postbox_website( $classes ) {
			array_push( $classes, 'admin_postbox_website' );
			return $classes;
		}

		public function show_metabox_website() {
			echo '<table class="ngfb-settings">', "\n";
			foreach ( $this->get_rows() as $row ) echo '<tr>', $row, '</tr>';
			echo '</table>', "\n";
		}

		public function show_metabox_social() {
			?>
			<table class="ngfb-settings">
			<tr>
				<td colspan="3"><p>The following social buttons can be added to the content, excerpt, 
				and / or enabled within the '<?php echo ngfbWidgetSocialSharing::$fullname; ?>' widget as well 
				(<a href="<?php echo get_admin_url( null, 'widgets.php' ); ?>">see the widgets admin webpage</a>).</p></td>
			</tr><tr>
			<?php
			echo $this->ngfb->util->th( 'Location in Content Text', null, null, '
				Individual social sharing button(s) must also be enabled below.' ); 
			echo '<td>', $this->ngfb->admin->form->get_select( 'buttons_location_the_content', 
				array( 'top' => 'Top', 'bottom' => 'Bottom', 'both' => 'Both Top and Bottom' ) ), '</td>';
			echo '</tr><tr>';
			echo $this->ngfb->util->th( 'Location in Excerpt Text', null, null, '
				Individual social sharing button(s) must also be enabled below.' ); 
			echo '<td>', $this->ngfb->admin->form->get_select( 'buttons_location_the_excerpt', 
				array( 'top' => 'Top', 'bottom' => 'Bottom', 'both' => 'Both Top and Bottom' ) ), '</td>';
			echo '</tr><tr>';
			echo $this->ngfb->util->th( 'Include on Index Webpages', null, null, '
				Add the following (enabled) social sharing buttons to each entry of an index webpage 
				(non-static homepage, category, archive, etc.). 
				By Default, social sharing buttons are <em>not</em> included on index webpages 
				(default is unchecked).' ); 
			echo '<td>', $this->ngfb->admin->form->get_checkbox( 'buttons_on_index' ), '</td>';
			echo '</tr><tr>';
			echo $this->ngfb->util->th( 'Include on Static Homepage', null, null, '
				If a static Post or Page has been chosen for the homepage, add the following (enabled) 
				social sharing buttons to the static homepage as well (default is checked).' ); 
			echo '<td>', $this->ngfb->admin->form->get_checkbox( 'buttons_on_front' ), '</td>';
			echo '</tr>';
			foreach ( $this->get_more_social() as $row ) echo '<tr>' . $row . '</tr>';
			echo '</table>';
		}

		protected function get_more_social() {
			$add_to_checkboxes = '';
			foreach ( get_post_types( array( 'show_ui' => true, 'public' => true ), 'objects' ) as $post_type )
				$add_to_checkboxes .= '<p>' . $this->ngfb->admin->form->get_hidden( 'buttons_add_to_' . $post_type->name ) . ' ' . $post_type->label . '</p>';

			return array(
				'<td colspan="2" align="center">' . $this->ngfb->msg->get( 'pro_feature' ) . '</td>',

				$this->ngfb->util->th( 'Include on Post Types', null, null, '
				By default, social sharing buttons are added to the Post, Page, Media and most custom post type webpages. 
				If your theme (or another plugin) supports additional custom post types, and you would like to <em>exclude</em> the 
				social sharing buttons from these webpages, uncheck the appropriate options here.' ) .
				'<td class="blank" style="padding:4px;">' . $add_to_checkboxes . '</td>',
			);
		}
	}
}

?>
