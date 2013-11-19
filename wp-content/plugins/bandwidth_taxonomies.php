<?php
/*
Plugin Name: Bandwidth Taxonomies
Plugin URI: http://wamu.org
Description: Custom Taxonomies for Bandwidth
Author: Chris Baronavski
Version: 0.1
Author URI: http://rep.tilio.us
*/
add_action('init', 'add_bandwidth_taxonomies',0);	

function add_bandwidth_taxonomies(){
	//Register genre taxonomy
	register_taxonomy('genre', 'post',array(
		'hierarchical' => true,
		'labels' => array(
				'name' => _x('Genres','taxonomy general name'),
				'singular_name' => _x('Genre', 'taxonomy singular name'),
				'search_items' => __('Search Genres'),
				'all_items' => __('All Genres'),
				'parent_item' => __('Parent Genre'),
				'parent_item_colon' => __('Parent Genre:'),
				'edit_item' => __('Edit Genre'),
				'update_item' => __('Update Genre'),
				'add_new_item' => __('Add New Genre'),
				'new_item_name' => __('New Genre Name'),
				'menu_name' => ('Genres'),		
		
		),
		'rewrite' => array(
				'slug' => 'genres',
				'with_front' => true,
				'hierarchical' => true
		),
	
	));
	//Register artist taxonomy
	register_taxonomy('artist', 'post',array(
		'hierarchical' => false,
		'labels' => array(
				'name' => _x('Artists','taxonomy general name'),
				'singular_name' => _x('Artist', 'taxonomy singular name'),
				'search_items' => __('Search Artists'),
				'all_items' => __('All Artists'),
				//'parent_item' => __('Parent Artist'),
				//'parent_item_colon' => __('Parent Artist:'),
				'edit_item' => __('Edit Artist'),
				'update_item' => __('Update Artist'),
				'add_new_item' => __('Add New Artist'),
				'new_item_name' => __('New Artist Name'),
				'menu_name' => ('Artists'),		
		
		),
		'rewrite' => array(
				'slug' => 'artists',
				'with_front' => false,
				'hierarchical' => false
		),
	
	));
	//Register contributor taxonomy
	register_taxonomy('contributor', 'post',array(
		'hierarchical' => false,
		'labels' => array(
				'name' => _x('Contributors','taxonomy general name'),
				'singular_name' => _x('Contributor', 'taxonomy singular name'),
				'search_items' => __('Search Contributors'),
				'all_items' => __('All Contributors'),
				//'parent_item' => __('Parent A'),
				//'parent_item_colon' => __('Parent Artist:'),
				'edit_item' => __('Edit Contributor'),
				'update_item' => __('Update Contributor'),
				'add_new_item' => __('Add New Contributor'),
				'new_item_name' => __('New Contributor Name'),
				'menu_name' => ('Contributors'),		
		
		),
		'rewrite' => array(
				'slug' => 'contributors',
				'with_front' => false,
				'hierarchical' => false
		),
	
	));
	//Register series taxonomy
	register_taxonomy('series', 'post',array(
		'hierarchical' => true,
		'labels' => array(
				'name' => _x('Series','taxonomy general name'),
				'singular_name' => _x('Series', 'taxonomy singular name'),
				'search_items' => __('Search Series'),
				'all_items' => __('All Series'),
				'parent_item' => __('Parent Series'),
				'parent_item_colon' => __('Parent Series:'),
				'edit_item' => __('Edit Series'),
				'update_item' => __('Update Series'),
				'add_new_item' => __('Add New Series'),
				'new_item_name' => __('New Series Name'),
				'menu_name' => ('Series'),		
		
		),
		'rewrite' => array(
				'slug' => 'series',
				'with_front' => false,
				'hierarchical' => true
		),
	
	));
}

add_action('admin_head', 'wpds_admin_head');
add_action('edit_term', 'wpds_save_tax_pic');
add_action('create_term', 'wpds_save_tax_pic');
function wpds_admin_head() {
    //$taxonomies = get_taxonomies();
    $taxonomies = array('artist','contributor'); // uncomment and specify particular taxonomies you want to add image feature.
    if (is_array($taxonomies)) {
        foreach ($taxonomies as $z_taxonomy) {
            add_action($z_taxonomy . '_add_form_fields', 'wpds_tax_field');
            add_action($z_taxonomy . '_edit_form_fields', 'wpds_tax_field');
        }
    }
}

