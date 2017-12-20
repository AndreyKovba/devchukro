<?php

function my_theme_enqueue_styles() {
    $parent_style = 'parent-style';
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css?v=1312171' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css?v=201217',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

function child_theme_translation() {
    load_child_theme_textdomain( 'Avada', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'child_theme_translation' );

add_filter('woocommerce_add_to_cart_redirect', 'themeprefix_add_to_cart_redirect');
function themeprefix_add_to_cart_redirect() {
    if(!isset($_GET['buy'])) {
        return;
    }
    global $woocommerce;
    $checkout_url = wc_get_checkout_url();
    return $checkout_url;
}

add_action( 'woocommerce_after_shop_loop_item', 'avada_woocommerce_buy_button', 10 );

function avada_woocommerce_buy_button( $args = array() ) {
    global $product;

    if ( $product &&
        ( ( $product->is_purchasable() && $product->is_in_stock() ) || $product->is_type( 'external' ) )
    ) {
        $defaults = array(
            'quantity' => 1,
            'class'    => implode( ' ', array_filter( array(
                'button',
                'product_type_' . $product->product_type,
                $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : ''
            ) ) )
        );
        $args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );
        ?>
        <div class="clearfix">
            <?php drawBuyButton($args); ?>
        </div>
        <?php
    }
}

function drawBuyButton($args){
    global $product;
    $href = $product->add_to_cart_url();
    $uriArray = explode('/', $href);
    if(strpos($uriArray[count($uriArray) - 1], '?') === false ){
        $href .= '?';
    }
    $href .= '&buy=1';
    echo apply_filters( 'woocommerce_loop_add_to_cart_link',
        sprintf( '<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="button-left %s">%s</a>',
            esc_url( $href ),
            esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
            esc_attr( $product->get_id() ),
            esc_attr( $product->get_sku() ),
            esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
            esc_html( 'Köpa' )
        ),
        $product );
}

add_action( 'avada_after_main_container','avada_child_after_main_container', 10 );
function avada_child_after_main_container() {
    ?>
    <script>
        jQuery(document).ready(function () {
            function isSmallScreen() {
                return jQuery(window).width() * 1 <= 600
            }

            var searchForm = '<?php
                echo str_replace("'", '"', str_replace("\n", " ", get_search_form(false)));
                ?>';
            var pattern = '#menu-top-secondary-menu a[href="#search-in-menu"]';
            var isSearchOpen = false;
            var wasSmallScreen = isSmallScreen();
            jQuery(pattern).parent().append('<div class="search-in-menu-overlay"></div>');
            jQuery(pattern).parent().append('<div class="search-in-menu" style="display:none;">' + searchForm + '</div>');
            jQuery(pattern).parent().parent().prepend('<div class="search-out-menu" style="display:none;">' + searchForm + '</div>');

            jQuery( window ).resize(function() {
                if( isSearchOpen && wasSmallScreen !== isSmallScreen() ){
                    var searchInMenu = jQuery('.search-in-menu');
                    var searchInMenuLink = searchInMenu.parent().children('a');
                    var searchOutMenu = jQuery('.search-out-menu');
                    if(wasSmallScreen){
                        searchOutMenu.hide();
                        searchInMenu.show();
                        searchInMenuLink.hide();
                    }
                    else{
                        searchOutMenu.show();
                        searchInMenu.hide();
                        searchInMenuLink.show();
                    }
                    wasSmallScreen = !wasSmallScreen;
                }
            });

            jQuery(document).on('click', pattern, function(event){
                event.preventDefault();
                var parent = jQuery(this).parent();
                if( isSmallScreen() ){
                    parent.parent().find('.search-out-menu').fadeIn(500);
                }
                else{
                    jQuery(this).hide();
                    parent.find('.search-in-menu').fadeIn(500);
                }
                parent.find('.search-in-menu-overlay').show();
                isSearchOpen = true;
            });

            jQuery(document).on('click', '.search-in-menu-overlay', function(event){
                event.preventDefault();
                var parent = jQuery(this).parent();
                parent.find('.search-in-menu-overlay').hide();
                if( isSmallScreen() ){
                    parent.parent().find('.search-out-menu').fadeOut(300, function () {
                        parent.find('a[href="#search-in-menu"]').show();
                    });
                }
                else {
                    parent.find('.search-in-menu').fadeOut(300, function () {
                        parent.find('a[href="#search-in-menu"]').show();
                    });
                }
                isSearchOpen = false;
            });
        });
    </script>
    <?php
}
