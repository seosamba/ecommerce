$(function(){
    $('input.customer-attribute').on('blur', function(e){
        var data = {};
        data[$(this).data('attribute')] = $(this).val();

        $.ajax({
            url: $('#website_url').val() + 'api/store/customer/id/' + $(this).data('uid'),
            method: 'PUT',
            data: JSON.stringify(data),
            complete: function(xhr, status, response) {
                if (status === 'error'){
                    showMessage(status, true);
                } else {

                }
            }
        })
    });
});