define([
    'backbone'
], function (Backbone) {
    var CompanyModel = Backbone.Model.extend({
        urlRoot: function(){
            return $('#website_url').val() + 'api/store/companies/id/';
        }
    });

    return CompanyModel;
});