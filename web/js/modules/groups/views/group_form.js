define([
	'backbone'
], function(Backbone){
    var GroupFormView = Backbone.View.extend({
        el: $('#edit-group'),
        events: {
            'submit': 'submit'
        },
        templates: {

        },
        initialize: function(){
            this.$el.attr('action', $('#website_url').val()+'api/store/groups');

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
            $.ajax({
                url: this.$el.attr('action'),
                data: this.$el.serialize(),
                type: 'POST',
                dataType: 'json',
                success: function(response){
                    self.$el.trigger('reset');
                    self.$el.trigger('group:created');
                    hideSpinner();
                },
                error: function(response){
                    hideSpinner();
                    showMessage(response.responseText, true);
                }
            });
        },
        validate: function(e){
            var el = $(e.currentTarget);
            console.log(el.data());
        }

    });

    return GroupFormView;
});