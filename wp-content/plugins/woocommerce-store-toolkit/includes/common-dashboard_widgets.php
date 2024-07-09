<?php
/*

Filename: common-dashboard_widgets.php
Description: common-dashboard_widgets.php loads commonly access Dashboard widgets across the Visser Labs suite.
Version: 1.5

*/

/* Start of: WooCommerce News - by Visser Labs */

if( !function_exists( 'woo_vl_dashboard_setup' ) ) {

	function woo_vl_dashboard_setup() {

		// Limit the Dashboard widget to Users with the Manage Options capability
		$user_capability = 'manage_options';
		if( current_user_can( $user_capability ) ) {
			if( apply_filters( 'woo_vl_news_widget', true ) ) {
				$dashboard_widget_title = __( 'Plugin News - by Visser Labs', 'woocommerce-store-toolkit' );
				wp_add_dashboard_widget( 'woo_vl_news_widget', $dashboard_widget_title, 'woo_vl_news_widget' );
			}
		}

	}
	add_action( 'wp_dashboard_setup', 'woo_vl_dashboard_setup' );

	function woo_vl_news_widget() {

		include_once( ABSPATH . WPINC . '/feed.php' );

		// Get the RSS feed for WooCommerce Plugins
		$rss = fetch_feed( 'http://www.visser.com.au/blog/category/woocommerce/feed/' );
		echo '<div class="rss-widget">';
		if( !is_wp_error( $rss ) ) {
			$maxitems = $rss->get_item_quantity( 5 );
			$rss_items = $rss->get_items( 0, $maxitems );
			echo '<ul>';
			foreach ( $rss_items as $item ) {
				echo '<li>';
				echo '<a href="' . esc_url( $item->get_permalink() ) . '" title="' . 'Posted ' . esc_attr( $item->get_date( 'j F Y | g:i a' ) ) . '" class="rsswidget">' . esc_html( $item->get_title() ) . '</a>';
				echo '<span class="rss-date">' . esc_html( $item->get_date( 'j F, Y' ) ) . '</span>';
				echo '<div class="rssSummary">' . wp_kses_post( $item->get_description() ) . '</div>';
				echo '</li>';
			}
			echo '</ul>';
		} else {
			$message = __( 'Connection failed. Please check your network settings.', 'woocommerce-store-toolkit' );
			echo '<p>';
			echo esc_html( $message );
			echo '</p>';
		}
		echo '</div>';

	}

}

/* End of: WooCommerce News - by Visser Labs */