// add image field in add form
function wpds_tax_field($taxonomy) {
    wp_enqueue_style('thickbox');
    wp_enqueue_script('thickbox');
    if(empty($taxonomy)) {
        echo '<div class="form-field">
                <label for="wpds_tax_pic">Picture</label>
                <input type="text" name="wpds_tax_pic" id="wpds_tax_pic" value="" />
            </div>';
    }
    else{
        $wpds_tax_pic_url = get_option('wpds_tax_pic' . $taxonomy->term_id);
        echo '<tr class="form-field">
		<th scope="row" valign="top"><label for="wpds_tax_pic">Picture</label></th>
		<td><input type="text" name="wpds_tax_pic" id="wpds_tax_pic" value="' . $wpds_tax_pic_url . '" /><br />';
        if(!empty($wpds_tax_pic_url))
            echo '<img src="'.$wpds_tax_pic_url.'" style="max-width:200px;border: 1px solid #ccc;padding: 5px;box-shadow: 5px 5px 10px #ccc;margin-top: 10px;" >';
        echo '</td></tr>';        
    }
    echo '<script type="text/javascript">
	    jQuery(document).ready(function() {
                jQuery("#wpds_tax_pic").click(function() {
                    tb_show("", "media-upload.php?type=image&amp;TB_iframe=true");
                    return false;
                });
                window.send_to_editor = function(html) {
                    jQuery("#wpds_tax_pic").val( jQuery("img",html).attr("src") );
                    tb_remove();
                }
	    });
	</script>';
}

// save our taxonomy image while edit or save term
function wpds_save_tax_pic($term_id) {
    if (isset($_POST['wpds_tax_pic']))
        update_option('wpds_tax_pic' . $term_id, $_POST['wpds_tax_pic']);
}

// output taxonomy image url for the given term_id (NULL by default)
function wpds_tax_pic_url($term_id = NULL) {
    if ($term_id) 
        return get_option('wpds_tax_pic' . $term_id);
    elseif (is_category())
        return get_option('wpds_tax_pic' . get_query_var('cat')) ;
    elseif (is_tax()) {
        $current_term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
        return get_option('wpds_tax_pic' . $current_term->term_id);
    }
}

//add custom metadata to attachments
add_filter('attachment_fields_to_edit', 'edit_media_custom_field', 11, 2 );
add_filter('attachment_fields_to_save', 'save_media_custom_field', 11, 2 );

function edit_media_custom_field( $form_fields, $post ) {
    $form_fields['producer'] = array( 'label' => 'Producer', 'input' => 'text', 'value' => get_post_meta( $post->ID, '_producer', true ) );
    $form_fields['provider'] = array( 'label' => 'Provider', 'input' => 'text', 'value' => get_post_meta( $post->ID, '_provider', true ) );
    $form_fields['provider_url'] = array( 'label' => 'Provider URL', 'input' => 'text', 'value' => get_post_meta( $post->ID, '_provider_url', true ) );
    $form_fields['link'] = array( 'label' => 'Link', 'input' => 'text', 'value' => get_post_meta( $post->ID, '_link', true ) );
    $form_fields['copyright'] = array( 'label' => 'Copyright', 'input' => 'text', 'value' => get_post_meta( $post->ID, '_copyright', true ) );
    
    return $form_fields;
}

function save_media_custom_field( $post, $attachment ) {
    update_post_meta( $post['ID'], '_producer', $attachment['producer'] );
    update_post_meta( $post['ID'], '_provider', $attachment['provider'] );
    update_post_meta( $post['ID'], '_provider_url', $attachment['provider_url'] );
    update_post_meta( $post['ID'], '_link', $attachment['link'] );
    update_post_meta( $post['ID'], '_copyright', $attachment['copyright'] );

    return $post;
}

