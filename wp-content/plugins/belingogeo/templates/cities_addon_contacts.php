<?php if($result) {?>
	<style type="text/css">
		.contacts-container {
			position: relative;
		}
		.contacts-container:after {
			clear: both;
		}
		.contacts-container .item {
			max-width: 50%;
			width: 50%;
			float: left;
			position: relative;
		}
		.contacts-container .item-container {
			padding: 10px;
		}
		@media (max-width: 768px) {
			.contacts-container .item {
				width: 100%;
				max-width: 100%;
			}
		}
	</style>
	<div class="contacts-container">
	<?php foreach ($result as $key => $value) {?>
		<div class="item">
			<div class="item-container">
				<p style="font-size: 20px;color: #000;"><?php echo esc_html($value['addon_contact_name']); ?></p>
				<p><?php echo esc_html($value['addon_contact_phone']); ?><br>
				<?php echo esc_html($value['addon_contact_address']); ?><br>
				<?php echo esc_html($value['addon_contact_time']); ?></p>
			</div>
		</div>
	<?php }?>
	</div>
<?php }?>