define([
	'backbone'
], function(Backbone){
    var PickupLocationFormView = Backbone.View.extend({
        el: $('#edit-pickup-location'),
        events: {
            'submit': 'submit'
        },
        templates: {

        },
        initialize: function(){
            this.$el.attr('action', $('#website_url').val()+'api/store/pickuplocations');
        },
        render: function(){
             return this;
        },
        submit: function(e){
            e.preventDefault();
            var self = this,
                form = $(e.currentTarget),
                isValid = true;

            _.each(form.find('.required'), function(el){
                if (!$(el).val()){
                    isValid = false;
                }
            });

            if (!isValid){
                showMessage('Missing required field', true);
                return false;
            }
            showSpinner();
            var data = this.$el.serialize()+'&categoryId='+$(".ui-state-active").find('a').data('category-id');
            $.ajax({
                url: this.$el.attr('action'),
                data: data,
                type: 'POST',
                dataType: 'json',
                success: function(response){
                    self.$el.trigger('pickupLocation:created');
                    hideSpinner();
                },
                error: function(response){
                    hideSpinner();
                    showMessage(response.responseText, true);
                }
            });
        }
    });

    return PickupLocationFormView;
});