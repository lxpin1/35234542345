<?php

add_action( 'init', 'belingoGeo_register_post_types' );
function belingoGeo_register_post_types() {

	// Города
	$cities_args = [
		'label'              => __('Cities', 'belingogeo'),
		'labels' => [
			'name'               => __('Cities', 'belingogeo'),
			'singular_name'      => __('City', 'belingogeo'),
			'add_new'            => __('Add city', 'belingogeo'),
			'add_new_item'       => __('Adding city', 'belingogeo'),
			'edit_item'          => __('Edit city', 'belingogeo'),
			'new_item'           => __('New city', 'belingogeo'),
			'view_item'          => __('Show city', 'belingogeo'),
			'search_items'       => __('Search city', 'belingogeo'),
			'not_found'          => __('Not found', 'belingogeo'),
			'not_found_in_trash' => __('Not found in trash', 'belingogeo'),
			'parent_item_colon'  => '',
			'menu_name'          => __('Cities', 'belingogeo'),
		],
		'public'             => false,
		'has_archive'        => false,
		'show_ui'			 => true,
		'show_in_menu'		 => 'belingo_geo_settings.php',
		'supports'           => [ 'title' ],
		'rewrite'            => false,
	];
	register_post_type( 'cities', $cities_args );

	// Regions
	register_taxonomy( 'bg_regions', 'cities', [
		'label'                 => __('Regions', 'belingogeo'),
		'labels'                => [
			'name'              => __('Regions', 'belingogeo'),
			'singular_name'     => __('Region', 'belingogeo'),
			'search_items'      => __('Search regions', 'belingogeo'),
			'all_items'         => __('All regions', 'belingogeo'),
			'view_item '        => __('View regions', 'belingogeo'),
			'parent_item'       => __('Parent region', 'belingogeo'),
			'parent_item_colon' => __('Parent region:', 'belingogeo'),
			'edit_item'         => __('Edit region', 'belingogeo'),
			'update_item'       => __('Update region', 'belingogeo'),
			'add_new_item'      => __('Add new region', 'belingogeo'),
			'new_item_name'     => __('New region name', 'belingogeo'),
			'menu_name'         => __('Region', 'belingogeo'),
			'back_to_items'     => __('← Back to region', 'belingogeo')
		],
		'description'           => '',
		'public'                => false,
		'show_ui'               => true,
		'show_in_menu'          => 'belingo_geo_settings.php',
		'hierarchical'          => false,

		'rewrite'               => false,
		'capabilities'          => array(),
		'meta_box_cb'           => null,
		'show_admin_column'     => true,
		'show_in_rest'          => null,
		'rest_base'             => null
	] );

}

add_action( "admin_init", "belingoGeo_cities_fields", 1 );
function belingoGeo_cities_fields() {
	add_meta_box( "cities_eng_field", __('In English', 'belingogeo'), "belingoGeo_cities_eng_field", "cities", "normal", "high" );
	add_meta_box( "cities_padej1_field", __('Prepositional', 'belingogeo'), "belingoGeo_cities_padej1_field", "cities", "normal", "high" );
	add_meta_box( "cities_padej2_field", __('Dative', 'belingogeo'), "belingoGeo_cities_padej2_field", "cities", "normal", "high" );
	add_meta_box( "cities_padej3_field", __('Genitive', 'belingogeo'), "belingoGeo_cities_padej3_field", "cities", "normal", "high" );
	add_meta_box( "cities_phone_field", __('Telephone', 'belingogeo'), "belingoGeo_cities_phone_field", "cities", "normal", "high" );
	add_meta_box( "cities_address_field", __('Address', 'belingogeo'), "belingoGeo_cities_address_field", "cities", "normal", "high" );
	add_meta_box( "cities_addon_contacts_field", __('Additional points', 'belingogeo'), "belingoGeo_cities_addon_contacts_field", "cities", "normal", "low" );
}

function belingoGeo_cities_eng_field() {
	global $post;

	$custom   = get_post_custom( $post->ID );
	if(isset($custom["city_eng"][0])) {
		$city_eng = $custom["city_eng"][0];
	}else{
		$city_eng = '';
	}
	echo '<input type="text" style="width:100%;" name="city_eng" value="' . htmlentities( $city_eng ) . '">';

}

function belingoGeo_cities_padej1_field() {
	global $post;

	$custom      = get_post_custom( $post->ID );
	if(isset($custom["city_padej1"][0])) {
		$city_padej1 = $custom["city_padej1"][0];
	}else{
		$city_padej1 = '';
	}
	echo '<input type="text" style="width:100%;" name="city_padej1" value="' . htmlentities( $city_padej1 ) . '">';

}

