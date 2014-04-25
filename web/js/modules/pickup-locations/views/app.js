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
        },
        activeTab: function(){
            this.showConfig();
            $('#location-edit-id').val('');
            $('#edit-pickup-location').attr('method', 'POST');
            showSpinner();

            this.categories = new PickupLocationCategoriesCollection();
            this.PickupLoationCategories.render();

            // Set img
            var currentCategoryId = $('.ui-state-active').find('a').data('category-id'),
                currentCategory   = this.PickupLoationCategories.categories.get(currentCategoryId),
                websiteUrl        = $('#website_url').val(),
                src               = websiteUrl+'system/images/noimage.png';
            if (typeof(currentCategory) != 'undefined' && !_.isNull(currentCategory.get('img'))) {
                src = websiteUrl+'media/'+$('#things-select-folder').val()+'/small/'+currentCategory.get('img');
            }
            $('.uploader-category-logo img').attr('src', src);
            this.pickupLocation = new PickupLocationCollection();
            this.pickupLocation.on('reset', this.render, this);
            this.pickupLocationTable.render();
        },
        showConfig: function(){
            $('.location-table').removeClass('hidden');
            $('#edit-pickup-location').removeClass('hidden');
            $('.delete-selected-category').removeClass('hidden');
            $('.change-category-label').removeClass('hidden');
            $('.category-label').removeClass('hidden');
            $('.uploader-category-logo').removeClass('hidden');
        }
    });

    return MainView;
});