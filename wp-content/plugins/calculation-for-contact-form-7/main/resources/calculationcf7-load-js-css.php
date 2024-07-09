<?php

/* frontend style and script */
add_action( 'wp_enqueue_scripts', 'CALCULATIONCF7_load_front_script_style');
function CALCULATIONCF7_load_front_script_style() {
    wp_enqueue_style( 'CALCULATIONCF7-front-css', CALCULATIONCF7_PLUGIN_DIR . '/assets/css/front.css', false, '2.0.0' );
    wp_enqueue_script( 'CALCULATIONCF7-front-js', CALCULATIONCF7_PLUGIN_DIR . '/assets/js/front.js', array('jquery'), '2.0.0', true );
}
