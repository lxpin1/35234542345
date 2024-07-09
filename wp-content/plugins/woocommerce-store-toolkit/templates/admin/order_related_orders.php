<?php
if ( ! empty( $orders ) ) {
	echo '<ul>';
	foreach ( $orders as $order ) {
		echo '<li>';
		echo '<a href="' . esc_url( add_query_arg( 'post', $order ) ) . '">' . esc_html( sprintf( '#%s', $order ) ) . '</a>';
		echo '</li>';
	}
	echo '</ul>';
	echo '<hr/>';
	echo '<p class="description">';
	echo '<strong>*</strong> ';
	// translators: %s is the matching criteria.
	echo wp_kses_post( sprintf( __( 'Orders matched by <code>%s</code>', 'woocommerce-store-toolkit' ), $matching ) );
	echo '</p>';
} else {
	esc_html_e( 'No other Orders were found.', 'woocommerce-store-toolkit' );
}
