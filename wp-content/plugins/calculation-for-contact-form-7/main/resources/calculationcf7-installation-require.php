<?php

/* load plugin */
add_action( 'admin_init', 'CALCULATIONCF7_check_load_cf7_plugin', 11 );
function CALCULATIONCF7_check_load_cf7_plugin() {
	if ( ! ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) ) {
	   add_action( 'admin_notices','CALCULATIONCF7_require_cf7_install_error' );
	}
}

/* install error  */
function CALCULATIONCF7_require_cf7_install_error() {
    deactivate_plugins( plugin_basename( CALCULATIONCF7_PLUGIN_FILE ) );?>
	<div class="error">
	   <p><?php _e( ' cf7 calculator plugin is deactivated because it require <a href="plugin-install.php?tab=search&s=contact+form+7">Contact Form 7</a> plugin installed and activated.', 'calculation-for-contact-form-7' ); ?></p>
	</div>
<?php
}