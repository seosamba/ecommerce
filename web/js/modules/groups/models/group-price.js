define([
    'backbone'
], function (Backbone) {
    var GroupsPriceModel = Backbone.Model.extend({
        urlRoot: function(){
            return $('#website_url').val() + 'api/store/groupsPrice/id/';
        }
    });

    return GroupsPriceModel;
});