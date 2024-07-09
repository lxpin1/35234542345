<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! empty( $order_refunds ) ) : ?>
<table class="widefat striped" style="font-family:monospace; text-align:left; width:100%;">
	<tbody>
		<?php
			foreach ( $order_refunds as $order_refund ) :
				$data = $order_refund->get_data();

                // Unset meta_data, line_items, shipping_lines, tax_lines, fee_lines, coupon_lines, refunds.
                unset( $data['meta_data'] );
                unset( $data['line_items'] );
                unset( $data['shipping_lines'] );
                unset( $data['tax_lines'] );
                unset( $data['fee_lines'] );
                unset( $data['coupon_lines'] );
                unset( $data['refunds'] );

				foreach ( $data as $key => $value ) :
						?>
						<tr>
							<th style="width:20%;"><?php echo esc_html( $key ); ?></th>
							<td>
								<?php
								switch ( $value ) {
									case is_array( $value ):
										break;
									case is_object( $value ):
										break;
									default:
										echo esc_html( $value );
										break;
								}
								?>
							</td>
						</tr>
						<?php
				endforeach;
			endforeach;
		?>
	</tbody>
</table>
<?php else : ?>
<p>No refunds data is associated with this Order.</p>
<?php endif; ?>