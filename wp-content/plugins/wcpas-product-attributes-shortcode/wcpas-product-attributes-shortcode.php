<?php

/**
 * Plugin name: Product Attributes Shortcode
 * Plugin URI: https://wordpress.org/plugins/wcpas-product-attributes-shortcode/
 * Description: WooCommerce extension by 99w.
 * Author: 99w
 * Author URI: https://99w.co.uk
 * Developer: 99w
 * Developer URI: https://99w.co.uk
 * Version: 1.9.2
 * Requires at least: 6.1.0
 * Requires PHP: 7.3.0
 * WC requires at least: 7.9.0
 * WC tested up to: 8.7.0
 * Domain path: /languages
 * Text domain: wcpas-product-attributes-shortcode
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

function wcpas_product_attributes_shortcode_translation() {

	load_plugin_textdomain( 'wcpas-product-attributes-shortcode', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

}
add_action( 'init', 'wcpas_product_attributes_shortcode_translation' );

require_once ABSPATH . 'wp-admin/includes/plugin.php';

if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

	add_action( 'before_woocommerce_init', function() {

		if ( class_exists( 'Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {

			Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );

		}

	});

	add_action( 'before_woocommerce_init', function() {

		if ( class_exists( 'Automattic\WooCommerce\Utilities\FeaturesUtil' ) && version_compare( WC_VERSION, '8.0.0', '>=' ) ) {

			Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );

		}

	});

	function wcpas_product_attributes_shortcode_hpos_enabled() {

		// This function is not recommended for use in custom development

		return class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();

	}

	function wcpas_product_attributes_shortcode( $atts ) {

		// Globals

		global $wp;

		// Shortcode attributes

		$atts = shortcode_atts(
			array(
				'archive_links'				=> 0, // Must be 0 not false
				'attribute'					=> '',
				'current_attribute_link'	=> 1, // Must be 1 not true
				'hide_empty'				=> 1, // Must be 1 not true
				'links_target'				=> '',
				'min_price'					=> '',
				'max_price'					=> '',
				'order'						=> 'asc',
				'orderby'					=> 'name',
				'show_counts'				=> 0, // Must be 0 not false
				'show_descriptions'			=> 0, // Must be 0 not false
			),
			$atts,
			'wcpas_product_attributes'
		);

		// Start output

		$output = '';

		// Get attribute taxonomies

		$attribute_taxonomies = wc_get_attribute_taxonomies();

		if ( !empty( $attribute_taxonomies ) ) {

			// Loop taxonomies

			foreach ( $attribute_taxonomies as $taxonomy ) {

				// If attribute matches shortcode parameter

				if ( strtolower( $atts['attribute'] ) == $taxonomy->attribute_name ) {

					// Set taxonomy id correctly so it can be used for get_terms() lookup

					$taxonomy_id = 'pa_' . $taxonomy->attribute_name;

					// Get terms

					$terms = get_terms(
						array(
							'hide_empty'	=> $atts['hide_empty'],
							'order'			=> $atts['order'],
							'orderby'		=> $atts['orderby'],
							'taxonomy'		=> $taxonomy_id,
						)
					);

					// If terms exist

					if ( !empty( $terms ) ) {

						// Output the list

						$output .= '<ul class="wcpas-product-attributes" id="wcpas-product-attributes-' . esc_attr( $taxonomy_id ) . '">';

						foreach ( $terms as $term ) {

							$current_attribute = 0;
							$current_attribute_class = '';

							if ( 0 == $atts['archive_links'] ) {

								if ( untrailingslashit( home_url( $wp->request ) ) == untrailingslashit( get_permalink( wc_get_page_id( 'shop' ) ) ) ) {

									if ( isset( $_GET[ 'filter_' . $taxonomy->attribute_name ] ) ) {

										if ( $term->slug == $_GET[ 'filter_' . $taxonomy->attribute_name ] ) {

											$current_attribute = 1;
											$current_attribute_class = ' wcpas-product-attribute-current';

										}

									}

								}

								$output .= '<li class="wcpas-product-attribute' . esc_attr( $current_attribute_class ) . '" id="wcpas-product-attribute-' . esc_attr( $taxonomy_id ) . '-' . esc_attr( $term->slug ) . '">';

								$href = add_query_arg( 'filter_' . $taxonomy->attribute_name, $term->slug, get_permalink( wc_get_page_id( 'shop' ) ) );

								if ( '' !== $atts['min_price'] ) {

									$href .= '&min_price=' . $atts['min_price'];

								}

								if ( '' !== $atts['max_price'] ) {

									$href .= '&max_price=' . $atts['max_price'];

								}

								if ( 1 == $current_attribute && 0 == $atts['current_attribute_link'] ) {

									$output .= wp_kses_post( $term->name );

								} else {

									$output .= '<a href="' . esc_url( $href ) . '" class="wcpas-product-attribute-link"' . ( '' !== $atts['links_target'] ? ' target="' . esc_attr( $atts['links_target'] ) . '"' : '' ) . '>' . wp_kses_post( $term->name ) . '</a>';

								}

							} else {

								if ( is_tax( $taxonomy_id, $term ) ) {

									$current_attribute = 1;
									$current_attribute_class = ' wcpas-product-attribute-current';

								}

								$output .= '<li class="wcpas-product-attribute' . esc_attr( $current_attribute_class ) . '" id="wcpas-product-attribute-' . esc_attr( $taxonomy_id ) . '-' . esc_attr( $term->slug ) . '">';

								if ( 1 == $taxonomy->attribute_public && 1 == $current_attribute && 0 == $atts['current_attribute_link'] ) {

									$output .= wp_kses_post( $term->name );

								} elseif ( 1 == $taxonomy->attribute_public ) {

									$href = get_term_link( $term );
									$output .= '<a href="' . esc_url( $href ) . '" class="wcpas-product-attribute-link"' . ( '' !== $atts['links_target'] ? ' target="' . esc_attr( $atts['links_target'] ) . '"' : '' ) . '>' . wp_kses_post( $term->name ) . '</a>';

								} else {

									$output .= wp_kses_post( $term->name );

								}

							}

							if ( 1 == $atts['show_counts'] ) {

								$output .= ' <span class="wcpas-product-attribute-count">' . esc_html__( '(', 'wcpas-product-attributes-shortcode' ) . wp_kses_post( $term->count ) . esc_html__( ')', 'wcpas-product-attributes-shortcode' ) . '</span>';

							}

							if ( 1 == $atts['show_descriptions'] ) {

								if ( !empty( $term->description ) ) {

									$output .= '<div class="wcpas-product-attribute-description">' . wp_kses_post( $term->description ) . '</div>';

								}

							}

							$output .= '</li>';

						}

						$output .= '</ul>';

					}

				}

			}

		}

		return wp_kses_post( $output );

	}
	add_shortcode( 'wcpas_product_attributes', 'wcpas_product_attributes_shortcode' );

} else {

	add_action( 'admin_notices', function() {

		if ( current_user_can( 'edit_plugins' ) ) {

			?>

			<div class="notice notice-error">
				<p><strong><?php esc_html_e( 'Product Attributes Shortcode requires WooCommerce to be installed and activated.', 'wcpas-product-attributes-shortcode' ); ?></strong></p>
			</div>

			<?php

		}

	});

}
