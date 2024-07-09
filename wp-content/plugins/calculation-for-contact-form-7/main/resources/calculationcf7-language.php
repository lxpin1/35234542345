<?php

/*PLUGIN LOADED contact form 7 */
add_action( 'plugins_loaded', 'CALCULATIONCF7_load_textdomain_lang' );
function CALCULATIONCF7_load_textdomain_lang() {
    load_plugin_textdomain( 'calculation-for-contact-form-7', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}

/*LANGUAGES FOLDER contact form 7 */
function CALCULATIONCF7_load_my_own_textdomain_lang( $mofile, $domain ) {
    if ( 'calculation-for-contact-form-7' === $domain && false !== strpos( $mofile, WP_LANG_DIR . '/plugins/' ) ) {
        $locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
        $mofile = WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/languages/' . $domain . '-' . $locale . '.mo';
    }
    return $mofile;
}
add_filter( 'load_textdomain_mofile', 'CALCULATIONCF7_load_my_own_textdomain_lang', 10, 2 );