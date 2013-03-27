$(function () {
    if (!window.accounting) {
        var script = document.createElement('script');
        script.type = 'text/javascript';// script.async = true;
        script.src = $('#website_url').val() + 'plugins/shopping/web/js/libs/accounting.min.js';
        var scr = document.getElementsByTagName('script')[0];
        scr.parentNode.insertBefore(script, scr);
    }

    $(document).on('change', '.product-options-listing select, .product-options-listing input[type="radio"]', function () {
        var productId = $(this).closest('.product-options-listing').data('productid');

        var prices = $(this).closest('.product-options-listing').data('prices');
        if (prices) {
            var newOriginalPrice = prices.original.price,
                newCurrentPrice = prices.current.price;

            $('div.product-options-listing[data-productid=' + productId + '] *').find('option:selected, input[type="radio"]:checked').each(function () {
                var index = $(this).val();
                if (prices.original.hasOwnProperty(index)) {
                    newOriginalPrice += prices.original[index];
                    newCurrentPrice += prices.current[index];
                }
            });

            newOriginalPrice = eval(newOriginalPrice);
            newCurrentPrice = eval(newCurrentPrice);
            console.log(prices.format);
            $('.price-lifereload-' + productId + '.original-price').text(accounting.formatMoney(newOriginalPrice, prices.format));
            $('.price-lifereload-' + productId + ':not(.original-price)').text(accounting.formatMoney(newCurrentPrice, prices.format));
        }
    });
});