<?php

function my_theme_enqueue_styles() {
    $parent_style = 'parent-style';
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css?v=150118' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css?v=170118',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

function child_theme_translation() {
    load_child_theme_textdomain( 'Avada', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'child_theme_translation' );

add_action( 'woocommerce_before_shop_loop', 'add_before_catalog_ordering', 30 );
function add_before_catalog_ordering(){
    ?>
    <div class="show-filters-block">
        <a href="#" class="show-filters-block-button">Filtrering</a>
    </div>
    <?php
}

add_action( 'woocommerce_after_shop_loop_item', 'avada_woocommerce_buy_button', 110 );
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
        <div class="clearfix buy-button-clearfix">
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
        sprintf( '<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="button-center %s">%s</a>',
            esc_url( $href ),
            esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
            esc_attr( $product->get_id() ),
            esc_attr( $product->get_sku() ),
            esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
            esc_html( 'KÖP' )
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

add_filter( 'woocommerce_get_price_html', 'wpa83367_price_html', 100, 2 );
function wpa83367_price_html( $price, $product ){
    $dom = new DOMDocument();
    $dom->loadHTML($price);
    $elem = $dom->getElementsByTagName('span')->item(0);
    preg_match('/\.*(\d+)\.*/', $elem->nodeValue, $matches);
    $digits = str_split($matches[1]);
    $priceFormatted = '';
    $lastDigitIndex = count($digits) - 1;
    for($i=0; $i<=$lastDigitIndex; $i++){
        $priceFormatted = $digits[$lastDigitIndex - $i] . $priceFormatted;
        if( $i%3 == 2){
            $priceFormatted = ' ' . $priceFormatted;
        }
    }
    $elem->nodeValue = preg_replace('/\.*(\d+)\.*/', $priceFormatted, $elem->nodeValue);
    return $dom->saveHTML();
}

add_action('wp_head', 'iphoneMouseEnterFix');
function iphoneMouseEnterFix() {
    ?>
    <script>
        jQuery( document ).ready( function() {
            var clicked = false;

            jQuery('li.product').on( 'click', '.add_to_cart_button', function(e){
                clicked = true;
            });

            function waitForClick(iterationsLeft, callback) {
                if(iterationsLeft>0 && !clicked){
                    setTimeout( function(){waitForClick(iterationsLeft - 1, callback)}, 100);
                }
                else{
                    console.log(iterationsLeft);
                    callback();
                }

            }
            jQuery('li.product').on( 'touchstart', '.add_to_cart_button', function(e){
                clicked = false;
                var self = this;
                waitForClick(10, function() {
                    if (!clicked) {
                        jQuery(self).click();
                    }
                });
            });

        });
    </script>
    <?php
}

add_action('wp_head', 'showFiltersClick');
function showFiltersClick() {
    ?>
    <script>
        jQuery( document ).ready( function() {
            var isCatalogOrderingVisible = false;
            jQuery(document).on('click', '.show-filters-block-button', function(event){
                event.preventDefault();
                var parent = jQuery(this).parent();
                if(!isCatalogOrderingVisible) {
                    jQuery('.catalog-ordering').addClass("opened");
                    parent.addClass("opened");
                    isCatalogOrderingVisible = true;
                }
                else{
                    jQuery('.catalog-ordering').removeClass("opened");
                    parent.removeClass("opened");
                    isCatalogOrderingVisible = false;
                }
            });
        });
    </script>
    <?php
}

add_action('admin_head', 'admin_custom_styles');
function admin_custom_styles() {
    ?>
    <style>
        .avadaredux-container #avadaredux-form-wrapper .form-table td>fieldset{
            position: relative;
        }
        .avadaredux-container #avadaredux-form-wrapper .avadaredux-main .wp-picker-container>a{
            display: inline-block;
            width: 100%;
            min-height: 50px;
            /*top: calc(50% - 25px) !important;*/
            top: 0px;
            position: absolute;
        }
        .avadaredux-container #avadaredux-form-wrapper .avadaredux-main .wp-picker-container>a>span{
            height: 50px !important;
            /*top: calc(50% - 25px) !important;*/
            top: 0px;
        }
        #wpbody .avadaredux-container #avadaredux-form-wrapper .avadaredux-main .avadaredux-container-color .iris-picker{
            margin-top: 35px;
        }
        #wpbody .avadaredux-container #avadaredux-form-wrapper .avadaredux-main .avadaredux-container-color_alpha .iris-picker{
            margin-top: 35px;
            width: 100% !important;
        }
        #wpbody .avadaredux-container #avadaredux-form-wrapper .form-table .avadaredux-field-container{
            margin: 25px 0px;
        }
    </style>
    <?php
}