$(function() {
    $(document).on('click', 'a.remove-notified-product[data-pid]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var self = $(this),
            pid  = self.data('pid');

        showConfirm('Are you sure you would like to remove this item from your notification List?', function(){
            $.ajax({
                url: $('#website_url').val()+'plugin/shopping/run/removeNotifiedProduct/',
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

    $(document).on('click', 'a.add-to-notify-list[data-pid]', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var self = $(this),
            pid  = self.data('pid'),
            toProfile = self.data('to-profile'),
            clientPage = self.data('client-page'),
            secureToken =  self.closest('.notify-list-block').find('.secureToken').val();

        $.ajax({
            url: $('#website_url').val()+'plugin/shopping/run/addToNotifyList/',
            type: 'POST',
            dataType: 'json',
            data: {pid : pid, secureToken : secureToken},
            success: function(response){
                if(response.error != 1) {
                    if (typeof response.responseText.alreadyNotified !== 'undefined'){
                        showMessage(response.responseText.alreadyNotified, false, 3000);
                    } else {
                        if(toProfile == 1) {
                            window.location.href = clientPage;
                        } else {
                            showMessage(response.responseText.addedToList, false, 3000);
                            self.addClass('already-notified');
                        }
                    }
                } else {
                    showMessage(response.responseText, true, 3000);
                }
            }
        });
    });
});
