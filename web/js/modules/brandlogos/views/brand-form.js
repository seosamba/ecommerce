define([
    'backbone',
    'i18n!../../../nls/'+$('#brand-system-language').val()+'_ln'
], function(Backbone, i18n){
    var BrandFormView = Backbone.View.extend({
        el: $('#brand-form'),
        events: {
            'submit': 'submit'
        },
        templates: {

        },
        initialize: function(){
            this.$el = $('#lead-form');
            this.$el.attr('action', $('#website_url').val()+'api/store/brands');

        },
        render: function(){
            return this;
        },
        submit: function(e, goToProfile){
            if (e.target) {
                e.preventDefault();
                var form =  $(e.currentTarget);
            } else {
                form = e;
            }

            var self = this,
                isValid = true,
                submitButton = form.find('#brand-form-save');

            if (submitButton.length < 1) {
                return false;
            }
            _.each(form.find('.required'), function(el){
                if (!$(el).val()){
                    isValid = false;
                }
            });

            if (!isValid){
                showMessage(_.isUndefined(i18n['Missing required field'])?'Missing required field':i18n['Missing required field'], true);
                return false;
            }

            var formParams = form.serialize();

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formParams,
                dataType: 'json'
            }).done(function(response) {
                 self.$el.trigger('BrandForm:created');
                 showMessage(response.responseText.message, false, 5000);
                 form.find('input[type="text"]').val('');
                 form.find('textarea').val('');
                 form.find('select[name="prefix"]').val('');
            }).fail(function(response){
                showMessage(response.responseJSON, true, 5000);
            });
            return false;

        }
    });

    return BrandFormView;
});