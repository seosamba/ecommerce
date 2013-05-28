$(function () {
    if (!window.accounting) {
        //if we have require.js loaded on page then we use it
        if (typeof define === 'function' && define.amd) {
            require([$('#website_url').val() + 'plugins/shopping/web/js/libs/accounting.min.js'], function (a) {
                window.accounting = a;
            });
        } else { //otherwise we load accounting.js in old-school manner
            var script = document.createElement('script');
            script.type = 'text/javascript';// script.async = true;
            script.src = $('#website_url').val() + 'plugins/shopping/web/js/libs/accounting.min.js';
            var scr = document.getElementsByTagName('script')[0];
            scr.parentNode.insertBefore(script, scr);
        }
    }

    if ($('.option-datepicker').size()) {
        var datepickers = $('.option-datepicker');
        if (datepickers[0].type !== 'date') {
            if (window.jQuery && jQuery.ui) {
                $(this).datepicker();
            } else {
                window.console && console.log('no jQuery loaded in this context');
            }
        }
    }

    $(document).on('change', '.product-options-listing select, .product-options-listing input[type="radio"]', function () {
        var $container = $(this).closest('.product-options-listing'),
            productId = $container.data('productid');

        var prices = $(this).closest('.product-options-listing').data('prices');
        if (prices) {
            var newOriginalPrice = prices.original.price,
                newCurrentPrice = prices.current.price;

            $container.find('option:selected, input[type="radio"]:checked').each(function () {
                var index = $(this).val();
                if (prices.original.hasOwnProperty(index)) {
                    newOriginalPrice += prices.original[index];
                    newCurrentPrice += prices.current[index];
                }
            });

            newOriginalPrice = eval(newOriginalPrice);
            newCurrentPrice = eval(newCurrentPrice);

            var $contextProductList = $(this).closest('.product-list').size() ? $(this).closest('.product-list') : 'body';

            $('.price-lifereload-' + productId + '.original-price', $contextProductList).text(accounting.formatMoney(newOriginalPrice, prices.format));
            $('.price-lifereload-' + productId + ':not(.original-price)', $contextProductList).text(accounting.formatMoney(newCurrentPrice, prices.format));
        }
    });
});