function belingoGeo_cities_padej2_field() {
	global $post;

	$custom      = get_post_custom( $post->ID );
	if(isset($custom["city_padej2"][0])) {
		$city_padej2 = $custom["city_padej2"][0];
	}else{
		$city_padej2 = '';
	}
	echo '<input type="text" style="width:100%;" name="city_padej2" value="' . htmlentities( $city_padej2 ) . '">';

}

function belingoGeo_cities_padej3_field() {
	global $post;

	$custom      = get_post_custom( $post->ID );
	if(isset($custom["city_padej3"][0])) {
		$city_padej3 = $custom["city_padej3"][0];
	}else{
		$city_padej3 = '';
	}
	echo '<input type="text" style="width:100%;" name="city_padej3" value="' . htmlentities( $city_padej3 ) . '">';

}

function belingoGeo_cities_phone_field() {
	global $post;

	$custom     = get_post_custom( $post->ID );
	if(isset($custom["city_phone"][0])) {
		$city_phone = $custom["city_phone"][0];
	}else{
		$city_phone = '';
	}
	echo '<input type="text" style="width:100%;" name="city_phone" value="' . htmlentities( $city_phone ) . '"><br>
	<sub>+7 (XXX) XXX-XX-XX</sub>';

}

function belingoGeo_cities_address_field() {
	global $post;

	$custom       = get_post_custom( $post->ID );
	if(isset($custom["city_address"][0])) {
		$city_address = $custom["city_address"][0];
	}else{
		$city_address = '';
	}
	echo '<input type="text" style="width:100%;" name="city_address" value="' . htmlentities( $city_address ) . '">';

}

