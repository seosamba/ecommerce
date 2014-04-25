define([
    'backbone'
], function (Backbone) {
    var PickupLocationCatModel = Backbone.Model.extend({
        urlRoot: function(){
            return $('#website_url').val()+'api/store/pickuplocationcategories/id/';
        }
    });

    return PickupLocationCatModel;
});
