define([
	'backbone',
    './pickup_location_form',
    './pickup_location_table',
    '../collections/pickup-location',
    './pickup_location_cat',
    '../collections/pickup-location-cat'
], function(Backbone,
            PickupLocationFormView, PickupLocationTableView, PickupLocationCollection, PickupLocationCatView, PickupLocationCategoriesCollection){
    var MainView = Backbone.View.extend({
        el: $('#manage-pickup-locations'),
        events: {
            'click .ui-state-default': 'activeTab'
        },
        templates: {},
        initialize: function(){
            showSpinner();
            this.PickupLoationCategories = new PickupLocationCatView();
            this.PickupLoationCategories.render();

            this.PickupLocationForm = new PickupLocationFormView();
            this.PickupLocationForm.render();

            this.pickupLocationTable = new PickupLocationTableView();
            this.pickupLocationTable.render();

            this.PickupLocationForm.$el.on('pickupLocation:created', _.bind(this.pickupLocationTable.render, this.pickupLocationTable));
            this.PickupLocationForm.$el.on('pickupLocation:deleted', _.bind(this.PickupLoationCategories.render, this.PickupLoationCategories));
        },
        activeTab: function(){
            this.showConfig();
            showSpinner();
            this.PickupLocationForm.render();
            this.PickupLoationCategories.render();
            this.pickupLocationTable.pickupLocation.currentPage = 0;
            this.pickupLocationTable.render();

        },
        showConfig: function(){
            $('#pickup-location-config').removeClass('hidden');
        }
    });

    return MainView;
});