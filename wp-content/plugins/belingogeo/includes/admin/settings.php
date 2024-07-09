<?php
$settings_page = 'belingo_geo_settings.php';

add_action('admin_menu', 'belingoGeo_rewrite_rules_settings');
function belingoGeo_rewrite_rules_settings() {

	$menu_name = 'BelingoGeo';
	$menu_name = apply_filters('belingogeo_menu_settings_name', $menu_name);

	add_menu_page( __('BelingoGeo plugin settings', 'belingogeo'), $menu_name, 'edit_others_posts', 'belingo_geo_settings.php', 'belingo_geo_function', plugins_url( 'belingogeo/images/logo_mini_20x20.png' ), 6 );
	add_submenu_page( 'belingo_geo_settings.php', __('Regions', 'belingogeo'), __('Regions', 'belingogeo'), 'edit_others_posts', '/edit-tags.php?taxonomy=bg_regions&post_type=cities');
	add_submenu_page( 'belingo_geo_settings.php', __('BelingoGeo plugin settings', 'belingogeo'), __('Settings', 'belingogeo'), 'edit_others_posts', 'belingo_geo_settings.php');
	add_submenu_page( 'belingo_geo_settings.php', __('Woo Price', 'belingogeo'), __('Woo Price', 'belingogeo'), 'edit_others_posts', 'belingogeo_woo_price', 'belingogeo_woo_price_func');
	add_submenu_page( 'belingo_geo_settings.php', __('Import', 'belingogeo'), __('Import', 'belingogeo'), 'edit_others_posts', 'belingogeo_import', 'belingogeo_import_func');
	add_submenu_page( 'belingo_geo_settings.php', __('Export', 'belingogeo'), __('Export', 'belingogeo'), 'edit_others_posts', 'belingogeo_export', 'belingogeo_export_func');
	add_submenu_page( 'belingo_geo_settings.php', __('About plugin', 'belingogeo'), __('About plugin', 'belingogeo'), 'edit_others_posts', 'belingo_geo_about.php', 'belingo_geo_about_function');
}

add_action('admin_init', 'belingogeo_download_example');
function belingogeo_download_example() {

	if(isset($_GET['page']) && $_GET['page'] == 'belingogeo_import' && isset($_GET['example'])) {
		$example = BELINGO_GEO_PLUGIN_DIR.'/examples/'.sanitize_text_field($_GET['example']);
		belingogeo_download_csv_file($example);
		exit;
	}

}

function belingogeo_woo_price_func($arr) {

	echo '<div class="wrap">';
	echo '<h1 class="wp-heading-inline">'.__('Woo Price', 'belingogeo').'</h1>';
	echo '<p>'.__('In this section, you can manage prices in WooCommerce for each city separately.').'</p>';
	if (is_plugin_active('belingogeopro/belingoGeoPro.php')) {
		if(function_exists('belingogeopro_woo_price_func')) {
			belingogeopro_woo_price_func();
		}else{
			echo '<p style="color:red;">'.__('You need to update the Pro extension to version 1.10 or higher', 'belingogeo').'</p>';
		}
	}else{
		echo '<p style="color:red;">'.__('Only available for Pro version', 'belingogeo').'</p>';
	}
	echo '</div>';

}

function belingogeo_import_func($arr) {

	echo '<div class="wrap">';
	echo '<h1 class="wp-heading-inline">'.__('Import', 'belingogeo').'</h1>';
	echo '<p>';
	echo str_replace( '<a>', '<a href="admin.php?page=belingogeo_import&example=example1.csv">', __('Upload a list of cities in CSV format, <a>download</a> an example file.', 'belingogeo') );
	echo '<br>';
	echo str_replace( '<a>', '<a href="admin.php?page=belingogeo_import&example=all_cities_rf.csv">', __('All cities of Russia - <a>download</a>', 'belingogeo') );
	echo '</p>';
	echo '<p style="color:red;">'.__('Attention! Loading all cities at once can be difficult and depends on the settings and limitations of your hosting provider. In this case, we recommend splitting the file into several parts.', 'belingogeo').'</p>';
	if (is_plugin_active('belingogeopro/belingoGeoPro.php')) {
		if(function_exists('belingogeopro_import_func')) {
			belingogeopro_import_func();
		}else{
			echo '<p style="color:red;">'.__('You need to update the Pro extension to version 1.9 or higher', 'belingogeo').'</p>';
		}
	}else{
		echo '<p style="color:red;">'.__('Only available for Pro version', 'belingogeo').'</p>';
	}
	echo '</div>';

}

