jQuery(document).ready(function () {
    /*********fixWpAdminBar*************/
    var wpAdminBar = jQuery('#wpadminbar');
    var fusionHeaderWrapper = jQuery('#wrapper .fusion-header-wrapper');
    var secondaryHeader = fusionHeaderWrapper.find('.fusion-secondary-header');
    var header = fusionHeaderWrapper.find('.fusion-header');
    var fixNumber = 0;

    fixTopPositions();

    function checkFix(element, expectedTop, iterationsLeft, myFixNumber){
        jQuery.data(this, 'checkFixTimer', setTimeout(function () {
            if(myFixNumber == fixNumber ) {
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
            let headerHeight = 0;
            if (header.is(':visible')) {
                headerHeight = header.outerHeight();
            }
            fusionHeaderWrapper.css(
                'min-height',
                ( headerTop + headerHeight - secondaryHeaderTop - jQuery('.fusion-mobile-nav-holder:visible').height() * 1 ) + 'px'
            );
            checkFix(header, headerTop, 20, fixNumber);
        }
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

    /********** Show search bar ****************/
    jQuery(document).on('click', '.show-search-button', function(event) {
        event.preventDefault();
        const searchForm = jQuery('.search-in-menu .searchform');
        const fusionMenuCart = searchForm.closest('.fusion-secondary-menu').find('.fusion-menu-cart');
        console.log(fusionMenuCart.html());
        if (searchForm.is(':visible')) {
            searchForm.hide();
            fusionMenuCart.css('float', 'right');
            fixTopPositions();
        }
        else {
            searchForm.css('display', 'inline-block');
            fusionMenuCart.css('float', 'none');
            fixTopPositions();
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

    if(jQuery('.useful-links').length){
        var usefulLinksSiblings = jQuery('.useful-links').siblings();
        if(usefulLinksSiblings.length>0){
            var searchBar = usefulLinksSiblings.last();
            searchBar.removeClass('col-lg-4 col-md-4 col-sm-4');
            searchBar.addClass('col-lg-8 col-md-8 col-sm-8');
        }
    }

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
        const totalPriceNew = kcoWidget.find('#kco-page-subtotal-amount .woocommerce-Price-amount').html();
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
