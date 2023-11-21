define([
	'backbone',
    'text!../templates/add-new-cash-register-id.html',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln',
], function(Backbone,
            addNewCashRegisterIdTmpl, i18n){
    var PickupLocationFormView = Backbone.View.extend({
        el: $('#edit-pickup-location'),
        events: {
            'submit': 'submit',
            'click .working-hours-dialog': 'workingHoursDialog',
            'click .cash-register-id-dialog': 'cashRegisterIdDialog',
        },
        templates: {

        },
        initialize: function(){
            this.$el.attr('action', $('#website_url').val()+'api/store/pickuplocations');
        },
        render: function(){
            $('#location-edit-id').val('');
            $('#cash-register-id-view').val('');
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
                showMessage(_.isUndefined(i18n['Missing required field'])?'Missing required field':i18n['Missing required field'], true);
                return false;
            }
            showSpinner();
            var cashRegisterId = [];
            var cashRegisterLabel = [];
            var hasEmptyRegisterId = false;
            if($('.cash-register-id-list').find('.cash-register-id').length) {
                _.each($('.cash-register-id-list').find('.cash-register-id'), function(el){
                    if ($(el).val() == ''){
                        hasEmptyRegisterId = true;
                    }
                    cashRegisterId.push($(el).val());
                });

                _.each($('.cash-register-id-list').find('.cash-register-label'), function(el){
                    var elLabel = $(el).val();

                    if(elLabel == '') {
                        elLabel = $(el).closest('.register-row').find('.cash-register-id').val();
                    }
                    cashRegisterLabel.push(elLabel);
                });

                if(cashRegisterId.length) {
                    cashRegisterId = cashRegisterId.join();
                }

                if(cashRegisterLabel.length) {
                    cashRegisterLabel = cashRegisterLabel.join();
                }
            }

            if(hasEmptyRegisterId) {
                showMessage(_.isUndefined(i18n['Cash register id should be not empty.'])?'Cash register id should be not empty.':i18n['Cash register id should be not empty.'], true);
                return false;
            }

            var data = this.$el.serialize()+'&'+$('.working-hours-list').find('input').serialize()+'&categoryId='+$(".ui-state-active").find('a').data('category-id')+'&id='+$('#location-edit-id').val()+'&cashRegisterId='+cashRegisterId+'&cashRegisterLabel='+cashRegisterLabel;
            $.ajax({
                url: this.$el.attr('action'),
                data: data,
                type: this.$el.attr('method'),
                dataType: 'json',
                success: function(response){
                    $('.working-hours-list').find('input').val('');
                    $('.register-row').remove();
                    $('#cash-register-id-view').val('');
                    $('#location-edit-id').val('');
                    self.$el.trigger('pickupLocation:created');
                    hideSpinner();
                    if($('#edit-pickup-location').attr('method') === 'POST'){
                        showMessage(_.isUndefined(i18n['Created'])?'Created':i18n['Created'], false);
                    }else{
                        showMessage(_.isUndefined(i18n['Updated'])?'Updated':i18n['Updated'], false);
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
            var applyButton  = _.isUndefined(i18n['Apply'])?'Apply':i18n['Apply'];
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

        },
        cashRegisterIdDialog: function(e){
            e.preventDefault();
            var applyButton  = _.isUndefined(i18n['Apply'])?'Apply':i18n['Apply'];
            var assignCashRegisterIdButtons = {};

            assignCashRegisterIdButtons[applyButton] = function() {
                $(this).dialog('close');
                $('#add-new-row').unbind();
            };

            $('.cash-register-id-list').dialog({
                buttons: assignCashRegisterIdButtons,
                width: 650,
                dialogClass: 'seotoaster',
                resizable : false,
                modal     : true,
                show      : 'clip',
                hide      : 'clip',
                open: function(event, ui) {
                    event.preventDefault();
                    $('#add-new-row').on('click', function(e){
                        e.preventDefault();
                        var rowDiv = _.template(addNewCashRegisterIdTmpl, {'i18n':i18n});
                        $('.cash-register-block').append(rowDiv);
                    });

                    $(document).on('click', '.remove-register-id', function(e){
                        var el = $(e.currentTarget);
                        $(el).closest('.register-row').remove();
                    });
                },
                close: function (event, ui) {
                    $(this).dialog('destroy');
                    $('#add-new-row').unbind();
                }
            });
            return false;

        }
    });

    return PickupLocationFormView;
});