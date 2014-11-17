define([
    'backbone'
], function (Backbone) {
    var PickupLocationModel = Backbone.Model.extend({
        urlRoot: function(){
            return $('#website_url').val() + 'api/store/pickuplocations/id/';
        }
    });

    return PickupLocationModel;
});