define([
	'backbone'
], function(Backbone){
    var DiscountFormView = Backbone.View.extend({
        el: $('#quantity-discount-form'),
        events: {
            'submit': 'submit'
        },
        templates: {

        },
        initialize: function(){
            this.$el.attr('action', $('#website_url').val()+'api/store/discounts');

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
            if($('#discount-quantity-global').is(':checked')){
                showConfirm('All local config will be updated. Continue?', function(){
                    self.saveForm(self);
                });
            }else{
                this.saveForm(self);
            }

        },
        validate: function(e){
            var el = $(e.currentTarget);
        },
        saveForm: function(form){
            showSpinner();
            $.ajax({
                url: this.$el.attr('action'),
                data: this.$el.serialize(),
                type: 'POST',
                dataType: 'json',
                success: function(response){
                    form.$el.trigger('reset');
                    form.$el.trigger('discount:created');
                    hideSpinner();
                },
                error: function(response){
                    hideSpinner();
                    showMessage(response.responseText, true);
                }
            });
        }

    });

    return DiscountFormView;
});