function belingoGeo_cities_addon_contacts_field() {
	global $post;

	$custom              = get_post_custom( $post->ID );
	if(isset($custom["city_addon_contacts"][0])) {
		$city_addon_contacts = json_decode( $custom["city_addon_contacts"][0] );
	}else{
		$city_addon_contacts = [];
	}
	echo '<script>';
	echo 'jQuery(document).ready(function() {';
	echo ' jQuery(".remove-point").click(function(e) {';
	echo '	e.preventDefault();';
	echo '	jQuery(this).parent().parent().remove();';
	echo ' });';
	echo ' jQuery("#add_point_btn").click(function(e) {';
	echo '	e.preventDefault();';
	echo ' 	jQuery(".point_row:last-child").after("';
	echo '<tr class=\'point_row\'>';
	echo '<td><input placeholder=\'Название точки\' type=\'text\' style=\'width:100%;\' name=\'addon_contact_name[]\' value=\'\'></td>';
	echo '<td><input placeholder=\'Телефон точки\' type=\'text\' style=\'width:100%;\' name=\'addon_contact_phone[]\' value=\'\'></td>';
	echo '<td><input placeholder=\'Адрес точки\' type=\'text\' style=\'width:100%;\' name=\'addon_contact_address[]\' value=\'\'></td>';
	echo '<td><input placeholder=\'Режим работы точки\' type=\'text\' style=\'width:100%;\' name=\'addon_contact_time[]\' value=\'\'></td>';
	echo '<td style=\'text-align: right;\'><button style=\'color:red;border-color:red;\' class=\'button remove-point\'>Удалить</button></td>';
	echo '</tr>';
	echo '");';
	echo ' jQuery(".remove-point").click(function(e) {';
	echo '	e.preventDefault();';
	echo '	jQuery(this).parent().parent().remove();';
	echo ' });';
	echo ' });';
	echo '});';
	echo '</script>';
	echo '<table border="0" style="width:100%;">';
	if ( $city_addon_contacts ) {
		foreach ( $city_addon_contacts as $key => $value ) {
			echo '<tr class="point_row">';
			echo '<td><input placeholder="Название точки" type="text" style="width:100%;" name="addon_contact_name[]" value="' . base64_decode( $value->addon_contact_name ) . '"></td>';
			echo '<td><input placeholder="Телефон точки" type="text" style="width:100%;" name="addon_contact_phone[]" value="' . base64_decode( $value->addon_contact_phone ) . '"></td>';
			echo '<td><input placeholder="Адрес точки" type="text" style="width:100%;" name="addon_contact_address[]" value="' . base64_decode( $value->addon_contact_address ) . '"></td>';
			echo '<td><input placeholder="Режим работы точки" type="text" style="width:100%;" name="addon_contact_time[]" value="' . base64_decode( $value->addon_contact_time ) . '"></td>';
			echo '<td style="text-align: right;"><button style="color:red;border-color:red;" class="button remove-point">Удалить</button></td>';
			echo '</tr>';
		}
	} else {
		echo '<tr class="point_row">';
		echo '<td><input placeholder="Название точки" type="text" style="width:100%;" name="addon_contact_name[]" value=""></td>';
		echo '<td><input placeholder="Телефон точки" type="text" style="width:100%;" name="addon_contact_phone[]" value=""></td>';
		echo '<td><input placeholder="Адрес точки" type="text" style="width:100%;" name="addon_contact_address[]" value=""></td>';
		echo '<td><input placeholder="Режим работы точки" type="text" style="width:100%;" name="addon_contact_time[]" value=""></td>';
		echo '<td style="text-align: right;"><button style="color:red;border-color:red;" class="button remove-point">Удалить</button></td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '<br>';
	echo '<div style="text-align:right;"><button id="add_point_btn" class="button button-small">Добавить точку</button></div>';

}

add_action( 'save_post', 'belingoGeo_save_city' );
function belingoGeo_save_city( $post_id ) {

	global $post;

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}

	if ( $parent_id = wp_is_post_revision( $post_id ) ) {
		$post_id = $parent_id;
	}

	if(get_post_type($post_id) == 'cities') {
		$city_addon_contacts = [];
		if(isset($_POST['addon_contact_name'])) {
			$addon_contact_name_field = sanitize_text_field($_POST['addon_contact_name']);
			if(isset($_POST['addon_contact_phone'])) {
				$addon_contact_phone_field = sanitize_text_field($_POST['addon_contact_phone']);
			}
			if(isset($_POST['addon_contact_address'])) {
				$addon_contact_address_field = sanitize_text_field($_POST['addon_contact_address']);
			}
			if(isset($_POST['addon_contact_time'])) {
				$addon_contact_time_field = sanitize_text_field($_POST['addon_contact_time']);
			}
			if ( ! empty( $addon_contact_name_field[0] ) ) {
				foreach ( $addon_contact_name_field as $key => $addon_contact_name ) {
					$city_addon_contacts[] = [
						"addon_contact_name"    => base64_encode( $addon_contact_name ),
						"addon_contact_phone"   => base64_encode( $addon_contact_phone_field[ $key ] ),
						"addon_contact_address" => base64_encode( $addon_contact_address_field[ $key ] ),
						"addon_contact_time"    => base64_encode( $addon_contact_time_field[ $key ] )
					];
				}
			}
		}

		update_post_meta( $post_id, "city_addon_contacts", json_encode( $city_addon_contacts ) );
		
		if(isset($_POST["city_eng"])) {
			update_post_meta( $post_id, "city_eng", html_entity_decode( sanitize_text_field($_POST["city_eng"]) ) );
		}
		if(isset($_POST["city_padej1"])) {
			update_post_meta( $post_id, "city_padej1", html_entity_decode( sanitize_text_field($_POST["city_padej1"]) ) );
		}
		if(isset($_POST["city_padej2"])) {
			update_post_meta( $post_id, "city_padej2", html_entity_decode( sanitize_text_field($_POST["city_padej2"]) ) );
		}
		if(isset($_POST["city_padej3"])) {
			update_post_meta( $post_id, "city_padej3", html_entity_decode( sanitize_text_field($_POST["city_padej3"]) ) );
		}
		if(isset($_POST["city_phone"])) {
			update_post_meta( $post_id, "city_phone", html_entity_decode( sanitize_text_field($_POST["city_phone"]) ) );
		}
		if(isset($_POST["city_address"])) {
			update_post_meta( $post_id, "city_address", html_entity_decode( sanitize_text_field($_POST["city_address"]) ) );
		}

		$belingo_geo_basic_forced_slug_generation = get_option('belingo_geo_basic_forced_slug_generation');

		if(!$belingo_geo_basic_forced_slug_generation) {
			$title = get_the_title($post_id);
			if($title != 'Default') {
				remove_action( 'save_post', 'belingoGeo_save_city' );
				wp_update_post( array(
		            'ID' => $post_id,
		            'post_name' => belingogeo_translit_city($title)
		        ));
		        add_action( 'save_post', 'belingoGeo_save_city' );
	    	}
    	}

        flush_rewrite_rules();
        
	}

}

