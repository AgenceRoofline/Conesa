<?php
/**
 * Thème enfant Conesa — functions.php
 * Conesa Rénovation
 */

add_action( 'wp_enqueue_scripts', 'conesa_enqueue_assets' );
function conesa_enqueue_assets() {
    $parent_style = 'astra-theme-css';

    wp_enqueue_style(
        $parent_style,
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme( 'astra' )->get( 'Version' )
    );

    wp_enqueue_style(
        'conesa-css',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get( 'Version' )
    );

    wp_enqueue_script(
        'conesa-js',
        get_stylesheet_directory_uri() . '/js/custom.js',
        array(),
        wp_get_theme()->get( 'Version' ),
        true
    );
}
