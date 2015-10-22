define([
	'backbone'
], function(Backbone){
    var PickupLocationFormView = Backbone.View.extend({
        el: $('#edit-pickup-location'),
        events: {
            'submit': 'submit',
            'click .working-hours-dialog': 'workingHoursDialog'
        },
        templates: {

        },
        initialize: function(){
            this.$el.attr('action', $('#website_url').val()+'api/store/pickuplocations');
        },
        render: function(){
            $('#location-edit-id').val('');
            $('#edit-pickup-location').attr('method', 'POST');
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
            var data = this.$el.serialize()+'&'+$('.working-hours-list').find('input').serialize()+'&categoryId='+$(".ui-state-active").find('a').data('category-id')+'&id='+$('#location-edit-id').val();
            $.ajax({
                url: this.$el.attr('action'),
                data: data,
                type: this.$el.attr('method'),
                dataType: 'json',
                success: function(response){
                    $('.working-hours-list').find('input').val('');
                    $('#location-edit-id').val('');
                    self.$el.trigger('pickupLocation:created');
                    hideSpinner();
                    if($('#edit-pickup-location').attr('method') === 'POST'){
                        showMessage('Created', false);
                    }else{
                        showMessage('Updated', false);
                    }
                    $('#edit-pickup-location').attr('method', 'POST');
                    $('#edit-pickup-location').find('input[name!="secureToken"]').val('');
                },
                error: function(response){
                    hideSpinner();
                    showMessage(response.responseText, true);
                }
            });
        },
        workingHoursDialog: function(e){
            var applyButton  = 'Apply';
            var assignWorkingHoursButtons = {};

            assignWorkingHoursButtons[applyButton] = function() {
                $(this).dialog('close');
            };


            $('.working-hours-list').dialog({
                buttons: assignWorkingHoursButtons,
                width: 350,
                dialogClass: 'seotoaster',
                resizable : false,
                modal     : true
            });
            return false;

        }
    });

    return PickupLocationFormView;
});