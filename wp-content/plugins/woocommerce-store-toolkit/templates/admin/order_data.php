<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! empty( $order_data ) ) : ?>
<table class="widefat striped" style="font-family:monospace; text-align:left; width:100%;">
	<tbody>
		<?php foreach ( $order_data as $key => $data ) : ?>
			<?php if ( 'meta_data' === $key ) : ?>
				<?php foreach ( $data as $meta ) : ?>
				<tr>
					<th style="width:20%;"><?php echo 'meta_data[' . esc_html( $meta->get_data()['key'] ) . ']'; ?></th>
					<td>
						<?php
							echo esc_html( $meta->get_data()['value'] );
						?>
					</td>	
				</tr>
				<?php endforeach; ?>
			<?php else: ?>
			<tr>
				<th style="width:20%;"><?php echo esc_html( $key ); ?></th>
				<td>
					<?php
					switch ( $data ) {
						case is_array( $data ):
							echo '<pre>' . esc_html( print_r( $data, true ) ) . '</pre>';
							break;
						case is_object( $data ):
							echo '<pre>' . esc_html( print_r( $data, true ) ) . '</pre>';
							break;
						default:
							echo esc_html( $data );
							break;
					}
					?>
				</td>
			</tr>
			<?php endif; ?>
		<?php endforeach; ?>
	</tbody>
</table>
<?php else : ?>
<p>No order data is associated with this Order.</p>
<?php endif; ?>