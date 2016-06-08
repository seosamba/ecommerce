define([
    'backbone'
], function (Backbone) {
    var DiscountQuantityModel = Backbone.Model.extend({
        urlRoot: function(){
            return $('#website_url').val() + 'api/store/discounts/id/';
        }
    });

    return DiscountQuantityModel;
});