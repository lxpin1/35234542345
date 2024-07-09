<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! empty( $order_items ) ) : ?>
<table class="widefat striped" style="font-family:monospace; text-align:left; width:100%;">
	<tbody>
		<?php
			foreach ( $order_items as $order_item ) :
				$data = $order_item->get_data();
				foreach ( $data as $key => $value ) :
					if ( 'meta_data' === $key ) :
						foreach ( $value as $meta ) :
							?>
							<tr>
								<th style="width:20%;"><?php echo 'meta_data[' . esc_html( $meta->get_data()['key'] ) . ']'; ?></th>
								<td>
									<?php
										echo esc_html( $meta->get_data()['value'] );
									?>
								</td>	
							</tr>
							<?php
						endforeach;
					else :
						?>
						<tr>
							<th style="width:20%;"><?php echo esc_html( $key ); ?></th>
							<td>
								<?php
								switch ( $value ) {
									case is_array( $value ):
										echo '<pre>' . esc_html( print_r( $value, true ) ) . '</pre>';
										break;
									case is_object( $value ):
										echo '<pre>' . esc_html( print_r( $value, true ) ) . '</pre>';
										break;
									default:
										echo esc_html( $value );
										break;
								}
								?>
							</td>
						</tr>
						<?php
					endif;
				endforeach;
			endforeach;
		?>
	</tbody>
</table>
<?php else : ?>
<p>No order item data is associated with this Order.</p>
<?php endif; ?>