function belingogeo_export_func($arr) {

	echo '<div class="wrap">';
	echo '<h1 class="wp-heading-inline">'.__('Export', 'belingogeo').'</h1>';
	if (is_plugin_active('belingogeopro/belingoGeoPro.php')) {
		if(function_exists('belingogeopro_export_func')) {
			belingogeopro_export_func();
		}else{
			echo '<p style="color:red;">'.__('You need to update the Pro extension to version 1.9 or higher', 'belingogeo').'</p>';
		}
	}else{
		echo '<p style="color:red;">'.__('Only available for Pro version', 'belingogeo').'</p>';
	}
	echo '</div>';

}

function belingo_geo_about_function($arr) {

	echo '<div class="wrap">';
	echo '<h1 class="wp-heading-inline">'.__('About plugin', 'belingogeo').'</h1> ';
	if (!is_plugin_active('belingogeopro/belingoGeoPro.php')) {
		echo '<a target="_blank" href="https://belingo.ru/products/belingogeo-pro/?utm_source=plugin_belingogeo&utm_medium=pro" class="page-title-action">'.__('Go Pro', 'belingogeo').'</a>';
	}
	echo '<p>'.__('The plugin adds the ability to select cities, unique pages are created with a unique url for each city. This allows you to make content unique to search engines.', 'belingogeo').'</p>';
	echo '<h2>'.__('Useful articles', 'belingogeo').'</h2>';
	echo '<div><a target="_blank" href="https://belingo.ru/ustanovka-i-nastrojka-plagina-belingogeo/?utm_source=plugin_belingogeo&utm_medium=description">'.__('Installing and configuring the belingoGeo plugin', 'belingogeo').'</a></div>';
	echo '<div><a target="_blank" href="https://belingo.ru/kak-sortirovat-goroda-v-plagine-belingogeo/?utm_source=plugin_belingogeo&utm_medium=description">'.__('How to sort cities in belingoGeo plugin?', 'belingogeo').'</a></div>';
	echo '<div><a target="_blank" href="https://belingo.ru/kak-sozdat-dopolnitelnoe-pole-dlya-goroda-v-plagine-belingogeo/?utm_source=plugin_belingogeo&utm_medium=description">'.__('How to create an additional field for the city in the belingoGeo plugin?', 'belingogeo').'</a></div>';
	echo '<div><a target="_blank" href="https://belingo.ru/kak-vyvesti-ssylku-dlya-pereklyucheniya-goroda-v-lyubom-meste-sajta/?utm_source=plugin_belingogeo&utm_medium=description">'.__('How to display a link to switch cities anywhere on the site', 'belingogeo').'</a></div>';
	echo '<div><a target="_blank" href="https://belingo.ru/opisanie-vsex-nastroek-plagina-belingogeo/?utm_source=plugin_belingogeo&utm_medium=description">'.__('Description of all settings of the BelingoGeo plugin', 'belingogeo').'</a></div>';
	echo '<h2>'.__('Support', 'belingogeo').'</h2>';
	echo '<div><a target="_blank" href="https://belingo.ru">https://belingo.ru</a> '.__('- Our website.', 'belingogeo').'</div>';
	echo '<div><a target="_blank" href="mailto: support@belingo.ru">support@belingo.ru</a> '.__('- E-mail for communication with technical support specialists.', 'belingogeo').'</div>';
	echo '<div><a target="_blank" href="https://t.me/belingollc">https://t.me/belingollc</a> '.__('- Our telegram channel, where you can find out the latest news and participate in the development of our products.', 'belingogeo').'</div>';
	echo '</div>';
	echo '<h2>'.__('Our other plugins', 'belingogeo').'</h2>';
	echo '<div><a href="https://belingo.ru/products/belingoantispam/?utm_source=plugin_belingogeo&utm_medium=description" target="_blank">Belingo Antispam</a> - '.__('A simple and effective anti-spam plugin for WordPress. The plugin\'s algorithm analyzes user behavior on the site and, if necessary, marks it as spam.', 'belingogeo').'</div>';
	echo '<div><a href="https://belingo.ru/products/belingocredit/?utm_source=plugin_belingogeo&utm_medium=description" target="_blank">Belingo Credit</a> - '.__('A very simple calculator to place on any page of your website using a shortcode. Annuity or differentiated calculation method. For any period and with any interest rate.', 'belingogeo').'</div>';

}

