define([
    'backbone'
], function (Backbone) {
    var BrandModel = Backbone.Model.extend({
        urlRoot: function(){
            return $('#website_url').val() + 'api/store/brands/id/';
        },
        parse: function(response) {
            return response;
        }
    });

    return BrandModel;
});