<?php
if( !empty( $order_items ) ) {
	echo '<table class="widefat striped" style="font-family:monospace; text-align:left; width:100%;">';
	echo '<tbody>';

	foreach( $order_items as $order_item ) {

		echo '<tr>';
		echo '<th colspan="3">';
		echo 'order_item_name: ' . esc_html( $order_item->name );
		echo '<br />';
		echo 'order_item_type: ' . esc_html( $order_item->type );
		echo '<br />';
		echo 'order_item_id: ' . esc_html( $order_item->id );
		echo '</th>';
		echo '</tr>';

		if( !empty( $order_item->meta ) ) {
			foreach( $order_item->meta as $meta_value ) {

				if( $meta_value->meta_key == '_tmcartepo_data' ) {

					$epos = maybe_unserialize( $meta_value->meta_value );
					if( !is_array( $epos ) )
						continue;

					echo '<tr>';
					echo '<th>&raquo; ' . esc_html( $meta_value->meta_key ) . '</th>';
					echo '<th colspan="2">' . __( 'Extra Product Options', 'woocommerce-store-toolkit' ) . '</th>';
					echo '</tr>';
					foreach( $epos as $epo_key => $epo ) {
						if( is_array( $epo ) ) {

							echo '<tr>';
							echo '<th>&raquo; &raquo; ' . esc_html( $epo_key ) . '</th>';
							echo '<th>';
							echo 'name: ' . esc_html( $epo['name'] );
							echo '<br />';
							echo 'value: ' . esc_html( $epo['value'] );
							echo '</th>';
							echo '<td class="actions">';
							do_action( 'woo_st_order_item_extra_product_option_data_actions', $post->ID, $epo['name'] );
							echo '</td>';
							echo '</tr>';

							foreach( $epo as $epo_item_key => $epo_item ) {

								echo '<tr>';
								echo '<th style="width:20%;">&raquo; &raquo; &raquo; <?php echo esc_html( $epo_item_key ); ?></th>';
								echo '<td><?php echo esc_html( is_array( $epo_item ) ? print_r( $epo_item, true ) : $epo_item ); ?></td>';
								echo '<td class="actions">&nbsp;</td>';
								echo '</tr>';

							}
						} else {

							echo '<tr>';
							echo '<th style="width:20%;">&raquo; &raquo; ' . esc_html( $epo_key ) . '</th>';
							echo '<td>' . esc_html( print_r( $epo, true ) ) . '</td>';
							echo '<td class="actions">&nbsp;</td>';
							echo '</tr>';

						}
					}
					continue;

				}
	
				echo '<tr>';
				echo '<th style="width:20%;">&raquo; ' . esc_html( $meta_value->meta_key ) . '</th>';
				echo '<td>' . esc_html( $meta_value->meta_value ) . '</td>';
				echo '<td class="actions">';
				echo do_action( 'woo_st_order_item_data_actions', $post->ID, $meta_value->meta_key );
				echo '</td>';
				echo '</tr>';

			}
		}

	}
	echo '</tbody>';
	echo '</table>';
} else {
	echo '<p>';
	_e( 'No order items are associated with this Order.', 'woocommerce-store-toolkit' );
	echo '</p>';
}