function belingo_geo_function($arr) {
	global $settings_page;

	flush_rewrite_rules();

	$belingogeo_version = BELINGO_GEO_VERSION;
	$belingogeo_version = apply_filters('belingogeo_settings_version', $belingogeo_version);

	echo '<div class="wrap">';
	echo '<h1 class="wp-heading-inline">'.__('Belingo Geo Settings', 'belingogeo').' <sup style="color:green;">v'.$belingogeo_version.'</sup></h1>';
	if (!is_plugin_active('belingogeopro/belingoGeoPro.php')) {
		echo '<a target="_blank" href="https://belingo.ru/products/belingogeo-pro/?utm_source=plugin_belingogeo&utm_medium=pro" class="page-title-action">'.__('Go Pro', 'belingogeo').'</a>';
	}
	echo '<form method="post" action="options.php" enctype="multipart/form-data">';
	settings_fields('belingo_geo_excludes');
	do_settings_sections($settings_page);
	submit_button();
	echo '</form>';

	echo '</div>';
}

add_action( 'admin_notices', 'belingo_geo_custom_admin_notices' );
function belingo_geo_custom_admin_notices() {
 	global $settings_page;
	if(isset( $_GET[ 'page' ] ) && $settings_page == $_GET[ 'page' ] && isset( $_GET[ 'settings-updated' ] ) && true == $_GET[ 'settings-updated' ]) {
		echo '<div class="notice notice-success is-dismissible"><p>'.__('Settings have been saved', 'belingogeo').'</p></div>';
	}
 
}

