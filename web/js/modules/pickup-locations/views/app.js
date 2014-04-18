define([
	'backbone',
    './pickup_location_form',
    './pickup_location_table',
    '../collections/pickup-location'
], function(Backbone,
            PickupLocationFormView, PickupLocationTableView, PickupLocationCollection){
    var MainView = Backbone.View.extend({
        el: $('#manage-pickup-locations'),
        events: {
            'click #new-pickup-location-btn': 'addCategory',
            'click .ui-state-default': 'activeTab',
            'click #delete-pickup-location-category': 'deleteCategory'
        },
        templates: {},
        initialize: function(){
            showSpinner();
            this.PickupLocationForm = new PickupLocationFormView();
            this.PickupLocationForm.render();

            this.pickupLocationTable = new PickupLocationTableView();
            this.pickupLocationTable.render();

            this.PickupLocationForm.$el.on('pickupLocation:created', _.bind(this.pickupLocationTable.render, this.pickupLocationTable));
        },
        activeTab: function(){
            $('.location-table').removeClass('hidden');
            $('#edit-pickup-location').removeClass('hidden');
            $('.delete-selected-category').removeClass('hidden');
            showSpinner();
            this.pickupLocation = new PickupLocationCollection();
            this.pickupLocation.on('reset', this.render, this);
            this.pickupLocationTable.render();
        },
        deleteCategory: function(){
            var currentCategoryId = $(".ui-state-active").find('a').data('category-id');
            var index = $('#manage-pickup-locations').tabs('option', 'active');
            showConfirm('Are you sure?', function(){
                $.ajax({
                    url: $('#website_url').val()+'api/store/pickuplocationcategories/id/'+currentCategoryId+'/',
                    type: 'DELETE',
                    dataType: 'json',
                    success: function(id) {
                        var tab = $('#manage-pickup-locations').find('.ui-tabs-nav li:eq('+index+')').remove();
                        var panelId = tab.attr( "aria-controls" );
                        $( "#" + panelId ).remove();
                        $('#manage-pickup-locations').tabs("refresh");
                    }
                });
            });
        },
        addCategory: function() {
            var name = 'name';
            var self = this;
            $.ajax({
                url: $('#website_url').val()+'api/store/pickuplocationcategories',
                type: 'POST',
                data:{name:name},
                dataType: 'json',
                success: function(id) {
                    self.$el.find('.ui-tabs-nav .add-new-pickup-location').before('<li><a data-category-id="'+id+'" href="#pickup-category-'+id+'">'+name+'</a></li>');
                    self.$el.find('.header').after('<div id="pickup-category-'+id+'"></div>');
                    self.$el.tabs('refresh');
                }
            });
        }
    });

    return MainView;
});