add_filter( 'wp_unique_term_slug', 'belingogeo_unique_term_slug_filter', 10, 3 );
function belingogeo_unique_term_slug_filter( $slug, $term, $original_slug ){

	if($term->taxonomy == 'bg_regions') {
		$belingo_geo_basic_forced_region_slug_generation = get_option('belingo_geo_basic_forced_region_slug_generation');
		if(!$belingo_geo_basic_forced_region_slug_generation) {
			$slug = sanitize_title( belingogeo_translit_city( $term->name) );
		}
	}

	return $slug;
}
 
function belingogeo_translit_city($title) {
	
	$map = array(
	   "Є"=>"YE","І"=>"I","Ѓ"=>"G","і"=>"i","№"=>"#","є"=>"ye","ѓ"=>"g",
	   "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D",
	   "Е"=>"E","Ё"=>"YO","Ж"=>"ZH",
	   "З"=>"Z","И"=>"I","Й"=>"J","К"=>"K","Л"=>"L",
	   "М"=>"M","Н"=>"N","О"=>"O","П"=>"P","Р"=>"R",
	   "С"=>"S","Т"=>"T","У"=>"U","Ф"=>"F","Х"=>"X",
	   "Ц"=>"C","Ч"=>"CH","Ш"=>"SH","Щ"=>"SHH","Ъ"=>"'",
	   "Ы"=>"Y","Ь"=>"","Э"=>"E","Ю"=>"YU","Я"=>"YA",
	   "а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d",
	   "е"=>"e","ё"=>"yo","ж"=>"zh",
	   "з"=>"z","и"=>"i","й"=>"j","к"=>"k","л"=>"l",
	   "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
	   "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"x",
	   "ц"=>"c","ч"=>"ch","ш"=>"sh","щ"=>"shh","ъ"=>"",
	   "ы"=>"y","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
	   "—"=>"-","«"=>"","»"=>"","…"=>""
	);

	return strtr($title, $map);

}

add_filter( 'posts_where', 'belingogeo_posts_where', 10, 2 );
function belingogeo_posts_where( $where, $wp_query ) {
    global $wpdb;
    if ( $title = $wp_query->get( 'search_city' ) ) {
        $where .= " AND " . $wpdb->posts . ".post_title LIKE '%" . esc_sql( $wpdb->esc_like( $title ) ) . "%'";
    }
    return $where;
}

add_action( 'wp_ajax_getcitiescallback', 'belingoGeo_get_cities_ajax_callback' );
function belingoGeo_get_cities_ajax_callback() {

	if(isset($_GET['q'])) {
		$q = sanitize_text_field($_GET['q']);
	}

	$return         = array();
	$search_results = new WP_Query( array(
		'post_type'			  => 'cities',
		'search_city'         => $q,
		'post_status'         => 'publish',
		'posts_per_page'      => 10
	) );
	if ( $search_results->have_posts() ) :
		while ( $search_results->have_posts() ) : $search_results->the_post();
			$title    = ( mb_strlen( $search_results->post->post_title ) > 50 ) ? mb_substr( $search_results->post->post_title, 0, 49 ) . '...' : $search_results->post->post_title;
			$return[] = array( $search_results->post->ID, $title );
		endwhile;
	endif;
	$return[] = array( 0, __('Disabled'));
	echo json_encode( $return );
	die;
}

add_action( 'wp_ajax_getpostscallback', 'belingoGeo_get_posts_ajax_callback' );
function belingoGeo_get_posts_ajax_callback() {

	if(isset($_GET['q'])) {
		$q = sanitize_text_field($_GET['q']);
	}

	$return         = array();
	$search_results = new WP_Query( array(
		's'                   => $q,
		'post_status'         => 'publish',
		'ignore_sticky_posts' => 1,
		'posts_per_page'      => 50,
		'post_type'			  => 'post'
	) );
	if ( $search_results->have_posts() ) :
		while ( $search_results->have_posts() ) : $search_results->the_post();
			$title    = ( mb_strlen( $search_results->post->post_title ) > 50 ) ? mb_substr( $search_results->post->post_title, 0, 49 ) . '...' : $search_results->post->post_title;
			$return[] = array( $search_results->post->ID, $title );
		endwhile;
	endif;
	echo json_encode( $return );
	die;
}

add_action( 'wp_ajax_getpagescallback', 'belingoGeo_get_pages_ajax_callback' );
function belingoGeo_get_pages_ajax_callback() {
	$return         = array();
	$search_results = new WP_Query( array(
		'post_type'			  => 'page',
		'post_status'         => 'publish',
		'posts_per_page'      => -1,
		//'title' 			  => $_GET['q']
	) );
	if ( $search_results->have_posts() ) :
		while ( $search_results->have_posts() ) : $search_results->the_post();
			$title    = ( mb_strlen( $search_results->post->post_title ) > 50 ) ? mb_substr( $search_results->post->post_title, 0, 49 ) . '...' : $search_results->post->post_title;
			$return[] = array( $search_results->post->ID, $title );
		endwhile;
	endif;
	echo json_encode( $return );
	die;
}

