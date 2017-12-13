<?php

function my_theme_enqueue_styles() {

    $parent_style = 'parent-style'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css?v=1312171' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css?v=1312171',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

add_action( 'avada_after_main_container','avada_child_after_main_container', 10 );
function avada_child_after_main_container() {
    ?>
    <script>
        jQuery(document).ready(function () {
            var searchForm = '<div class="search-in-menu" style="display:none;"><?php
                echo str_replace("'", '"', str_replace("\n", " ", get_search_form(false) ) );
            ?></div>';
            var pattern = '#menu-top-secondary-menu a[href="#search-in-menu"]';
            jQuery(pattern).parent().append('<div class="search-in-menu-overlay"></div>');
            jQuery(pattern).parent().append(searchForm);

            jQuery(document).on('click', pattern, function(event){
                event.preventDefault();
                var parent = jQuery(this).parent();
                jQuery(this).hide();
                parent.find('.search-in-menu-overlay').show();
                parent.find('.search-in-menu').fadeIn(500);
            });
            jQuery(document).on('click', '.search-in-menu-overlay', function(event){
                event.preventDefault();
                var parent = jQuery(this).parent();
                parent.find('.search-in-menu-overlay').hide();
                parent.find('.search-in-menu').fadeOut(300, function(){
                    parent.find('a[href="#search-in-menu"]').show();
                });
            });
        });
    </script>
    <?php
}