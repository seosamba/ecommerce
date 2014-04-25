define([
    'backbone',
    '../models/pickup-location-cat'
], function(Backbone, PickupLocationCatModel) {

    var PickupLocationCategories = Backbone.Collection.extend({
        model: PickupLocationCatModel,
        url: function() {
            return $('#website_url').val()+'api/store/pickuplocationcategories/id/';
        }
    });
    return PickupLocationCategories;
});