define([
    'backbone'
], function (Backbone) {
    var DigitalProductModel = Backbone.Model.extend({
        urlRoot: function () {
            return $('#website_url').val() + 'api/store/digitalproducts/id/';
        }
    });

    return DigitalProductModel;
});