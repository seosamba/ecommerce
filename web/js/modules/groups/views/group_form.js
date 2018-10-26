define([
	'backbone'
], function(Backbone){
    var GroupFormView = Backbone.View.extend({
        el: $('#edit-group'),
        events: {
            'submit': 'submit',
            'change #groups-list': 'changeUserDefaultGroup'
        },
        templates: {

        },
        initialize: function(){
            this.$el.find('#edit-group-form').attr('action', $('#website_url').val()+'api/store/groups');

        },
        render: function(){
             return this;
        },
        submit: function(e){
            e.preventDefault();
            var self = this,
                form = $(e.currentTarget).find('#edit-group-form'),
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
                url: this.$el.find('#edit-group-form').attr('action'),
                data: form.serialize(),
                type: 'POST',
                dataType: 'json',
                success: function(response){
                    self.$el.find('#edit-group-form').trigger('reset');
                    self.$el.find('#edit-group-form').trigger('group:created');

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
        },
        changeUserDefaultGroup: function (e) {
            var defaultGropId = $(e.currentTarget).val();
            $.ajax({
                url        : $('#website_url').val() + 'plugin/shopping/run/changeDefaultUserGroup/',
                type       : 'post',
                dataType   : 'json',
                data       : {
                    defaultGroupId : defaultGropId
                },
                success : function(response) {
                    showMessage('Changed', false, 3000);
                }
            });
        }

    });

    return GroupFormView;
});