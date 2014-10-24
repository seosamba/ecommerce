define([
    'backbone',
    '../collections/pickup-location',
    'text!../templates/paginator.html',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln',
    $('#website_url').val()+'system/js/external/jquery/plugins/DataTables/jquery.dataTables.min.js'
], function(Backbone,
            PickupLocationCollection,PaginatorTmpl, i18n
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
                workingHours = model.get('workingHours');

            $('.location-name').val(model.get('name'));
            $('.location-address1').val(model.get('address1'));
            $('.location-address2').val(model.get('address2'));
            $('.location-city').val(model.get('city'));
            $('.location-zip').val(model.get('zip'));
            $('.location-weight').val(model.get('weight'));
            $('.location-country [value="'+model.get('country')+'"]').prop('selected', true);
            $('.location-phone').val(model.get('phone'));
            _.each(workingHours, function(value, name){
                $('input[name="working-hours-'+name+'"]').val(value);
            });
            $('#location-edit-id').val(locationId);
            $('#edit-pickup-location').attr('method', 'PUT');
        },
        deleteLocation: function(e){
            var cid = $(e.currentTarget).data('cid'),
                model = this.pickupLocation.get(cid);

            showConfirm('Are you sure want to delete', function(){
                if (model){
                    model.destroy();
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