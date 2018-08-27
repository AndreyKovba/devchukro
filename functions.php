<?php

function my_theme_enqueue_styles() {
    $parent_style = 'parent-style';
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css?v=270818' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/main.css?v=270818',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

function child_theme_translation() {
    load_child_theme_textdomain( 'Avada', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'child_theme_translation' );

function add_before_catalog_ordering(){
    ?>
    <div class="show-filters-block">
        <a href="#" class="show-filters-block-button">Filtrering</a>
    </div>
    <?php
}
add_action( 'woocommerce_before_shop_loop', 'add_before_catalog_ordering', 30 );

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
add_action( 'woocommerce_after_shop_loop_item', 'avada_woocommerce_buy_button', 110 );

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

function avada_child_after_main_container() {
    ?>
    <script>
        jQuery(document).ready(function () {
            if(jQuery('.useful-links').length){
                var usefulLinksSiblings = jQuery('.useful-links').siblings();
                if(usefulLinksSiblings.length>0){
                    var searchBar = usefulLinksSiblings.last();
                    searchBar.removeClass('col-lg-4 col-md-4 col-sm-4');
                    searchBar.addClass('col-lg-8 col-md-8 col-sm-8');
                }
            }

            /*********fixWpAdminBar*************/
            var wpAdminBar = jQuery('#wpadminbar');
            var fusionHeaderWrapper = jQuery('#wrapper .fusion-header-wrapper');
            var secondaryHeader = fusionHeaderWrapper.find('.fusion-secondary-header');
            var header = fusionHeaderWrapper.find('.fusion-header');
            var countWaitingForResize = 0;
            var fixNumber = 0;

            function checkFix(element, expectedTop, iterationsLeft, myFixNumber){
                jQuery.data(this, 'checkFixTimer', setTimeout(function () {
                    if(countWaitingForResize == 0 && myFixNumber == fixNumber ) {
                        if (expectedTop!=0 && parseInt(element.css('top')) != expectedTop) {
                            fixTopPositions();
                        }
                        else {
                            if (iterationsLeft > 0) {
                                checkFix(element, expectedTop, iterationsLeft - 1);
                            }
                        }
                    }
                }, 50));
            }

            function fixTopPositions(){
                fixNumber++;
                var secondaryHeaderTop = wpAdminBar.outerHeight() * 1;
                var headerTop = secondaryHeaderTop + secondaryHeader.outerHeight();
                if(headerTop>0) {
                    secondaryHeader.css('top', secondaryHeaderTop + 'px');
                    header.css('top', headerTop + 'px', 'important');
                    header.css('max-height', 'calc(100% - ' + headerTop + 'px)', 'important');
                    fusionHeaderWrapper.css(
                        'min-height',
                        ( headerTop + header.outerHeight() - secondaryHeaderTop - jQuery('.fusion-mobile-nav-holder:visible').height() * 1 ) + 'px'
                    );
                    checkFix(header, headerTop, 20, fixNumber);
                }
            }

            if(jQuery('body').width() <= 1000) {
                fixTopPositions();
            }

            jQuery( window ).resize(function() {
                if(jQuery('body').width() <= 1000) {
                    clearTimeout(jQuery.data(this, 'resizeTimer'));
                    clearTimeout(jQuery.data(this, 'checkFixTimer'));
                    jQuery.data(this, 'resizeTimer', setTimeout(function() {
                        fixTopPositions();
                    }, 200));
                }
            });

            jQuery(window).scroll(function() {
                if(jQuery('body').width() <= 1000) {
                    clearTimeout(jQuery.data(this, 'scrollTimer'));
                    clearTimeout(jQuery.data(this, 'checkFixTimer'));
                    jQuery.data(this, 'scrollTimer', setTimeout(function () {
                        fixTopPositions();
                    }, 50));
                }
            });
            /**********fixWpAdminBar end****************/

            function isSmallScreen() {
                return jQuery(window).width() * 1 <= 600
            }

            jQuery('#menu-main .search').remove();
            jQuery(document).on('click', '.fusion-icon-bars', function(event) {
                event.preventDefault();
                const fusionHeader = jQuery('.fusion-header');
                if (fusionHeader.hasClass('menu-open')) {
                    fusionHeader.removeClass('menu-open');
                }
                else {
                    fusionHeader.addClass('menu-open');
                }
            });

            jQuery(document).on('touchend', '.fusion-open-submenu', function(event) {
                const parentLi = jQuery(this);
                jQuery.data(this, 'submenuOpenTimer', setTimeout(function () {
                    if (jQuery(window).height() - (parentLi.offset().top + parentLi.height()) < 20) {
                        parentLi.closest('.fusion-header.menu-open').animate({ scrollTop: jQuery(document).height() }, 50);
                    }
                }, 100));
            });

            jQuery(document).on('click', '.search-in-menu-overlay', function(event) {
                event.preventDefault();
                var fusionHeaderWrapper = jQuery(this).closest('.fusion-header-wrapper');
                fusionHeaderWrapper.removeClass('with-search');
                var parent = jQuery(this).parent();
                parent.find('.search-in-menu-overlay').hide();
                if( isSmallScreen() ){
                    parent.parent().find('.search-out-menu').fadeOut(300, function () {
                        parent.find('a[href="#search-in-menu"]').show();
                        fixTopPositions();
                    });
                }
                else {
                    parent.find('.search-in-menu').fadeOut(300, function () {
                        parent.find('a[href="#search-in-menu"]').show();
                        fixTopPositions();
                    });
                }
                isSearchOpen = false;
                fixTopPositions();
            });

            /********open cart submenu on click***********/
            var isCartSubmenuVisible = false;
            var firstOpen = true;
            jQuery(document).on('mouseover', '.fusion-menu-cart .fusion-secondary-menu-icon', function() {
                if (firstOpen) {
                    fixLongCartList();
                    firstOpen = false;
                }
            });

            jQuery(document).on('click', '.fusion-menu-cart .fusion-secondary-menu-icon', function(event) {
                event.preventDefault();
                var cartSubmenu = jQuery(this).closest('.fusion-menu-cart').find('.fusion-custom-menu-item-contents');
                if (isCartSubmenuVisible) {
                    cartSubmenu.css('visibility', 'none');
                    cartSubmenu.css('opacity', 0);
                    isCartSubmenuVisible = false;
                }
                else {
                    cartSubmenu.css('visibility', 'visible');
                    cartSubmenu.css('opacity', 1);
                    isCartSubmenuVisible = true;
                    if (firstOpen) {
                        fixLongCartList();
                        firstOpen = false;
                    }
                }
            });

            jQuery(document).on('mouseleave', '.fusion-menu-cart .fusion-secondary-menu-icon', function(event) {
                var cartSubmenu = jQuery(this).closest('.fusion-menu-cart').find('.fusion-custom-menu-item-contents');
                if (!isCartSubmenuVisible) {
                    cartSubmenu.css('visibility', '');
                    cartSubmenu.css('opacity', '');
                }
            });

            /*******Fix long cart list********/
            function fixLongCartList() {
                var cartItems = jQuery('.fusion-header-wrapper .fusion-secondary-header .fusion-menu-cart-items');
                cartItems.prepend('<div class="fusion-menu-cart-items-list"></div>');
                cartItems.find('.fusion-menu-cart-item').each(function() {
                    jQuery(this).detach().appendTo(cartItems.find(".fusion-menu-cart-items-list"));
                });
            }

            /****** Fix top cart after klarna's items count changed *********/
            function fixTopCart(kcoWidget) {
                let itemsTotalCount = 0;
                let itemsInfo = {};
                kcoWidget.find('#klarna-checkout-cart tr').each(function(index, elem) {
                    const href = jQuery(elem).find('.product-name a').attr('href');
                    if (typeof href !== 'undefined') {
                        const count = jQuery(elem).find('input.qty').val() * 1;
                        itemsTotalCount += count;
                        itemsInfo[href] = count;
                    }
                });
                const fusionWooCartSeparator = jQuery('.menu-text .fusion-woo-cart-separator').html();
                const totalPriceNew = kcoWidget.find('#kco-page-total .woocommerce-Price-amount').html();
                jQuery('.menu-text')
                    .html(itemsTotalCount + ' Item(s) ')
                    .append('<span class="fusion-woo-cart-separator">' + fusionWooCartSeparator + '</span>')
                    .append('<span class="woocommerce-Price-amount"> ' + totalPriceNew + '</span>');

                jQuery('.fusion-menu-cart-item a').each(function(index, elem) {
                    const href = jQuery(elem).attr('href');
                    if (typeof href !== 'undefined') {
                        const updatedElementCount = itemsInfo[href];
                        if (typeof updatedElementCount !== 'undefined') {
                            const woocommercePriceAmount = jQuery(elem).find('.woocommerce-Price-amount').html();
                            jQuery(elem).find('.fusion-menu-cart-item-quantity')
                                .html(updatedElementCount + ' x ')
                                .append('<span class="woocommerce-Price-amount">' + woocommercePriceAmount + '</span>');
                        }
                        else {
                            jQuery(elem).remove();
                        }
                    }
                });
            }

            const kcoWidget = jQuery('#klarna-checkout-widget');
            const totalPriceSelector = '#kco-page-total .woocommerce-Price-amount';
            let totalPriceOld = kcoWidget.find(totalPriceSelector).text();
            kcoWidget.bind("DOMSubtreeModified",function(){
                const totalPriceNew = kcoWidget.find(totalPriceSelector).text();
                if (totalPriceNew !== '' && totalPriceNew !== totalPriceOld) {
                    totalPriceOld = totalPriceNew;
                    fixTopCart(kcoWidget, totalPriceNew);
                }
            });
        });
    </script>
    <?php
}
add_action( 'avada_after_main_container','avada_child_after_main_container', 10 );

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
                    callback();
                }

            }
            jQuery('li.product').on( 'touchstart', '.add_to_cart_button', function(e){
                const oldTopPosition = jQuery(window).scrollTop();
                clicked = false;
                waitForClick(10, () => {
                    if (!clicked) {
                        const newTopPosition = jQuery(window).scrollTop();
                        if (Math.abs(newTopPosition - oldTopPosition) <= 2 ) {
                            jQuery(this).click();
                        }
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
            top: 0px;
            position: absolute;
        }
        .avadaredux-container #avadaredux-form-wrapper .avadaredux-main .wp-picker-container>a>span{
            height: 50px !important;
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

function avada_add_search_to_main_nav( $items, $args ) {
    $ubermenu = false;

    if ( function_exists( 'ubermenu_get_menu_instance_by_theme_location' ) && ubermenu_get_menu_instance_by_theme_location( $args->theme_location ) ) {
        // disable woo cart on ubermenu navigations
        $ubermenu = true;
    }

    if ( Avada()->settings->get( 'header_layout' ) != 'v6' && false == $ubermenu ) {
        if ( 'main_navigation' == $args->theme_location || 'sticky_navigation' == $args->theme_location ) {
            if ( Avada()->settings->get( 'main_nav_search_icon' ) ) {
                $items .= '<li class="fusion-custom-menu-item fusion-main-menu-search">';
                $items .= '<a class="fusion-main-menu-icon"></a>';
                $items .= '<div class="fusion-custom-menu-item-contents">';
                $items .= get_search_form( false );
                $items .= '</div>';
                $items .= '</li>';
            }
        }
        if ('main_navigation' != $args->theme_location) {
            $searchForm = str_replace("'", '"',
                str_replace("\r", " ",
                    str_replace("\n", " ", get_search_form(false))
                )
            );
            $items = "<li>" . get_avada_mobile_main_menu() . "</li>
                <li><div class='search-in-menu'>$searchForm</div></li>" .
                $items;
        }
    }

    return $items;
}

function get_avada_mobile_main_menu() {
    ob_start();
    avada_mobile_main_menu();
    return ob_get_clean();
}
