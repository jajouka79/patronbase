<?php
/**
 * Plugin Name: PatronBase UK XML Feed Worker
 * Version: 1.0.0
 * Author: Thom Smith
 * Author URI: http://about.me/thomsmith
 */

 // Register Productions Custom Post Type
function production_post_type() {

	$labels = array(
		'name'                => _x( 'Production', 'Post Type General Name', 'text_domain' ),
		'singular_name'       => _x( 'Production', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'           => __( 'Productions', 'text_domain' ),
		'parent_item_colon'   => __( 'Parent Production:', 'text_domain' ),
		'all_items'           => __( 'All Productions', 'text_domain' ),
		'view_item'           => __( 'View Production', 'text_domain' ),
		'add_new_item'        => __( 'Add New Production', 'text_domain' ),
		'add_new'             => __( 'Add New', 'text_domain' ),
		'edit_item'           => __( 'Edit Production', 'text_domain' ),
		'update_item'         => __( 'Update Production', 'text_domain' ),
		'search_items'        => __( 'Search Production', 'text_domain' ),
		'not_found'           => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
	);
	$args = array(
		'label'               => __( 'productions', 'text_domain' ),
		'description'         => __( 'PatronBase Productions', 'text_domain' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'thumbnail', 'revisions', 'custom-fields', ),
		'taxonomies'          => array( 'category', 'post_tag' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'post',
	);
	register_post_type( 'productions', $args );

}

// Hook into the 'init' action
add_action( 'init', 'production_post_type', 0 );
 
// Modify query to order by event date
function order_events_by_date( $query ) {
	
	if( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] <> 'nav_menu_item' &&!is_admin() ) {
		$query->set( 'post_type', 'productions');
		$query->set( 'meta_key', 'date_from' );
		$query->set( 'orderby', 'meta_value_num');
		$query->set( 'order', 'ASC');
	} 
}
add_action( 'pre_get_posts', 'order_events_by_date' );

// Register [productionmeta] Shortcode
function productionmetabox() {

	$postid = get_the_id();
	
	if ( get_post_meta($postid, 'date_from') | get_post_meta($postid, 'venue') | get_post_meta($postid, 'pricing_max') | get_post_meta($postid, 'bookonline') ) {
		
		echo '<div class="event_meta">';
		
		if ( get_post_meta($postid, 'date_from') ) {
			switch ( date( "D j M", strtotime( get_post_meta($postid, 'date_from', true) ) ) === date( "D j M", strtotime( get_post_meta($postid, 'date_to', true) ) ) ) {
				case true:
					echo '<strong>Date</strong><br />'.date( "D j M @ g:i a", strtotime( get_post_meta($postid, 'date_from', true) ) ).'<br />';
					break;
				case false:
					echo '<strong>Date</strong><br />'.date( "D j M", strtotime( get_post_meta($postid, 'date_from', true) ) ).' - '.date( "D j M", strtotime( get_post_meta($postid, 'date_to', true) ) ).'<br />';
					break;
				default:
					echo '<strong>Date</strong><br />'.date( "D j M", strtotime( get_post_meta($postid, 'date_from', true) ) ).' - '.date( "D j M", strtotime( get_post_meta($postid, 'date_to', true) ) ).'<br />';
			}
		}
		
		if ( get_post_meta($postid, 'venue') ) {echo '<strong>Venue</strong><br />'.get_post_meta($postid, 'venue', true).'<br />' ; }

		$rating_meta = get_post_meta($postid, 'rating', true);
		if ( !empty($rating_meta) ) { echo '<strong>Age Rating</strong><br />'.get_post_meta($postid, 'rating', true).'<br />' ; }
		
		if ( get_post_meta($postid, 'pricing_max') ) {
			switch ( get_post_meta($postid, 'pricing_min', true) === get_post_meta($postid, 'pricing_max', true) ) {
				case true:
					echo '<strong>Tickets</strong><br />&pound;'.get_post_meta($postid, 'pricing_max', true).'<br />';
					break;
				case false:
					echo '<strong>Tickets</strong><br />&pound;'.get_post_meta($postid, 'pricing_min', true).' - &pound;'.get_post_meta($postid, 'pricing_max', true).'<br />';
					break;
				default:
					echo '<strong>Tickets</strong><br />&pound;'.get_post_meta($postid, 'pricing_min', true).' - &pound;'.get_post_meta($postid, 'pricing_max', true).'<br />';
			}
		}

		echo do_shortcode( '[bookbtn]Book now[/bookbtn]');

		echo '</div>';
	}
}
add_shortcode( 'productionmeta', 'productionmetabox' );

// Register [productiondates] Shortcode
function production_dates() {

	$postid = get_the_id();

	switch ( date( "D j M", strtotime( get_post_meta($postid, 'date_from', true) ) ) === date( "D j M", strtotime( get_post_meta($postid, 'date_to', true) ) ) ) {
		case true:
			return date( "D j M", strtotime( get_post_meta($postid, 'date_from', true) ) );
			break;
		case false:
			return date( "D j M", strtotime( get_post_meta($postid, 'date_from', true) ) ).' - '.date( "D j M", strtotime( get_post_meta($postid, 'date_to', true) ) );
			break;
		default:
			return date( "D j M", strtotime( get_post_meta($postid, 'date_from', true) ) ).' - '.date( "D j M", strtotime( get_post_meta($postid, 'date_to', true) ) );
	}
}
add_shortcode( 'productiondates', 'production_dates' );
 
 // Register [productionpricing] Shortcode
function production_pricing() {

	$postid = get_the_id();

	switch ( get_post_meta($postid, 'pricing_min', true) === get_post_meta($postid, 'pricing_max', true) ) {
		case true:
			echo '&pound;'.get_post_meta($postid, 'pricing_max', true);
			break;
		case false:
			echo '&pound;'.get_post_meta($postid, 'pricing_min', true).' - &pound;'.get_post_meta($postid, 'pricing_max', true);
			break;
		default:
			echo '&pound;'.get_post_meta($postid, 'pricing_min', true).' - &pound;'.get_post_meta($postid, 'pricing_max', true);
	}
}
add_shortcode( 'productionpricing', 'production_pricing' );

// Register [bookbtn] Shortcode
function book_button( $atts , $content = null ) {

	$postid = get_the_ID();

	$currentstate = get_post_meta($postid, 'visibility',true);

	switch ($currentstate) {
		case 0:
			//hidden
			break;
		case 1:
			//visible
			echo '<a href="#" id="bookbtn">On sale from '.date( "D j M @ g:i a", strtotime( get_post_meta($postid, 'onsale_date', true) ) ).'</a>';
			break;
		case 2:
			//on sale
			echo '<a href="'.get_post_meta($postid, 'bookonline', true).'" id="bookbtn">'.$content.'</a>';
			break;
		case 3:
			//not on sale
			echo '<a href="#" id="bookbtn">Not on sale</a>';
			break;
		default:
			//default to booking button linking to webskin
			echo '<a href="'.get_post_meta($postid, 'bookonline', true).'" id="bookbtn">'.$content.'</a>';
	}
}
add_shortcode( 'bookbtn', 'book_button' );

// Add [rating] Shortcode
function production_rating() {

	$postid = get_the_id();

	echo get_post_meta($postid, 'rating', true);
}
add_shortcode( 'rating', 'production_rating' );
 
 // Add [venue] Shortcode
function production_venue() {

	$postid = get_the_id();

	echo get_post_meta($postid, 'venue', true);
}
add_shortcode( 'venue', 'production_venue' );

// Add [department] Shortcode
function production_department() {

	$postid = get_the_id();

	echo get_post_meta($postid, 'department', true);
}
add_shortcode( 'department', 'production_department' );

// Add [project] Shortcode
function production_project() {

	$postid = get_the_id();

	echo get_post_meta($postid, 'project', true);
}
add_shortcode( 'project', 'production_project' );

// Add [promoter] Shortcode
function production_promoter() {

	$postid = get_the_id();

	echo get_post_meta($postid, 'promoter', true);
}
add_shortcode( 'promoter', 'production_promoter' );

// Add [type] Shortcode
function production_type() {

	$postid = get_the_id();

	echo get_post_meta($postid, 'type', true);
}
add_shortcode( 'type', 'production_type' );
 
 ?>