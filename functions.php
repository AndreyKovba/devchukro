<?php

function my_theme_enqueue_styles() {

    $parent_style = 'parent-style'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css?v=131217' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}

function avada_header_1() {
    if ( ! in_array( Avada()->settings->get( 'header_layout' ), array( 'v1', 'v2', 'v3' ) ) ) {
        return;
    }
    ?>
    <div class="fusion-header-sticky-height"></div>
    <div class="fusion-header">
        <div class="fusion-row">
            <?php avada_logo(); ?>
            <div class="top-search-bar">
                <?php get_search_form(); ?>
            </div>
            <?php avada_main_menu(); ?>
        </div>
    </div>
    <?php
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

add_action( 'avada_after_main_container','avada_child_after_main_container', 10 );
function avada_child_after_main_container() {
    ?>
    <div class="bottom-search-bar">
        <?php get_search_form(); ?>
    </div>
    <?php
}