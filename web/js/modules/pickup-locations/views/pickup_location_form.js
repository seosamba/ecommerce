define([
	'backbone',
    'text!../templates/cash-register-ids.html',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln',
], function(Backbone,
            cashRegisterIdsTmpl, i18n){
    var PickupLocationFormView = Backbone.View.extend({
        el: $('#edit-pickup-location'),
        events: {
            'submit': 'submit',
            'click .working-hours-dialog': 'workingHoursDialog',
            'click .cash-register-id-dialog': 'cashRegisterIdDialog',
            'change .location-country': 'changeLocationCountry',
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
            var cashRegisterIds = [];
            if($('.cash-register-id-list').find('input.register-field').length) {
                _.each($('.cash-register-id-list').find('input.register-field'), function(el){
                    if($(el).is(":checked")) {
                        var regId = $(el).val();
                        cashRegisterIds.push(regId);
                    }
                });

                if(cashRegisterIds.length) {
                    cashRegisterIds = cashRegisterIds.join();
                }
            }

            var data = this.$el.serialize()+'&'+$('.working-hours-list').find('input').serialize()+'&categoryId='+$(".ui-state-active").find('a').data('category-id')+'&id='+$('#location-edit-id').val()+'&cashRegisterIds='+cashRegisterIds;
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
                    $('.state-block').addClass('hide');
                    $('.location-state').empty().append('<option value="">'+ (_.isUndefined(i18n['Select state'])?'Select state':i18n['Select state']) +'</option>');
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
            var applyButton  = _.isUndefined(i18n['Apply'])?'Apply':i18n['Apply'],
                assignCashRegisterIdButtons = {},
                cashRegisterList = {};

            assignCashRegisterIdButtons[applyButton] = function() {
                $(this).dialog('close');
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
                    $.ajax({
                        url: $('#website_url').val()+'plugin/shopping/run/getCashRegisterList',
                        type: 'GET',
                        data:{secureToken: $('.secure-token-pickup-cat').val()},
                        dataType: 'json',
                        success: function(response) {
                            if(!response.error) {
                                cashRegisterList = response.responseText.cashRegisterList;

                                if(!$('.register-row').find('.cash-register-field-row').length) {
                                    var rowsDiv = _.template(cashRegisterIdsTmpl, {'i18n':i18n, cashRegisterList});
                                    $('.cash-register-block').append(rowsDiv);
                                }
                            }
                        }
                    });
                },
                close: function (event, ui) {
                    $(this).dialog('destroy');
                }
            });
            return false;

        },
        changeLocationCountry: function (e) {
            var el = $(e.currentTarget),
                country = $(el).val(),
                states = '<option value="">'+ (_.isUndefined(i18n['Select state'])?'Select state':i18n['Select state']) +'</option>';

            if(country == 'United States' || country == 'Canada' || country == 'Australia') {
                $.ajax({
                    url: $('#website_url').val()+'plugin/shopping/run/getStateListByCountry',
                    type: 'POST',
                    data:{country: country, secureToken: $('.secure-token-pickup-cat').val()},
                    dataType: 'json',
                    success: function(response) {
                        if(!response.error) {
                           var stateList = response.responseText.stateList;

                            _.each(stateList, function(stateData, key ){
                                states += '<option value="'+ stateData.state +'">'+ stateData.name +'</option>';
                            });

                            $('.state-block').removeClass('hide');
                            $('.location-state').empty().append(states);
                        } else {
                            $('.location-state').empty().append(states);
                        }
                    }
                });
            } else {
                $('.state-block').addClass('hide');
                $('.location-state').empty().append(states);
            }
        }
    });

    return PickupLocationFormView;
});