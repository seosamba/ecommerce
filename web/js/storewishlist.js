$(function() {
    $(document).on('click', 'a.remove-wished-product[data-pid]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var self = $(this),
            pid  = self.data('pid');

        showConfirm('Are you sure you would like to remove this item from your Wish List?', function(){
            $.ajax({
                url: $('#website_url').val()+'plugin/shopping/run/removeWishedProduct/',
                type: 'POST',
                dataType: 'json',
                data: {pid : pid},
                success: function(response){
                    if(response.error != 1) {
                        showMessage(response.responseText, false, 3000);
                        window.location.reload();
                    } else {
                        showMessage(response.responseText, true, 3000);
                    }
                }
            });
        });
    });

    $(document).on('click', 'a.add-to-wish-list[data-pid]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var self = $(this),
            pid  = self.data('pid'),
            qty = 1,
            toProfile = self.data('to-profile'),
            clientPage = self.data('client-page'),
            secureToken =  self.closest('.wish-list-block').find('.secureToken').val();

        $.ajax({
            url: $('#website_url').val()+'plugin/shopping/run/addToWishList/',
            type: 'POST',
            dataType: 'json',
            data: {pid : pid, qty : qty, secureToken : secureToken},
            success: function(response){
                if(response.error != 1) {
                    if (typeof response.responseText.alreadyWished !== 'undefined'){
                        showMessage(response.responseText.alreadyWished, false, 3000);
                    } else {
                        if(toProfile == 1) {
                            window.location.href = clientPage;
                        } else {
                            showMessage(response.responseText.addedToList, false, 3000);
                            var productQty = $('.product-wishlist-'+pid).data('qty');
                            $('.product-wishlist-'+pid).text(parseInt(productQty) + qty);
                            $('.last-user-full-name-'+pid).text(response.responseText.lastAddedUser);
                            self.addClass('already-wished');
                        }
                    }
                } else {
                    showMessage(response.responseText, true, 3000);
                }
            }
        });
    });
});
