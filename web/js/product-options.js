$(function() {
    $(document).on('change', '.product-options-listing select', function() {
        var optionId         = $(this).find('option:selected').val();
        var productId        = $(this).closest('.product-options-listing').attr('data-productid');
        calculateOptionPrice(optionId, productId);
      
    });
    
    $(document).on('click', '.product-options-listing input[type="radio"]', function() {
        var optionId         = $(this).find('option:selected').val();
        var productId        = $(this).closest('.product-options-listing').attr('data-productid');
        calculateOptionPrice(optionId, productId);
    });
    
    function calculateOptionPrice(optionId, productId){
        var productBasePrice = parseFloat($('#product-option-original-price-'+productId).val());
        var productOptionsSelect = $('div[data-productid=' + productId + '] *').find('option:selected');
        var productOptionsRadio  = $('div[data-productid=' + productId + '] *').find('input[type="radio"]:checked');
        $('div[data-productid=' + productId + '] *').find('option:selected').each(function(){
            if($('input[name=product-option-calculated-price-'+$(this).val()+']').length>0){
                var optionPriceValue = $('input[name=product-option-calculated-price-'+$(this).val()+']').val();
                var optionPriceModifier = $('input[name=product-option-calculated-price-'+$(this).val()+']').attr('data-modifier');
                if(optionPriceModifier == '-'){
                    productBasePrice -= parseFloat(optionPriceValue);
                }
                if(optionPriceModifier == '+'){
                    productBasePrice += parseFloat(optionPriceValue);
                }
            }
        });
        $('div[data-productid=' + productId + '] *').find('input[type="radio"]:checked').each(function(){
            if($('input[name=product-option-calculated-price-'+$(this).val()+']').length>0){
                var optionPriceValue = $('input[name=product-option-calculated-price-'+$(this).val()+']').val();
                var optionPriceModifier = $('input[name=product-option-calculated-price-'+$(this).val()+']').attr('data-modifier');
                if(optionPriceModifier == '-'){
                    productBasePrice -= parseFloat(optionPriceValue);
                }
                if(optionPriceModifier == '+'){
                    productBasePrice += parseFloat(optionPriceValue);
                }
            }
            
        });
        if($('.product-option-original-currency').length>0){
            var currency = $('.product-option-original-currency').val();
        }
        if($('.price-lifereload-'+productId).length>0){
           $('.price-lifereload-'+productId).text(currency+productBasePrice);
        }
       
    }
});