function belingo_geo_settings() {
	global $settings_page;
	register_setting( 'belingo_geo_excludes', 'belingo_geo_exclude_posts' );
	register_setting( 'belingo_geo_excludes', 'belingo_geo_exclude_post_types' );
	register_setting( 'belingo_geo_excludes', 'belingo_geo_exclude_pages' );
	register_setting( 'belingo_geo_excludes', 'belingo_geo_exclude_terms' );
	register_setting( 'belingo_geo_excludes', 'belingo_geo_exclude_tags' );
	register_setting( 'belingo_geo_excludes', 'belingo_geo_exclude_taxonomies' );
	register_setting( 'belingo_geo_excludes', 'belingo_geo_basic_default_nonecity' );
	register_setting( 'belingo_geo_excludes', 'belingo_geo_basic_disable_url' );
	register_setting( 'belingo_geo_excludes', 'belingo_geo_basic_redirect_page' );
	register_setting( 'belingo_geo_excludes', 'belingo_geo_basic_finding_nonecity' );
	register_setting( 'belingo_geo_excludes', 'belingo_geo_sitemap_per_page');
	register_setting( 'belingo_geo_excludes', 'belingo_geo_exclude_nonobject');
	register_setting( 'belingo_geo_excludes', 'belingo_geo_basic_show_in_breadcrumbs');
	register_setting( 'belingo_geo_excludes', 'belingo_geo_url_type');
	register_setting( 'belingo_geo_excludes', 'belingo_geo_basic_forced_confirmation_city');
	register_setting( 'belingo_geo_excludes', 'belingo_geo_basic_default_text_nonecity');
	register_setting( 'belingo_geo_excludes', 'belingo_geo_basic_filter_links_by_url');
	register_setting( 'belingo_geo_excludes', 'belingo_geo_basic_forced_slug_generation');
	register_setting( 'belingo_geo_excludes', 'belingo_geo_basic_forced_region_slug_generation');
	register_setting( 'belingo_geo_excludes', 'belingo_geo_basic_woo_auto_detect_city_checkout');
	register_setting( 'belingo_geo_excludes', 'belingo_geo_basic_enable_search_in_popup');
	register_setting( 'belingo_geo_excludes', 'belingo_geo_basic_add_city_to_woo_page_title');
	register_setting( 'belingo_geo_excludes', 'belingo_geo_basic_popup_window_header');
	register_setting( 'belingo_geo_excludes', 'belingo_geo_basic_popup_window_text1');
	register_setting( 'belingo_geo_excludes', 'belingo_geo_basic_popup_window_text2');
	register_setting( 'belingo_geo_excludes', 'belingo_geo_basic_enable_windows_in_footer');
	register_setting( 'belingo_geo_excludes', 'belingo_geo_exclude_all_posts');
	add_settings_section( 'belingo_geo_basic', __('Basic', 'belingogeo'), '', $settings_page );
	add_settings_section( 'belingo_geo_excludes', __('Exceptions', 'belingogeo'), '', $settings_page );

	add_settings_field( 
		'belingo_geo_url_type', 
		__('Link type', 'belingogeo'), 
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_basic', 
		array( 
			'type'      => 'radio',
			'option_name' => 'belingo_geo_url_type',
			'descr' 	=> '',
			'options' => [
				[
					"label" => __('City in subdirectory (the link will look like this: example.com/samara/)', 'belingogeo'),
					"value" => "subdirectory",
					"disabled" => false
				],
				[
					"label" => __('City in subdomain (the link will look like this: samara.example.com)', 'belingogeo'),
					"value" => "subdomain",
					"disabled" => true
				]
			],
			'post_type'	=> false,
			'disabled' => false,
			'is_pro' => true
		)
	);

	add_settings_field( 
		'belingo_geo_basic_default_nonecity',  
		__('Default city', 'belingogeo'),
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_basic', 
		array( 
			'type'      => 'select',
			'option_name' => 'belingo_geo_basic_default_nonecity',
			'descr' 	=> __('The string is displayed in the select_city shortcode if there is no selected city', 'belingogeo'),
			'post_type'	=> 'cities',
			'multiple'  => false
		)
	);

	add_settings_field( 
		'belingo_geo_basic_default_text_nonecity', 
		__('If the city is not found', 'belingogeo'), 
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_basic', 
		array( 
			'type'      => 'text',
			'option_name' => 'belingo_geo_basic_default_text_nonecity',
			'descr' 	=> __('If no city is selected, and no default city is specified, you can specify the text that will be displayed, by default: "Not Found".', 'belingogeo'),
			'post_type'	=> false
		)
	);

	add_settings_field( 
		'belingo_geo_basic_popup_window_header', 
		__('Popup title with cities', 'belingogeo'), 
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_basic', 
		array( 
			'type'      => 'text',
			'option_name' => 'belingo_geo_basic_popup_window_header',
			'descr' 	=> __('Popup title with cities', 'belingogeo'),
			'post_type'	=> false
		)
	);

	add_settings_field( 
		'belingo_geo_basic_popup_window_text1', 
		__('Additional text1 in the popup window', 'belingogeo'), 
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_basic', 
		array( 
			'type'      => 'text',
			'option_name' => 'belingo_geo_basic_popup_window_text1',
			'descr' 	=> __('Additional text1 in the popup window', 'belingogeo'),
			'post_type'	=> false
		)
	);

	add_settings_field( 
		'belingo_geo_basic_popup_window_text2', 
		__('Additional text2 in the popup window', 'belingogeo'), 
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_basic', 
		array( 
			'type'      => 'text',
			'option_name' => 'belingo_geo_basic_popup_window_text2',
			'descr' 	=> __('Additional text2 in the popup window', 'belingogeo'),
			'post_type'	=> false
		)
	);

	add_settings_field( 
		'belingo_geo_basic_enable_windows_in_footer', 
		__('Connect pop-ups in the site footer', 'belingogeo'),
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_basic', 
		array( 
			'type'      => 'checkbox',
			'option_name' => 'belingo_geo_basic_enable_windows_in_footer',
			'descr' 	=> __('Enables pop-up windows in the footer of the site, the option can not be enabled if you placed the shortcode manually', 'belingogeo'),
			'post_type'	=> false
		)
	);

	add_settings_field( 
		'belingo_geo_sitemap_per_page', 
		__('Number of urls in the sitemap', 'belingogeo'), 
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_basic', 
		array( 
			'type'      => 'text',
			'option_name' => 'belingo_geo_sitemap_per_page',
			'descr' 	=> __('The number of URLs per page in the sitemap', 'belingogeo'),
			'post_type'	=> false
		)
	);

	add_settings_field( 
		'belingo_geo_exclude_nonobject', 
		__('Exclude anything that is not a registered entity', 'belingogeo'),
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_excludes', 
		array( 
			'type'      => 'checkbox',
			'option_name' => 'belingo_geo_exclude_nonobject',
			'descr' 	=> __('This option excludes all pages not related to WP_Post, WP_Term, WP_Post_Type and WP_Taxonomy. For example archives by date', 'belingogeo'),
			'post_type'	=> false
		)
	);

	add_settings_field( 
		'belingo_geo_exclude_all_posts', 
		__('Exclude all regular posts', 'belingogeo'),
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_excludes', 
		array( 
			'type'      => 'checkbox',
			'option_name' => 'belingo_geo_exclude_all_posts',
			'descr' 	=> __('This option excludes all regular entries. If enabled, exclusion of each individual entry is ignored.', 'belingogeo'),
			'post_type'	=> false
		)
	);

	add_settings_field(
		'belingo_geo_exclude_pages',
		__('Pages', 'belingogeo'),
		'belingo_geo_display_settings',
		$settings_page,
		'belingo_geo_excludes',
		array(
			'type'      => 'select',
			'option_name' => 'belingo_geo_exclude_pages',
			'descr' 	=> __('Pages selected in this list will be excluded from the plugin', 'belingogeo'),
			'post_type'	=> 'page',
			'multiple'  => true	
		)
	);

	add_settings_field(
		'belingo_geo_exclude_terms',
		__('Categories', 'belingogeo'), 
		'belingo_geo_display_settings',
		$settings_page,
		'belingo_geo_excludes',
		array(
			'type'      => 'select',
			'option_name' => 'belingo_geo_exclude_terms',
			'descr' 	=> __('The selected headings will be excluded, urls with the city will not be generated for them, and all posts belonging to this heading will be automatically excluded', 'belingogeo'),
			'post_type'	=> 'term',
			'multiple'  => true	
		)
	);

	add_settings_field(
		'belingo_geo_exclude_tags',
		__('Tags', 'belingogeo'), 
		'belingo_geo_display_settings',
		$settings_page,
		'belingo_geo_excludes',
		array(
			'type'      => 'select',
			'option_name' => 'belingo_geo_exclude_tags',
			'descr' 	=> __('The selected tags will be excluded, urls with the city will not be generated for them', 'belingogeo'),
			'post_type'	=> 'tag',
			'multiple'  => true	
		)
	);

	add_settings_field(
		'belingo_geo_exclude_posts',
		__('Posts', 'belingogeo'),
		'belingo_geo_display_settings',
		$settings_page,
		'belingo_geo_excludes',
		array(
			'type'      => 'select',
			'option_name' => 'belingo_geo_exclude_posts',
			'descr' 	=> __('Posts selected in this list will be excluded from the plugin', 'belingogeo'),
			'post_type'	=> 'post',
			'multiple'  => true	
		)
	);

	add_settings_field(
		'belingo_geo_exclude_post_types',
		__('Post types', 'belingogeo'),
		'belingo_geo_display_settings',
		$settings_page,
		'belingo_geo_excludes',
		array(
			'type'      => 'checkboxes',
			'option_name' => 'belingo_geo_exclude_post_types',
			'descr' 	=> __('The selected post types will be excluded, URLs with the city will not be generated for them, and all posts of the specified post type will be automatically excluded', 'belingogeo'),
			'list'		=> 'custom_post_types',
			'post_type' => false	
		)
	);

	add_settings_field(
		'belingo_geo_exclude_taxonomies',
		__('Taxonomies', 'belingogeo'), 
		'belingo_geo_display_settings',
		$settings_page,
		'belingo_geo_excludes',
		array(
			'type'      => 'checkboxes',
			'option_name' => 'belingo_geo_exclude_taxonomies',
			'descr' 	=> __('The selected taxonomies will be excluded, urls with the city will not be generated for them, and all terms of the specified taxonomy will be automatically excluded', 'belingogeo'),
			'post_type'	=> false,
			'list'		=> 'taxonomies'
		)
	);

	add_settings_field( 
		'belingo_geo_basic_disable_url', 
		__('Disable virtual URLs', 'belingogeo'),
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_basic', 
		array( 
			'type'      => 'checkbox',
			'option_name' => 'belingo_geo_basic_disable_url',
			'descr' 	=> __('You can disable virtual URLs, the plugin will not generate them', 'belingogeo'),
			'post_type'	=> false
		)
	);

	add_settings_field( 
		'belingo_geo_basic_redirect_page', 
		__('Redirect on page if is exists', 'belingogeo'),
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_basic', 
		array( 
			'type'      => 'checkbox',
			'option_name' => 'belingo_geo_basic_redirect_page',
			'descr' 	=> __('When virtual URLs are disabled, you can enable redirection to existing pages. Works only in conjunction with disabled URLs!', 'belingogeo'),
			'post_type'	=> false,
			'disabled'  => true,
			'is_pro'	=> true
		)
	);

	add_settings_field( 
		'belingo_geo_basic_finding_nonecity', 
		__('Definition of a city outside the list', 'belingogeo'),
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_basic', 
		array( 
			'type'      => 'checkbox',
			'option_name' => 'belingo_geo_basic_finding_nonecity',
			'descr' 	=> __('If this option is enabled, the city will be determined anyway, even if it is not in the list. Virtual URLs for such a city will not be generated.', 'belingogeo'),
			'post_type'	=> false
		)
	);

	add_settings_field( 
		'belingo_geo_basic_show_in_breadcrumbs', 
		__('Add city to breadcrumbs', 'belingogeo'),
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_basic', 
		array( 
			'type'      => 'checkbox',
			'option_name' => 'belingo_geo_basic_show_in_breadcrumbs',
			'descr' 	=> __('Option to add the city to breadcrumbs on the website.', 'belingogeo'),
			'post_type'	=> false
		)
	);

	add_settings_field( 
		'belingo_geo_basic_filter_links_by_url', 
		__('Filter links by url', 'belingogeo'),
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_basic', 
		array( 
			'type'      => 'checkbox',
			'option_name' => 'belingo_geo_basic_filter_links_by_url',
			'descr' 	=> __('By default, the plugin replaces links using internal functions that determine the city, but when using caching, problems may arise, to eliminate it, you can try using this option', 'belingogeo'),
			'post_type'	=> false
		)
	);

	add_settings_field( 
		'belingo_geo_basic_forced_confirmation_city', 
		__('Forced confirmation of the city', 'belingogeo'),
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_basic', 
		array( 
			'type'      => 'checkbox',
			'option_name' => 'belingo_geo_basic_forced_confirmation_city',
			'descr' 	=> __('Forced confirmation of the city without a pop-up window with the question: "What is your city?".', 'belingogeo'),
			'post_type'	=> false
		)
	);

	add_settings_field( 
		'belingo_geo_basic_forced_slug_generation', 
		__('Disable forced slug generation for city', 'belingogeo'),
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_basic', 
		array( 
			'type'      => 'checkbox',
			'option_name' => 'belingo_geo_basic_forced_slug_generation',
			'descr' 	=> __('The plugin automatically generates a slug for the city, this option disables automatic generation and you can manually specify the slug for the city.', 'belingogeo'),
			'post_type'	=> false
		)
	);

	add_settings_field( 
		'belingo_geo_basic_forced_region_slug_generation', 
		__('Disable forced slug generation for region', 'belingogeo'),
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_basic', 
		array( 
			'type'      => 'checkbox',
			'option_name' => 'belingo_geo_basic_forced_region_slug_generation',
			'descr' 	=> __('The plugin automatically generates a slug for the region, this option disables automatic generation and you can manually specify the slug for the region.', 'belingogeo'),
			'post_type'	=> false
		)
	);

	add_settings_field( 
		'belingo_geo_basic_woo_auto_detect_city_checkout', 
		__('Automatic city detection on the WooCommerce checkout page', 'belingogeo'),
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_basic', 
		array( 
			'type'      => 'checkbox',
			'option_name' => 'belingo_geo_basic_woo_auto_detect_city_checkout',
			'descr' 	=> __('Automatic city detection on the WooCommerce checkout page', 'belingogeo'),
			'post_type'	=> false,
			'disabled'  => true,
			'is_pro'	=> true
		)
	);

	add_settings_field( 
		'belingo_geo_basic_enable_search_in_popup', 
		__('Enable city search by pop-up window', 'belingogeo'),
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_basic', 
		array( 
			'type'      => 'checkbox',
			'option_name' => 'belingo_geo_basic_enable_search_in_popup',
			'descr' 	=> __('The option displays a city search in a pop-up list of cities', 'belingogeo'),
			'post_type'	=> false,
			'disabled'  => true,
			'is_pro'	=> true
		)
	);

	add_settings_field( 
		'belingo_geo_basic_add_city_to_woo_page_title', 
		__('Add a city to the header of the WooCommerce category', 'belingogeo'), 
		'belingo_geo_display_settings', 
		$settings_page, 
		'belingo_geo_basic', 
		array( 
			'type'      => 'text',
			'option_name' => 'belingo_geo_basic_add_city_to_woo_page_title',
			'descr' 	=> __('You can specify any shortcode, it will be added to the woocommerce category header.', 'belingogeo'),
			'post_type'	=> false,
			'disabled'	=> true,
			'is_pro'	=> true
		)
	);

}
add_action( 'admin_init', 'belingo_geo_settings' );


