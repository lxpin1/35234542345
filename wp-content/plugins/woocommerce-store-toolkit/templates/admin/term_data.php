<?php
echo '<tr class="form-field">';
echo '<th scope="row" valign="top">';
echo '<label>' . __( 'Term meta', 'woocommerce-store-toolkit' ) . '</label>';
echo '</th>';
echo '<td>';

echo '<table class="widefat page fixed ' . esc_attr( $class ) . '">';

echo '<thead>';
echo '<tr>';
echo '<th class="manage-column" style="padding-left:1em;">' . __( 'Meta key', 'woocommerce-store-toolkit' ) . '</th>';
echo '<th class="manage-column">' . __( 'Meta value', 'woocommerce-store-toolkit' ) . '</th>';
echo '<th class="manage-column">&nbsp;</th>';
echo '</tr>';
echo '</thead>';

echo '<tbody>';
if( !empty( $term_meta ) ) {
	foreach( $term_meta as $meta_name => $meta_value ) {

		if( count( maybe_unserialize( $meta_value ) ) == 1 )
			$meta_value = $meta_value[0];
		$meta_value = maybe_unserialize( $meta_value );

		echo '<tr>';

		if(
			is_array( $meta_value ) || 
			is_object( $meta_value )
		) {

			echo '<tr>';
			echo '<th colspan="2">' . esc_html( $meta_name ) . '</th>';
			echo '<td class="actions">';
			do_action( sprintf( 'woo_st_%s_data_actions', $type ), $term->term_id, $meta_name );
			echo '</td>';
			echo '</tr>';
			foreach( $meta_value as $inner_meta_name => $inner_meta_value ) {
				echo '<tr>';
				echo '<th style="width:20%;">&raquo; ' . esc_html( $inner_meta_name ) . '</th>';
				echo '<td>' . esc_html( is_array( $inner_meta_value ) || is_object( $inner_meta_value ) ? print_r( $inner_meta_value, true ) : $inner_meta_value ) . '</td>';
				echo '</tr>';
			}

		} else {
			echo '<td style="width:20%;">' . esc_html( $meta_name ) . '</td>';
			echo '<td>' . esc_html( is_array( $meta_value ) || is_object( $meta_value ) ? print_r( $meta_value, true ) : $meta_value ) . '</td>';
			echo '<td class="actions">';
			do_action( sprintf( 'woo_st_%s_data_actions', $type ), $term->term_id, $meta_name );
			echo '</td>';
		}
		echo '</tr>';

	}
} else {
	echo '<tr>';
	echo '<td colspan="2">';
	echo __( 'No Term meta is assocated with this Term.', 'woocommerce-store-toolkit' );
	echo '</td>';
	echo '</tr>';
}
echo '</tbody>';

echo '</table>';

echo '</td>';
echo '</tr>';