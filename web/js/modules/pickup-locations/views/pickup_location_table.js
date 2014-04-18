define([
	'backbone',
    '../collections/pickup-location',
    $('#website_url').val()+'system/js/external/jquery/plugins/DataTables/jquery.dataTables.min.js'
], function(Backbone,
            PickupLocationCollection
            ){

    var PickupLocationTableView = Backbone.View.extend({
        el: $('#pickup-locations-table'),
        events: {
            'click a[data-role=delete]': 'deleteLocation',
            'click a[data-role=edit]'  : 'editLocation'
        },
        templates: {},
        initialize: function(options){
            var aoColumnDefs = [
                { "bSortable": false, "aTargets": [ -1 ] }
            ];

            this.$el.dataTable({
                'sDom': 't<"clearfix"p>',
                "iDisplayLength": 6,
                "bPaginate": true,
                "bAutoWidth": false,
                "aoColumnDefs": aoColumnDefs
            });
            this.pickupLocation = new PickupLocationCollection();

            this.pickupLocation.on('reset', this.renderLocations, this);
            this.pickupLocation.on('add', this.renderLocations, this);
            this.pickupLocation.on('destroy', this.renderLocations, this);
        },
        render: function(){
            this.pickupLocation.categoryId = $(".ui-state-active").find('a').data('category-id');
            this.pickupLocation.pager();
        },
        renderLocations: function(){
            this.$el.fnClearTable();
            this.pickupLocation.each(this.renderLocation, this);
        },
        renderLocation: function(pickupLocation){

            this.$el.fnAddData([
                '<span>'+pickupLocation.get('name')+'</span>',
                '<span>'+pickupLocation.get('address1')+'</span>',
                '<span>'+pickupLocation.get('city')+'</span>',
                '<span>'+pickupLocation.get('country')+'</span>',
                '<span>'+pickupLocation.get('phone')+'</span>',
                '<a class="icon-pencil icon14" data-role="edit" data-cid="'+pickupLocation.get('id')+'" href="javascript:;"></a> <a class="icon-remove error icon14" data-role="delete" data-cid="'+pickupLocation.get('id')+'" href="javascript:;"></a>',
            ]);
        },
        editLocation: function(e){

        },
        deleteLocation: function(e){
            var cid = $(e.currentTarget).data('cid');
            var model = this.pickupLocation.get(cid);
            if (model){
                model.destroy();
            }
        },
        resetLocation: function(){
            this.$el.fnClearTable();
        }
    });

    return PickupLocationTableView;
});