function belingo_geo_display_settings($args) {

	$args = apply_filters('belingo_geo_display_settings', $args);

	extract( $args );

	$belingo_geo_exclude = get_option( $option_name );

	if(!$belingo_geo_exclude) {
		$belingo_geo_exclude = [];
	}

	if($post_type != 'term' && $post_type != 'tag') {

		switch ( $type ) {

			case 'select':

				$posts = get_posts( array(
					'post_type'   => $post_type,
					'include'     => $belingo_geo_exclude
				) );

				wp_reset_postdata();

				echo "<select ";
				if($multiple) {
					echo " multiple ";
				} 
				echo "class='select2_field' id='".esc_attr($option_name)."' name='" . esc_attr($option_name) . "[]' style='min-width: 300px;'>";

				if(count((array)$belingo_geo_exclude)>0) {
					foreach($posts as $post){
						echo "<option value='".esc_attr($post->ID)."' selected>".esc_html($post->post_title)."</option>";
					}
				}

				echo ($descr != '') ? esc_html($descr) : "";
				echo "</select>";
				echo "<p class='description'>".esc_html($descr)."</p>";
				break;

			case 'text':
				echo "<input type='text' id='".esc_attr($option_name)."' name='" . esc_attr($option_name) . "' value='".esc_attr(get_option($option_name))."' style='min-width: 300px;'";
				if(isset($disabled) && $disabled) {
					echo ' disabled="disabled"';
				}
				echo ">";
				echo "<p class='description'>".esc_html($descr);
				if(isset($is_pro) && $is_pro) {
					echo " <span style=\"color:red;\">";
					_e('Only available for Pro version', 'belingogeo');
					echo "</span>";
				}
				echo "</p>";
				break; 

			case 'simple-text':
				echo "<p style='".$style."'>".esc_html($descr)."</p>";
				break; 

			case 'checkbox':
				echo "<input type='checkbox' id='".esc_attr($option_name)."' name='" . esc_attr($option_name) . "' value='1' ";
				$checked = get_option($option_name);
				if($checked && $checked == 1) {
					echo "checked";
				}
				if(isset($disabled) && $disabled) {
					echo " disabled";
				}
				echo ">";
				echo "<p class='description'>".esc_html($descr);
				if(isset($is_pro) && $is_pro) {
					echo " <span style=\"color:red;\"> ";
					_e('Only available for Pro version', 'belingogeo');
					echo "</span>";
				}
				echo "</p>";
			break;

			case 'radio':
				foreach($options as $option) {
					echo "<p><label for='".esc_attr($option_name)."-".esc_attr($option['value'])."'><input ";
					if($disabled || $option['disabled']) {
						echo "disabled='disabled'";
					}
					echo " type='radio' id='".esc_attr($option_name)."-".esc_attr($option['value'])."' name='" . esc_attr($option_name) . "' value='" . esc_attr($option['value']) . "' ";
					$checked = get_option($option_name);
					if($checked && $checked == $option['value']) {
						echo "checked";
					}
					echo "> ".esc_html($option['label'])."</label></p>";
				}
				echo "<p class='description'>".esc_html($descr);
				if(isset($is_pro) && $is_pro) {
					echo " <span style=\"color:red;\">";
					_e('Only available for Pro version', 'belingogeo');
					echo "</span>";
				}
				echo "</p>";
			break;

			case 'checkboxes':

				if($list == 'custom_post_types') {
					$args = array(
				       'public'   => true,
				       '_builtin' => false,
				       //'rewrite'  => true
				    );
				    $post_types = get_post_types( $args, 'names', 'and' );
				    foreach ( $post_types  as $post_type ) {
				       	echo "<p><label for='".esc_attr($option_name.'_'.$post_type)."'><input type='checkbox' id='".esc_attr($option_name.'_'.$post_type)."' name='" . esc_attr($option_name) . "[".esc_attr($post_type)."]' value='1' ";
				       	if(is_array(get_option($option_name))) {
							$checked = array_key_exists($post_type, get_option($option_name));
							if($checked) {
								echo "checked";
							}
						}
						echo "> ".$post_type."</label></p>";
				    }
				    echo "<p class='description'>".esc_html($descr)."</p>";
				}

				if($list == 'taxonomies') {
					$args = array(
				       'public'   => true,
				       //'rewrite'  => true,
				       '_builtin' => false,
				    );
				    $taxonomies = get_taxonomies( $args );
				    foreach ( $taxonomies  as $taxonomy ) {
				       	echo "<p><label for='".esc_attr($option_name.'_'.$taxonomy)."'><input type='checkbox' id='".esc_attr($option_name.'_'.$taxonomy)."' name='" . esc_attr($option_name) . "[".esc_attr($taxonomy)."]' value='1' ";
				       	if(is_array(get_option($option_name))) {
							$checked = array_key_exists($taxonomy, get_option($option_name));
							if($checked) {
								echo "checked";
							}
						}
						echo "> ".$taxonomy."</label></p>";
				    }
				    echo "<p class='description'>".esc_html($descr)."</p>";
				}

			break;

		}
	}elseif($post_type == 'term') {
		$terms = get_terms( array(
			'taxonomy'   => 'category',
			'include'     => $belingo_geo_exclude,
			'hide_empty'  => false
		) );

		wp_reset_postdata();

		switch ( $type ) {

			case 'select':
				echo "<select multiple class='select2_field' id='".esc_attr($option_name)."' name='" . esc_attr($option_name) . "[]' style='min-width: 300px;'>";

				if(count($belingo_geo_exclude)>0) {
					foreach($terms as $term){
						echo "<option value='".esc_attr($term->term_id)."' selected>".esc_html($term->name)."</option>";
					}
				}

				echo ($descr != '') ? esc_html($descr) : "";
				echo "</select>";
				echo "<p class='description'>".esc_html($descr)."</p>";
				break;
		}
	}elseif($post_type == 'tag') {
		$terms = get_terms( array(
			'taxonomy'   => 'post_tag',
			'include'     => $belingo_geo_exclude,
			'hide_empty'  => false
		) );

		wp_reset_postdata();

		switch ( $type ) {

			case 'select':
				echo "<select multiple class='select2_field' id='".esc_attr($option_name)."' name='" . esc_attr($option_name) . "[]' style='min-width: 300px;'>";

				if(count($belingo_geo_exclude)>0) {
					foreach($terms as $term){
						echo "<option value='".esc_attr($term->term_id)."' selected>".esc_html($term->name)."</option>";
					}
				}

				echo ($descr != '') ? esc_html($descr) : "";
				echo "</select>";
				echo "<p class='description'>".esc_html($descr)."</p>";
				break;
		}
	}
}

?>