add_action( 'wp_ajax_gettermscallback', 'belingoGeo_get_terms_ajax_callback' );
function belingoGeo_get_terms_ajax_callback() {
	$return         = array();
	$search_results = get_terms( array(
		'taxonomy'			  => 'category',
		'hide_empty'		  => false
	) );
	if ( is_array($search_results) && count($search_results)>0 ) :
		foreach ( $search_results as $search_result ) {
			$title    = ( mb_strlen( $search_result->name ) > 50 ) ? mb_substr( $search_result->name, 0, 49 ) . '...' : $search_result->name;
			$return[] = array( $search_result->term_id, $title );
		}
	endif;
	echo json_encode( $return );
	die;
}

add_action( 'wp_ajax_gettagscallback', 'belingoGeo_get_tags_ajax_callback' );
function belingoGeo_get_tags_ajax_callback() {
	$return         = array();
	$search_results = get_terms( array(
		'taxonomy'			  => 'post_tag',
		'hide_empty'		  => false
	) );
	if ( is_array($search_results) && count($search_results)>0 ) :
		foreach ( $search_results as $search_result ) {
			$title    = ( mb_strlen( $search_result->name ) > 50 ) ? mb_substr( $search_result->name, 0, 49 ) . '...' : $search_result->name;
			$return[] = array( $search_result->term_id, $title );
		}
	endif;
	echo json_encode( $return );
	die;
}

add_action( 'bg_regions_add_form_fields', 'belingogeo_bg_regions_add_term_fields' );
function belingogeo_bg_regions_add_term_fields( $taxonomy ) {

	echo '<div class="form-field">
			<label for="bg_regions_phone">'.__('Telephone', 'belingogeo').'</label>
			<input type="text" name="bg_regions_phone" id="bg_regions_phone" />
		</div>
		<div class="form-field">
			<label for="bg_regions_address">'.__('Address', 'belingogeo').'</label>
			<input type="text" name="bg_regions_address" id="bg_regions_address" />
		</div>';

}

add_action( 'bg_regions_edit_form_fields', 'belingogeo_bg_regions_edit_term_fields', 10, 2 );
function belingogeo_bg_regions_edit_term_fields( $term, $taxonomy ) {

	$phone = get_term_meta( $term->term_id, 'bg_regions_phone', true );
	$address = get_term_meta( $term->term_id, 'bg_regions_address', true);

	echo '<tr class="form-field">
			<th><label for="bg_regions_phone">'.__('Telephone', 'belingogeo').'</label></th>
			<td>
				<input name="bg_regions_phone" id="bg_regions_phone" type="text" value="'.esc_attr( $phone ).'">
			</td>
		</tr>
		<tr class="form-field">
			<th><label for="bg_regions_address">'.__('Address', 'belingogeo').'</label></th>
			<td>
				<input name="bg_regions_address" id="bg_regions_address" type="text" value="'.esc_attr( $address ).'">
			</td>
		</tr>';

}

add_action( 'created_bg_regions', 'belingogeo_bg_regions_save_term_fields' );
add_action( 'edited_bg_regions', 'belingogeo_bg_regions_save_term_fields' );
function belingogeo_bg_regions_save_term_fields( $term_id ) {
	
	if(isset($_POST['bg_regions_phone'])) {
		update_term_meta(
			$term_id,
			'bg_regions_phone',
			sanitize_text_field( $_POST['bg_regions_phone'] )
		);
	}

	if(isset($_POST['bg_regions_address'])) {
		update_term_meta(
			$term_id,
			'bg_regions_address',
			sanitize_text_field( $_POST['bg_regions_address'] )
		);
	}
	
}

add_action( 'admin_enqueue_scripts', 'belingoGeo_scripts_admin' );
function belingoGeo_scripts_admin() {
	wp_enqueue_style( 'belingo-geo-select2', BELINGO_GEO_PLUGIN_URL . '/css/select2.min.css' );
	wp_enqueue_script( 'belingo-geo-select2', BELINGO_GEO_PLUGIN_URL . '/js/select2.min.js', array( 'jquery' ) );
	wp_enqueue_script( 'belingo-geo-scripts-admin', BELINGO_GEO_PLUGIN_URL . '/js/belingoGeoAdmin.js', array( 'jquery' ), '', false );
}

?>