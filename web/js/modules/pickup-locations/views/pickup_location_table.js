define([
    'backbone',
    '../collections/pickup-location',
    'text!../templates/paginator.html',
    'text!../templates/cash-register-ids.html',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln',
    $('#website_url').val()+'system/js/external/jquery/plugins/DataTables/jquery.dataTables.min.js'
], function(Backbone,
            PickupLocationCollection,PaginatorTmpl, cashRegisterIdsTmpl, i18n
    ){

    var PickupLocationTableView = Backbone.View.extend({
        el: $('#pickup-locations-table'),
        events: {
            'click a[data-role=delete]': 'deleteLocation',
            'click a[data-role=edit]'  : 'editLocation',
            'click td.location-paginator a.page': 'navigate'
        },
        templates: {
            paginator: _.template(PaginatorTmpl)
        },
        initialize: function(options){
            this.pickupLocation = new PickupLocationCollection();

            this.pickupLocation.on('reset', this.renderLocations, this);
            this.pickupLocation.on('add', this.renderLocations, this);
            this.pickupLocation.on('destroy', this.renderLocations, this);
        },
        render: function(){
            $('.change-category-label').val($(".ui-state-active").find('a').text());
            this.pickupLocation.categoryId = $(".ui-state-active").find('a').data('category-id');
            this.pickupLocation.pager();
        },
        renderLocations: function(){
            this.$el.find('tbody').empty();
            this.pickupLocation.each(this.renderLocation, this);
            this.pickupLocation.info()['i18n'] = i18n;
            this.$('td.location-paginator').html(this.templates.paginator(this.pickupLocation.information));
        },
        renderLocation: function(pickupLocation){
            var locationName = pickupLocation.get('name');
            if(locationName.length > 25){
               locationName = locationName.substr(0, 25)+'...';
            }
            this.$el.find('tbody').append(
                '<tr>'+
                    '<td>'+locationName+'</td>'+
                    '<td>'+pickupLocation.get('address1')+'</td>'+
                    '<td>'+pickupLocation.get('address2')+'</td>'+
                    '<td>'+pickupLocation.get('city')+'</td>'+
                    '<td>'+pickupLocation.get('zip')+'</td>'+
                    '<td>'+pickupLocation.get('country')+'</td>'+
                    '<td>'+pickupLocation.get('phone')+'</td>'+
                    '<td class="text-center"><a class="ticon-pencil icon14" data-role="edit" data-cid="'+pickupLocation.get('id')+'" href="javascript:;"></a> ' +
                    '<a class="ticon-remove error icon14" data-role="delete" data-cid="'+pickupLocation.get('id')+'" href="javascript:;"></a></td>'+
                    '</tr>'

            );
        },
        editLocation: function(e){
            var locationId = $(e.currentTarget).data('cid'),
                model = this.pickupLocation.get(locationId),
                workingHours = model.get('workingHours'),
                checkedCashRegisterId = model.get('cashRegisterId'),
                checkedCashRegisterLabel = model.get('cashRegisterLabel'),
                cashRegisterList = model.get('cashRegisterList'),
                country = model.get('country'),
                state = model.get('state');

            $('.register-row').remove();
            $('.location-name').val(model.get('name'));
            $('.location-address1').val(model.get('address1'));
            $('.location-address2').val(model.get('address2'));
            $('.location-city').val(model.get('city'));
            $('.location-zip').val(model.get('zip'));
            $('.location-weight').val(model.get('weight'));
            $('.location-country [value="'+country+'"]').prop('selected', true);

            $('.state-block').addClass('hide');
            $('.cash-register-id-view').val('');

            var states = '<option value="">'+ (_.isUndefined(i18n['Select state'])?'Select state':i18n['Select state']) +'</option>';
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

                            $('.location-state [value="'+state+'"]').prop('selected', true);
                        } else {
                            $('.location-state').empty().append(states);
                        }
                    }
                });
            } else {
                $('.state-block').addClass('hide');
                $('.location-state').empty().append(states);
            }

            $('.location-phone').val(model.get('phone'));
            $('#location-external-id').val(model.get('external_id'));
            $('#location-allowed-to-delete').val(model.get('allowed_to_delete'));
            var cashRegisterIdView = [];
            if(typeof checkedCashRegisterId !== 'undefined' && checkedCashRegisterId.length && typeof checkedCashRegisterLabel !== 'undefined' && checkedCashRegisterLabel.length) {
                _.each(checkedCashRegisterId, function(value, id){
                    var cRow = checkedCashRegisterLabel[id] + '('+value+')';
                    cashRegisterIdView.push(cRow);
                });

                if(cashRegisterIdView.length) {
                    $('#cash-register-id-view').val(cashRegisterIdView.join(', '));
                }
            }
            _.each(workingHours, function(value, name){
                $('input[name="working-hours-'+name+'"]').val(value);
            });

            if(!$('.register-row').find('.cash-register-field-row').length) {
                var rowsDiv = _.template(cashRegisterIdsTmpl, {'i18n':i18n, cashRegisterList, 'checkedCashRegisterIds':checkedCashRegisterId});
                $('.cash-register-block').append(rowsDiv);
            }

            $('#location-edit-id').val(locationId);
            $('#edit-pickup-location').attr('method', 'PUT');
        },
        deleteLocation: function(e){
            var cid = $(e.currentTarget).data('cid'),
                model = this.pickupLocation.get(cid);

            showConfirmCustom(_.isUndefined(i18n['Are you sure want to delete?'])?'Are you sure want to delete?':i18n['Are you sure want to delete?'], _.isUndefined(i18n['Yes'])?'Yes':i18n['Yes'], _.isUndefined(i18n['No'])?'No':i18n['No'], function(){
                if (model){
                    model.destroy();
                    $('.working-hours-list').find('input').val('');
                    $('.register-row').remove();
                    $('#location-edit-id').val('');
                    $('.state-block').addClass('hide');
                    $('.location-state').empty().append('<option value="">'+ (_.isUndefined(i18n['Select state'])?'Select state':i18n['Select state']) +'</option>');
                    $('.location-name').val('');
                    $('.location-address1').val('');
                    $('.location-address2').val('');
                    $('.location-city').val('');
                    $('.location-zip').val('');
                    $('.location-phone').val('');
                    $('.location-weight').val('');
                    $('#cash-register-id-view').val('');
                }
            });
        },
        resetLocation: function(){
            this.$el.fnClearTable();
        },
        navigate: function(e){
            e.preventDefault();

            var page = $(e.currentTarget).data('page');
            if ($.isNumeric(page)){
                this.pickupLocation.goTo(page);
            } else {
                switch(page){
                    case 'first':
                        this.pickupLocation.goTo(this.pickupLocation.firstPage);
                        break;
                    case 'last':
                        this.pickupLocation.goTo(this.pickupLocation.totalPages);
                        break;
                    case 'prev':
                        this.pickupLocation.requestPreviousPage();
                        break;
                    case 'next':
                        this.pickupLocation.requestNextPage();
                        break;
                }
            }
        }
    });

    return PickupLocationTableView;
});
